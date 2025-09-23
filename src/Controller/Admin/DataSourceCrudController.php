<?php

namespace App\Controller\Admin;

use App\Entity\DataSource;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

use function Symfony\Component\Translation\t;

class DataSourceCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return DataSource::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setEntityLabelInSingular(t('Data source'))
            ->setEntityLabelInPlural(t('Data sources'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('label', t('Label'));
        yield TextField::new('url', t('URL'));
    }
}
