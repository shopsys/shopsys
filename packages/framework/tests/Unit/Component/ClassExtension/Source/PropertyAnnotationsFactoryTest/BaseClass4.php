<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\PropertyAnnotationsFactoryTest;

class BaseClass4
{
    /**
     * This property is of a type that is registered in the class extension map and hence the "@property" annotation must be added to the child class
     *
     * @var \Shopsys\FrameworkBundle\Model\Category\CategoryFacade
     */
    public $categoryFacade;

    /**
     * This property defined using constructor property promotion is of a type that is registered in the class extension map and hence the "@property" annotation must be added to the child class
     *
     * @param \Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\PropertyAnnotationsFactoryTest\BaseClass $class
     */
    public function __construct(
        protected readonly BaseClass $class,
    ) {
    }
}
