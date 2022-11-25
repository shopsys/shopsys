<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\MethodAnnotationsFactoryTest;

use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;

class ChildClass3 extends BaseClass3
{
    /**
     * @return \App\Model\Category\CategoryFacade
     */
    public function getCategoryFacade(): CategoryFacade
    {
    }
}
