<?php

declare(strict_types=1);

namespace Shopsys\FormTypesBundle\OrderedForm;

use Override;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Form\ResolvedFormTypeFactoryInterface;
use Symfony\Component\Form\ResolvedFormTypeInterface;

/**
 * Replaces the default factory so that every resolved form type orders its children by the `position` option
 *
 * Taken over from Becklyn/OrderedFormBundle (BSD-3-Clause, Copyright (c) 2018, Becklyn Studios),
 * see open-source-license-acknowledgements-and-third-party-copyrights.md
 */
class OrderedResolvedFormTypeFactory implements ResolvedFormTypeFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    public function createResolvedType(
        FormTypeInterface $type,
        array $typeExtensions,
        ?ResolvedFormTypeInterface $parent = null,
    ): ResolvedFormTypeInterface {
        return new OrderedResolvedFormType($type, $typeExtensions, $parent);
    }
}
