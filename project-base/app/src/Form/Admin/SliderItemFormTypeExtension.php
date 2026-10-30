<?php

declare(strict_types=1);

namespace App\Form\Admin;

use Override;
use Shopsys\FrameworkBundle\Form\Admin\Slider\SliderItemFormType;
use Symfony\Component\Form\AbstractTypeExtension;

final class SliderItemFormTypeExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function getExtendedTypes(): iterable
    {
        yield SliderItemFormType::class;
    }
}
