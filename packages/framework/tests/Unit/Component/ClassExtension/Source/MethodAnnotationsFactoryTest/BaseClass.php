<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\MethodAnnotationsFactoryTest;

use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;

class BaseClass
{
    /**
     * This method is redeclared in the child class using the method annotation
     *
     * @return \Shopsys\FrameworkBundle\Model\Category\CategoryFacade
     */
    public function getCategoryFacade(): CategoryFacade
    {
    }
}
