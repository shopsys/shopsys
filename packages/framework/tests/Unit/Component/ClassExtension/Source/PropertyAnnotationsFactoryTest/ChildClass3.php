<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\PropertyAnnotationsFactoryTest;

class ChildClass3 extends BaseClass3
{
    /**
     * @var \App\Model\Category\CategoryFacade
     */
    public $categoryFacade;
}
