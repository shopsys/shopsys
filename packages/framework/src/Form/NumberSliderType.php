<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

final class NumberSliderType extends AbstractType
{
    #[Override]
    public function getParent(): string
    {
        return NumberType::class;
    }
}
