<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

final class ProductParameterValueType extends AbstractType
{
    #[Override]
    public function getParent(): string
    {
        return CollectionType::class;
    }
}
