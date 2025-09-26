<?php

namespace App\Controller\Admin;

use App\DataSourceHelper;
use App\Entity\ProcessOverview;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;

use function Symfony\Component\Translation\t;

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
            ->addFormTheme('admin/form.html.twig');
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, Action::new('show', t('Show process overview'))
                ->displayIf(fn (ProcessOverview $overview) => $overview->getGroup())
                ->linkToUrl(fn (ProcessOverview $overview) => $this->generateUrl('process_overview_show', [
                    'group' => $overview->getGroup()->getId(),
                    'overview' => $overview->getId(),
                ])))
            ->remove(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER)
            ->add(Crud::PAGE_NEW, Action::SAVE_AND_CONTINUE)
            ->remove(Crud::PAGE_NEW, Action::SAVE_AND_RETURN)
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', t('ID'))
            ->onlyOnDetail();
        yield TextField::new('label', t('Label'));
        yield AssociationField::new('group', t('Group'));
        yield AssociationField::new('dataSource', t('Data source'))
            ->setFormTypeOption('block_prefix', 'mikkel')
            ->hideOnIndex();

        $entity = $this->getContext()->getEntity()->getInstance();
        $datasource = null;
        $process = null;
        if ($datasource = $entity?->getDataSource()) {
            if ($processId = $entity->getProcessId()) {
                try {
                    $process = $this->dataSourceHelper->getProcess($datasource, $processId);
                } catch (\Exception) {
                }
            }
        }

        if (Crud::PAGE_DETAIL === $pageName) {
        } else {
            if (in_array($pageName, [Crud::PAGE_EDIT])) {
                if ($datasource) {
                    yield ChoiceField::new('processId', t('Process'))
                        ->setFormTypeOptions([
                            // @todo Add search for process
                            'choice_loader' => new CallbackChoiceLoader(function () use ($datasource): array {
                                $processes = $this->dataSourceHelper->getProcesses($datasource);
                                $options = array_combine(
                                    array_column($processes['items'] ?? [], 'name'),
                                    array_column($processes['items'] ?? [], 'id'),
                                );

                                return $options;
                            }),
                        ])
                        ->setRequired(true)
                        ->hideOnIndex()

                        // This is a hack to pass the process data to the template (cf. templates/admin/form.html.twig)
                        ->setFormTypeOptions([
                            'attr' => ['data-process-data' => json_encode($process)],
                        ]);

                    if ($process) {
                        yield CodeEditorField::new('options', t('Options'))
                            ->hideOnIndex()
                            ->setLanguage('yaml');
                    }
                }
            } else {
                // @todo Show message telling to save the entity
                // @todo Show a “Save to continue” button.
            }
        }
    }
}
