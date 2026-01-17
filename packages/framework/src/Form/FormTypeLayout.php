<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class FormTypeLayout
{
    public const string LAYOUT_BLOCK = 'block';
    public const string LAYOUT_INLINE = 'inline';

    public function resolveLayoutType(string $formTypeClass): string
    {
        $inlineTypes = [
            TextType::class,
            MoneyType::class,
        ];

        if (in_array($formTypeClass, $inlineTypes, true)) {
            return self::LAYOUT_INLINE;
        }

        return self::LAYOUT_BLOCK;
    }
}
