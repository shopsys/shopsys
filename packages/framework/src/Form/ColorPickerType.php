<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class ColorPickerType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getParent(): string
    {
        return TextType::class;
    }
}
