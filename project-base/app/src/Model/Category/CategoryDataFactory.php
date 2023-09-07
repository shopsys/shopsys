<?php

declare(strict_types=1);

namespace App\Model\Category;

use App\Model\Category\LinkedCategory\LinkedCategory;
use App\Model\Category\LinkedCategory\LinkedCategoryRepository;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory;
use Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Category\Category as BaseCategory;
use Shopsys\FrameworkBundle\Model\Category\CategoryData as BaseCategoryData;
use Shopsys\FrameworkBundle\Model\Category\CategoryDataFactory as BaseCategoryDataFactory;

class CategoryDataFactory extends BaseCategoryDataFactory
{
    /**
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade $pluginCrudExtensionFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory $imageUploadDataFactory
     * @param \App\Model\Category\CategoryParameterRepository $categoryParameterRepository
     * @param \App\Model\Category\LinkedCategory\LinkedCategoryRepository $linkedCategoryRepository
     */
    public function __construct(
        FriendlyUrlFacade $friendlyUrlFacade,
        PluginCrudExtensionFacade $pluginCrudExtensionFacade,
        Domain $domain,
        ImageUploadDataFactory $imageUploadDataFactory,
        private readonly CategoryParameterRepository $categoryParameterRepository,
        private readonly LinkedCategoryRepository $linkedCategoryRepository,
    ) {
        parent::__construct(
            $friendlyUrlFacade,
            $pluginCrudExtensionFacade,
            $domain,
            $imageUploadDataFactory,
        );
    }

    /**
     * @param \App\Model\Category\Category $category
     * @return \App\Model\Category\CategoryData
     */
    public function createFromCategory(BaseCategory $category): BaseCategoryData
    {
        $categoryData = $this->createInstance();
        $this->fillFromCategory($categoryData, $category);

        return $categoryData;
    }

    /**
     * @return \App\Model\Category\CategoryData
     */
    public function create(): BaseCategoryData
    {
        $categoryData = $this->createInstance();
        $this->fillNew($categoryData);

        return $categoryData;
    }

    /**
     * @return \App\Model\Category\CategoryData
     */
    protected function createInstance(): BaseCategoryData
    {
        $categoryData = new CategoryData();
        $categoryData->image = $this->imageUploadDataFactory->create();

        return $categoryData;
    }

    /**
     * @param \App\Model\Category\CategoryData $categoryData
     */
    protected function fillNew(BaseCategoryData $categoryData): void
    {
        parent::fillNew($categoryData);

        $categoryData->parametersCollapsed = [];
    }

    /**
     * @param \App\Model\Category\CategoryData $categoryData
     * @param \App\Model\Category\Category $category
     */
    protected function fillFromCategory(BaseCategoryData $categoryData, BaseCategory $category): void
    {
        parent::fillFromCategory($categoryData, $category);

        $categoryData->akeneoCode = $category->getAkeneoCode();
        $categoryData->parametersCollapsed = $this->categoryParameterRepository->getParametersCollapsedByCategory($category);
        $categoryData->parametersPosition = $this->getParametersSortedByPositionFilteredByCategory($category);

        $linkedCategories = $this->linkedCategoryRepository->getAllByParentCategory($category);
        $categoryData->linkedCategories = array_map(function (LinkedCategory $linkedCategory) {
            return $linkedCategory->getCategory();
        }, $linkedCategories);
    }

    /**
     * @param \App\Model\Category\Category $category
     * @return int[]
     */
    private function getParametersSortedByPositionFilteredByCategory(Category $category): array
    {
        $parameterIdsSortedByPosition = [];
        $categoryParameters = $this->categoryParameterRepository->getCategoryParametersByCategorySortedByPosition($category);

        foreach ($categoryParameters as $categoryParameter) {
            $parameterIdsSortedByPosition[] = $categoryParameter->getParameter()->getId();
        }

        return $parameterIdsSortedByPosition;
    }
}
