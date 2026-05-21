<?php

namespace App\Admin\Form;

use Symfony\Component\Form\AbstractType;

class FormTemplateViewType extends AbstractType
{
    public function getBlockPrefix(): string
    {
        return 'admin_form_template_view';
    }
}
