<?php

namespace Shopsys\FrameworkBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class ProductParameterValueType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function getParent(): ?string
    {
        return CollectionType::class;
    }
}
