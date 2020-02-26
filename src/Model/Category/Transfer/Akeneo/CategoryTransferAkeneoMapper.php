<?php

declare(strict_types=1);

namespace App\Model\Category\Transfer\Akeneo;

use App\Component\Akeneo\AkeneoHelper;
use App\Model\Category\Category;
use App\Model\Category\CategoryData;
use App\Model\Category\CategoryDataFactory;

class CategoryTransferAkeneoMapper
{
    /**
     * @var \App\Model\Category\CategoryDataFactory
     */
    private $categoryDataFactory;

    /**
     * @param \App\Model\Category\CategoryDataFactory $categoryDataFactory
     */
    public function __construct(CategoryDataFactory $categoryDataFactory)
    {
        $this->categoryDataFactory = $categoryDataFactory;
    }

    /**
     * @param array $akeneoCategoryData
     * @param \App\Model\Category\Category|null $category
     * @return \App\Model\Category\CategoryData
     */
    public function mapAkeneoCategoryDataToCategoryData(array $akeneoCategoryData, ?Category $category): CategoryData
    {
        if ($category === null) {
            $categoryData = $this->categoryDataFactory->create();
            $categoryData->akeneoCode = $akeneoCategoryData['code'];
        } else {
            $categoryData = $this->categoryDataFactory->createFromCategory($category);
        }

        $categoryData->name = AkeneoHelper::mapLocalizedLabels($categoryData->name, $akeneoCategoryData);

        return $categoryData;
    }
}
