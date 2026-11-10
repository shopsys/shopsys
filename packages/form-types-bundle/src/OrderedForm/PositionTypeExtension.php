<?php

declare(strict_types=1);

namespace Shopsys\FormTypesBundle\OrderedForm;

use Override;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Adds the `position` option to all form types and buttons, see docs/extensibility/form-extension.md
 *
 * Taken over from Becklyn/OrderedFormBundle (BSD-3-Clause, Copyright (c) 2018, Becklyn Studios),
 * see open-source-license-acknowledgements-and-third-party-copyrights.md
 */
final class PositionTypeExtension extends AbstractTypeExtension
{
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault('position', null)
            ->setAllowedTypes('position', ['null', 'string', 'int', 'array']);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function getExtendedTypes(): iterable
    {
        yield FormType::class;

        yield ButtonType::class;
    }
}
