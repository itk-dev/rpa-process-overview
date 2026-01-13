<?php

namespace App\Controller\Admin;

use App\Entity\DataSource;
use App\Entity\UserRole;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Security\Http\Attribute\IsGranted;

use function Symfony\Component\Translation\t;

#[IsGranted(UserRole::Admin->value)]
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
        yield TextField::new('url', t('URL'))
            ->setFormType(UrlType::class);

        yield TextField::new('createdBy', t('Created by'))
            ->hideOnForm();
        yield DateTimeField::new('createdAt', t('Created at'))
            ->hideOnForm();

        $help = t(<<<'HELP'
The options must contain a <code>client_options</code> value with options for an <a href="https://symfony.com/doc/current/http_client.html">HTTP Client</a>, e.g.

{example_options}
HELP, ['example_options' => '<code><pre>
client_options:
  headers:
    x-api-key: …
  # Optionally, ignore the SSL certificate provided used by the API
  # verify_peer: false
  # verify_host: false
</pre></code>']);
        yield CodeEditorField::new('options', t('Options'))
            ->hideOnIndex()
            ->setLanguage('yaml')
            ->setHelp($help);
    }
}
