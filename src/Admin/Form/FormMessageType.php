<?php

namespace App\Admin\Form;

use Symfony\Component\Form\AbstractType;

class FormMessageType extends AbstractType
{
    public function getBlockPrefix(): string
    {
        return 'admin_form_message';
    }
}
