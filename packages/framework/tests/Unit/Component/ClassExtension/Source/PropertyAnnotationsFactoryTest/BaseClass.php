<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\PropertyAnnotationsFactoryTest;

class BaseClass
{
    /**
     * This property is redeclared in the child class using the property annotation
     *
     * @var \Shopsys\FrameworkBundle\Model\Category\CategoryFacade
     */
    public $categoryFacade;
}
