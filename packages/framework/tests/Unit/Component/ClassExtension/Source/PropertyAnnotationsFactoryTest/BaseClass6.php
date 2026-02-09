<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\PropertyAnnotationsFactoryTest;

use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;

class BaseClass6
{
    /**
     * Property with typehint only (no @var annotation)
     * Should generate @property annotation with replaced type
     */
    public CategoryFacade $categoryFacade;

    /**
     * Property with nullable typehint only (no @var annotation)
     * Should generate @property annotation with replaced nullable type
     */
    public ?Category $nullableCategory;

    /**
     * Property with union typehint only (no @var annotation)
     * Should generate @property annotation with replaced union type
     */
    public Category|CategoryFacade $unionType;
}
