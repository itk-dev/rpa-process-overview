<?php

namespace App\Controller\Admin;

use App\Entity\ProcessOverview;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

use function Symfony\Component\Translation\t;

class ProcessOverviewCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ProcessOverview::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setEntityLabelInPlural(t('Process overviews'))
            ->setEntityLabelInSingular(t('Process overview'));
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
                ])));
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', t('ID'))
            ->onlyOnDetail();
        yield TextField::new('label', t('Label'));
        yield AssociationField::new('group', t('Group'));
        yield CodeEditorField::new('options', t('Options'))
            ->hideOnIndex()
            ->setLanguage('yaml');
    }
}
