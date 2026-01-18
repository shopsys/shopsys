<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\MethodAnnotationsFactoryTest;

use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;

class BaseClass6
{
    /**
     * This method has return typehint only (no @return annotation)
     * Should generate @method annotation with replaced return type
     */
    public function getCategoryFacade(): CategoryFacade
    {
    }
}
