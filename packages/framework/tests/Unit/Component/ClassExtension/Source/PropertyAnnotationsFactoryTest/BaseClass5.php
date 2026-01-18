<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\PropertyAnnotationsFactoryTest;

class BaseClass5
{
    /**
     * This property has BOTH annotation and typehint - annotation should take precedence
     * The annotation provides more specific type info (array of Category vs just array)
     *
     * @var \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public array $categories;
}
