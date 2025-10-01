<?php

namespace App\Admin\Field;

use App\Admin\Form\FormTemplateViewType;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use Symfony\Contracts\Translation\TranslatableInterface;

final class FormField implements FieldInterface
{
    use FieldTrait;

    public const OPTION_TEMPLATE_CONTEXT = 'template_context';

    /**
     * @internal Use the other named constructors instead (addMessage(), etc.)
     *
     * @param TranslatableInterface|string|false|null $label
     */
    public static function new(string $propertyName, $label = null): never
    {
        throw new \RuntimeException('Instead of this method, use the "addMessage()" method.');
    }

    public static function addTemplateView(string $templatePath, array $context = []): self
    {
        return new self()
            ->setFieldFqcn(__CLASS__)
            ->hideOnIndex()
            ->setProperty('admin_form_template_view')
            ->setLabel(false)
            ->setFormType(FormTemplateViewType::class)
            ->addCssClass('field-form_template_view')
            ->setFormTypeOptions(['mapped' => false, 'required' => false])
            ->setValue(true)
            ->setTemplatePath($templatePath)
            ->setTemplateContext($context);
    }

    public function setTemplateContext(array $context): self
    {
        $this->setCustomOption(self::OPTION_TEMPLATE_CONTEXT, $context);

        return $this;
    }
}
