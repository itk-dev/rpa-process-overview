<?php

namespace App\Controller\Admin;

use App\Entity\ProcessOverviewGroup;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ProcessOverviewGroupCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ProcessOverviewGroup::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('label');
    }
}
