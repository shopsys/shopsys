<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\PropertyAnnotationsFactoryTest;

class BaseClass3
{
    /**
     * This property is redeclared in the child class
     *
     * @var \Shopsys\FrameworkBundle\Model\Category\CategoryFacade
     */
    public $categoryFacade;
}
