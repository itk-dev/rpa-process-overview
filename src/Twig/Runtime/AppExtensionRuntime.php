<?php

namespace App\Twig\Runtime;

use Twig\Extension\RuntimeExtensionInterface;

class AppExtensionRuntime implements RuntimeExtensionInterface
{
    public function jsonDecode($value, ?bool $associative = true): mixed
    {
        try {
            return json_decode($value, true); // , $associative, 512, JSON_THROW_ON_ERROR);
        } catch (\Exception) {
            return null;
        }
    }
}
