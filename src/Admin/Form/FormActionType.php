<?php

namespace App\Admin\Form;

use Symfony\Component\Form\AbstractType;

class FormActionType extends AbstractType
{
    public function getBlockPrefix(): string
    {
        return 'admin_form_action';
    }
}
