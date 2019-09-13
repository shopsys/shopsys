<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\PropertyAnnotationsFactoryTest;

class BaseClass2
{
    /**
     * This property is not of a type that is extended in the project
     *
     * @var \Shopsys\FrameworkBundle\Model\Article\ArticleData
     */
    public $articleData;
}
