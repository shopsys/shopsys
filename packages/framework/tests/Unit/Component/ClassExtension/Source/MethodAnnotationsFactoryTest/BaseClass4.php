<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\MethodAnnotationsFactoryTest;

use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;

class BaseClass4
{
    /**
     * This method returns a type that is registered in the class extension map and hence the "@method" annotation must be added to the child class
     *
     * @return \Shopsys\FrameworkBundle\Model\Category\CategoryFacade
     */
    public function getCategoryFacade(): CategoryFacade
    {
    }

    /**
     * This method accepts parameter with type that is registered in the class extension map and hence the "@method" annotation must be added to the child class
     *
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     */
    public function setCategory(Category $category): void
    {
    }
}
