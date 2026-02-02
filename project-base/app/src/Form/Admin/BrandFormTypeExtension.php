<?php

declare(strict_types=1);

namespace App\Form\Admin;

use Override;
use Shopsys\FrameworkBundle\Form\Admin\Product\Brand\BrandFormType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

final class BrandFormTypeExtension extends AbstractTypeExtension
{
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
    }

    #[Override]
    public static function getExtendedTypes(): iterable
    {
        yield BrandFormType::class;
    }
}
