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
use Shopsys\FrameworkBundle\Model\Category\CategoryParameterRepository;

/**
 * @method fillNew(\App\Model\Category\CategoryData $categoryData)
 * @method int[] getParametersSortedByPositionFilteredByCategory(\App\Model\Category\Category $category)
 * @method \App\Model\Category\CategoryData create()
 */
class CategoryDataFactory extends BaseCategoryDataFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade $pluginCrudExtensionFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory $imageUploadDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryParameterRepository $categoryParameterRepository
     * @param \App\Model\Category\LinkedCategory\LinkedCategoryRepository $linkedCategoryRepository
     */
    public function __construct(
        FriendlyUrlFacade $friendlyUrlFacade,
        PluginCrudExtensionFacade $pluginCrudExtensionFacade,
        Domain $domain,
        ImageUploadDataFactory $imageUploadDataFactory,
        CategoryParameterRepository $categoryParameterRepository,
        private readonly LinkedCategoryRepository $linkedCategoryRepository,
    ) {
        parent::__construct(
            $friendlyUrlFacade,
            $pluginCrudExtensionFacade,
            $domain,
            $imageUploadDataFactory,
            $categoryParameterRepository,
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
    protected function createInstance(): BaseCategoryData
    {
        $categoryData = new CategoryData();
        $categoryData->image = $this->imageUploadDataFactory->create();

        return $categoryData;
    }

    /**
     * @param \App\Model\Category\CategoryData $categoryData
     * @param \App\Model\Category\Category $category
     */
    protected function fillFromCategory(BaseCategoryData $categoryData, BaseCategory $category): void
    {
        parent::fillFromCategory($categoryData, $category);

        /** @var \App\Model\Product\Parameter\Parameter[] $parameters */
        $parameters = $this->categoryParameterRepository->getParametersCollapsedByCategory($category);
        $categoryData->parametersCollapsed = $parameters;
        $categoryData->parametersPosition = $this->getParametersSortedByPositionFilteredByCategory($category);

        $linkedCategories = $this->linkedCategoryRepository->getAllByParentCategory($category);
        $categoryData->linkedCategories = array_map(function (LinkedCategory $linkedCategory) {
            return $linkedCategory->getCategory();
        }, $linkedCategories);
    }
}
