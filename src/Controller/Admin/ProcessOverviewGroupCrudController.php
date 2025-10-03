<?php

namespace App\Controller\Admin;

use App\Entity\ProcessOverviewGroup;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

use function Symfony\Component\Translation\t;

class ProcessOverviewGroupCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ProcessOverviewGroup::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setEntityLabelInSingular(t('Group'))
            ->setEntityLabelInPlural(t('Groups'));
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->add(Crud::PAGE_INDEX, Action::new('show', t('Show group'))
                ->linkToUrl(fn (ProcessOverviewGroup $group) => $this->generateUrl('process_overview_group_show', [
                    'id' => $group->getId(),
                ])));
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', t('ID'))
            ->onlyOnDetail();
        yield TextField::new('label', t('Label'));

        yield TextField::new('createdBy', t('Created by'))
            ->hideOnForm();
        yield DateTimeField::new('createdAt', t('Created at'))
            ->hideOnForm();
    }
}
