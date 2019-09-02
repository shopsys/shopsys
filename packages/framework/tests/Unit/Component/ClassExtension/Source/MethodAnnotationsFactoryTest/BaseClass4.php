<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\MethodAnnotationsFactoryTest;

class BaseClass4
{
    /**
     * This method return a type that is registered in the class extension map and hence the "@method" annotation must be added to the child class
     *
     * @return \Shopsys\FrameworkBundle\Model\Category\CategoryFacade
     */
    public function getCategoryFacade()
    {
    }
}
