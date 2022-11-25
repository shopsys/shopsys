<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\MethodAnnotationsFactoryTest;

use Shopsys\FrameworkBundle\Model\Article\ArticleData;

class BaseClass2
{
    /**
     * This method returns a type that is not extended in the project
     *
     * @return \Shopsys\FrameworkBundle\Model\Article\ArticleData
     */
    public function getArticleData(): ArticleData
    {
    }
}
