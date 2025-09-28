<?php

namespace App\Admin\Field;

use App\Admin\Form\FormActionType;
use App\Admin\Form\FormAlertType;
use App\Admin\Form\FormMessageType;
use App\Admin\Form\FormTemplateViewType;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use Symfony\Contracts\Translation\TranslatableInterface;

final class FormField implements FieldInterface
{
    use FieldTrait;

    public const OPTION_CONTENT = 'content';
    public const OPTION_TYPE = 'type';
    public const OPTION_ACTION = 'action';
    public const OPTION_TEMPLATE = 'template';
    public const OPTION_TEMPLATE_CONTEXT = 'template_context';

    public const TYPE_INFO = 'info';
    public const TYPE_WARNING = 'warning';
    public const TYPE_DANGER = 'danger';

    /**
     * @internal Use the other named constructors instead (addMessage(), etc.)
     *
     * @param TranslatableInterface|string|false|null $label
     */
    public static function new(string $propertyName, $label = null): never
    {
        throw new \RuntimeException('Instead of this method, use the "addMessage()" method.');
    }

    public static function addAlert(string|TranslatableInterface $content, string $type = self::TYPE_INFO): self
    {
        return new self()
            ->setFieldFqcn(__CLASS__)
            ->hideOnIndex()
            ->setProperty('admin_forms_alert')
            ->setLabel(false)
            ->setFormType(FormAlertType::class)
            ->addCssClass('field-form_alert')
            ->setFormTypeOptions(['mapped' => false, 'required' => false])
            ->setValue(true)
            ->setContent($content)
            ->setType($type);
    }

    public static function addAction(string $label, string $action): self
    {
        return new self()
            ->setFieldFqcn(__CLASS__)
            ->hideOnIndex()
            ->setProperty('admin_forms_action')
            ->setLabel(false)
            ->setFormType(FormActionType::class)
            ->addCssClass('field-form_action')
            ->setFormTypeOptions(['mapped' => false, 'required' => false])
            ->setValue(true)
            ->setContent($label)
            ->setAction($action);
    }

    public static function addMessage(string $content): self
    {
        return new self()
            ->setFieldFqcn(__CLASS__)
            ->hideOnIndex()
            ->setProperty('admin_forms_message')
            ->setLabel(false)
            ->setFormType(FormMessageType::class)
            ->addCssClass('field-form_message')
            ->setFormTypeOptions(['mapped' => false, 'required' => false])
            ->setValue(true)
            ->setContent($content);
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

    public function setAction(string $action): self
    {
        $this->setCustomOption(self::OPTION_ACTION, $action);

        return $this;
    }

    public function setContent(string $content): self
    {
        $this->setCustomOption(self::OPTION_CONTENT, $content);

        return $this;
    }

    public function setType(string $type): self
    {
        $this->setCustomOption(self::OPTION_TYPE, $type);

        return $this;
    }

    public function setTemplateContext(array $context): self
    {
        $this->setCustomOption(self::OPTION_TEMPLATE_CONTEXT, $context);

        return $this;
    }
}
