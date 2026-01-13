<?php

namespace App\Controller\Admin;

use App\DataSourceHelper;
use App\Entity\DataSource;
use App\Entity\UserRole;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

use function Symfony\Component\Translation\t;

#[IsGranted(UserRole::Admin->value)]
class DataSourceCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly DataSourceHelper $dataSourceHelper,
    ) {
    }

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

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->add(Crud::PAGE_EDIT, Action::new('pingDataSource', t('Ping data source'))
            ->linkToCrudAction('pingDataSource'));
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

        $exampleOptions = <<<OPTIONS
client_options:
  headers:
    x-api-key: …
  # Optionally, ignore the SSL certificate provided used by the API
  # verify_peer: false
  # verify_host: false
OPTIONS;
        $help = t(<<<'HELP'
The options must contain a <code>client_options</code> value with options for an <a href="https://symfony.com/doc/current/http_client.html">HTTP Client</a>, e.g.

{example_options}
HELP, ['example_options' => '<code><pre>'.$exampleOptions.'</pre></code>']);
        yield CodeEditorField::new('options', t('Options'))
            ->hideOnIndex()
            ->setLanguage('yaml')
            ->setFormTypeOptions([
                'empty_data' => '',
            ])
            ->setHelp($help);
    }

    #[AdminRoute(path: '/ping/{entityId}', name: 'process_overview_admin_data_source_ping')]
    public function pingDataSource(AdminContext $context): Response
    {
        /** @var ?DataSource $dataSource */
        $dataSource = $context->getEntity()?->getInstance();

        try {
            $this->dataSourceHelper->getProcesses($dataSource);
            $this->addFlash('success', t('Data source successfully pinged'));
        } catch (\Exception $exception) {
            $this->addFlash('error', t('Unable to ping data source: {message}', ['message' => $exception->getMessage()]));
        }

        return $this->redirectToRoute('process_overview_admin_data_source_edit', ['entityId' => $dataSource->getId()]);
    }
}
