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
    public function returnsFrameworkCategoryFacade(): \Shopsys\FrameworkBundle\Model\Category\CategoryFacade
    {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\CategoryFacade|null
     */
    public function returnsFrameworkCategoryFacadeOrNull(): ?\Shopsys\FrameworkBundle\Model\Category\CategoryFacade
    {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Article\ArticleData[]
     */
    public function returnsFrameworkArticleDataArray(): array
    {
    }

    /**
     * @return int
     */
    public function returnsInt(): int
    {
    }

    /**
     * @return array<string, int>
     */
    public function returnsAssocArray(): array
    {
    }

    /**
     * @return array
     */
    public function returnsNotTypedArray(): array
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryFacade $categoryFacade
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryFacade|null $categoryFacadeOrNull
     * @param \Shopsys\FrameworkBundle\Model\Article\ArticleData[] $array
     * @param int $integer
     */
    public function acceptsVariousParameters(\Shopsys\FrameworkBundle\Model\Category\CategoryFacade $categoryFacade, ?\Shopsys\FrameworkBundle\Model\Category\CategoryFacade $categoryFacadeOrNull, array $array, int $integer): void
    {
    }
}
