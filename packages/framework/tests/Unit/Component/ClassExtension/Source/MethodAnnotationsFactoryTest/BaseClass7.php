<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\MethodAnnotationsFactoryTest;

class BaseClass7
{
    /**
     * This method has BOTH annotation and typehint - annotation should take precedence
     * The annotation provides more specific type info (array of Category vs just array)
     *
     * @param \Shopsys\FrameworkBundle\Model\Category\Category[] $categories
     */
    public function setCategories(array $categories): void
    {
    }

    /**
     * This method has BOTH annotation and typehint for return - annotation should take precedence
     * The annotation provides more specific type info (array of CategoryFacade vs just array)
     *
     * @return \Shopsys\FrameworkBundle\Model\Category\CategoryFacade[]
     */
    public function getCategoryFacades(): array
    {
    }
}
