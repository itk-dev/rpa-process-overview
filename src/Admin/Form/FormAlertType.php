<?php

namespace App\Admin\Form;

use Symfony\Component\Form\AbstractType;

class FormAlertType extends AbstractType
{
    public function getBlockPrefix(): string
    {
        return 'admin_form_alert';
    }
}
