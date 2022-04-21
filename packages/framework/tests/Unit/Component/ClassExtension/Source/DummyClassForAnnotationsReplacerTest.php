<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ClassExtension\Source;

class DummyClassForAnnotationsReplacerTest
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\CategoryFacade|null
     */
    public $categoryFacadeOrNull;

    /**
     * @var int
     */
    public $integer;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Article\ArticleData[]
     */
    public $articleDataArray;

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\CategoryFacade
     */
    public function returnsFrameworkCategoryFacade()
    {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\CategoryFacade|null
     */
    public function returnsFrameworkCategoryFacadeOrNull()
    {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Article\ArticleData[]
     */
    public function returnsFrameworkArticleDataArray()
    {
    }

    /**
     * @return int
     */
    public function returnsInt()
    {
    }

    /**
     * @return array<string, int>
     */
    public function returnsAssocArray()
    {
    }

    /**
     * @return array
     */
    public function returnsNotTypedArray()
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryFacade $categoryFacade
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryFacade|null $categoryFacadeOrNull
     * @param \Shopsys\FrameworkBundle\Model\Article\ArticleData[] $array
     * @param int $integer
     */
    public function acceptsVariousParameters($categoryFacade, $categoryFacadeOrNull, $array, $integer)
    {
    }
}
