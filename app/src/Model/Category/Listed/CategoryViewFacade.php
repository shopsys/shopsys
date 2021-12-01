<?php

declare(strict_types=1);

namespace App\Model\Category\Listed;

use App\Model\Category\CategoryFacade;

class CategoryViewFacade
{
    /**
     * @var \App\Model\Category\CategoryFacade
     */
    private CategoryFacade $categoryFacade;

    /**
     * @var \App\Model\Category\Listed\CategoryViewFactory
     */
    private CategoryViewFactory $categoryViewFactory;

    /**
     * @param \App\Model\Category\CategoryFacade $categoryFacade
     * @param \App\Model\Category\Listed\CategoryViewFactory $categoryViewFactory
     */
    public function __construct(
        CategoryFacade $categoryFacade,
        CategoryViewFactory $categoryViewFactory
    ) {
        $this->categoryFacade = $categoryFacade;
        $this->categoryViewFactory = $categoryViewFactory;
    }

    /**
     * @param array $categoryIds
     * @return \App\Model\Category\Listed\CategoryView[]
     */
    public function getByCategoryIds(array $categoryIds): array
    {
        $categories = $this->categoryFacade->getByIds($categoryIds);

        $categoryViews = [];
        foreach ($categories as $category) {
            $categoryViews[] = $this->categoryViewFactory->createFromCategory($category);
        }

        return $categoryViews;
    }
}
