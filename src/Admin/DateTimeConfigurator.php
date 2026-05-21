<?php

namespace App\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldConfiguratorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;

class DateTimeConfigurator implements FieldConfiguratorInterface
{
    public function __construct(private readonly array $options)
    {
    }

    public function supports(FieldDto $field, EntityDto $entityDto): bool
    {
        return DateTimeField::class === $field->getFieldFqcn();
    }

    public function configure(FieldDto $field, EntityDto $entityDto, AdminContext $context): void
    {
        $timezone = $this->options['view_timezone'];
        $field->setFormTypeOption('view_timezone', $timezone);
    }
}
