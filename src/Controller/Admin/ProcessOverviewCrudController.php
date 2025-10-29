<?php

namespace App\Controller\Admin;

use App\Admin\Field\FormField;
use App\DataSourceHelper;
use App\Entity\ProcessOverview;
use App\Entity\UserRole;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField as EaFormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Security\Http\Attribute\IsGranted;

use function Symfony\Component\Translation\t;

#[IsGranted(UserRole::OverviewEditor->value)]
class ProcessOverviewCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly DataSourceHelper $dataSourceHelper,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return ProcessOverview::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setEntityLabelInSingular(t('Process overview'))
            ->setEntityLabelInPlural(t('Process overviews'))
            ->addFormTheme('admin/process_overview_form.html.twig');
    }

    public function configureActions(Actions $actions): Actions
    {
        // Cheat a little to set display condition on built in action.
        $saveAndReturn = $actions->getAsDto(Crud::PAGE_EDIT)->getAction(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN)
            ->getAsConfigObject()
            // Hide for incomplete overview.
            ->displayIf(static fn (ProcessOverview $overview) => $overview->isReady());

        return parent::configureActions($actions)
            ->add(Crud::PAGE_INDEX, Action::new('show', t('Show process overview'))
                ->displayIf(fn (ProcessOverview $overview) => $overview->isReady())
                ->linkToUrl(fn (ProcessOverview $overview) => $this->generateUrl('process_overview_show', [
                    'group' => $overview->getGroup()->getId(),
                    'overview' => $overview->getId(),
                ])))
            ->remove(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER)
            ->add(Crud::PAGE_NEW, Action::SAVE_AND_CONTINUE)
            ->remove(Crud::PAGE_NEW, Action::SAVE_AND_RETURN)
            // Hide “Save and return” for incomplete overview.
            ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN)
            ->add(Crud::PAGE_EDIT, $saveAndReturn);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', t('ID'))
            ->onlyOnDetail();
        yield TextField::new('label', t('Label'));
        yield AssociationField::new('group', t('Group'));

        yield TextField::new('createdBy', t('Created by'))
            ->hideOnForm();
        yield DateTimeField::new('createdAt', t('Created at'))
            ->hideOnForm();

        /** @var ProcessOverview $entity */
        $entity = $this->getContext()->getEntity()->getInstance();
        $dataSource = $entity?->getDataSource();
        $process = null;
        if ($dataSource) {
            if ($processId = $entity->getProcessId()) {
                try {
                    $process = $this->dataSourceHelper->getProcess($dataSource, $processId);
                } catch (\Exception) {
                }
            }
        }

        // https://symfony.com/bundles/EasyAdminBundle/current/fields.html#form-fieldsets
        yield EaFormField::addFieldset(
            $dataSource ? t('Data source ({label})', ['label' => $dataSource->getLabel()]) : t('Data source'),
            propertySuffix: 'data_source'
        );

        yield AssociationField::new('dataSource', t('Data source'))
            ->hideOnIndex();

        yield EaFormField::addFieldset(
            isset($process['name']) ? t('Process ({label})', ['label' => $process['name']]) : t('Process'),
            propertySuffix: 'process'
        );

        if ($dataSource) {
            yield ChoiceField::new('processId', t('Process'))
                ->setFormTypeOptions([
                    // @todo Add search for process
                    'choice_loader' => new CallbackChoiceLoader(function () use ($dataSource): array {
                        $processes = $this->dataSourceHelper->getProcesses($dataSource)['items'] ?? [];
                        $options = array_combine(
                            array_column($processes, 'name'),
                            array_column($processes, 'id'),
                        );

                        return $options;
                    }),
                ])
                // ->setRequired(true)
                ->onlyOnForms();
        }

        yield EaFormField::addFieldset(t('Process options'), propertySuffix: 'process_options');

        if ($process) {
            yield CodeEditorField::new('options', t('Options'))
                ->setLanguage('yaml')
                ->setColumns(6)
                ->setFormTypeOptions([
                    'empty_data' => '',
                ])
            ;
            yield FormField::addTemplateView('admin/crud/process_overview/options_details.html.twig', [
                'process' => $process,
            ])
                ->onlyOnForms()
                ->setColumns(6)
            ;
        }
    }
}
