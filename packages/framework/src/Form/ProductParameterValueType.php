<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Override;
use Symfony\Component\Form\AbstractType;

final class ProductParameterValueType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getParent(): string
    {
        return InteractiveCollectionType::class;
    }
}
