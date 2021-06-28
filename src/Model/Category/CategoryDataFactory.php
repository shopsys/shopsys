<?php

declare(strict_types=1);

namespace App\Model\Category;

use App\Model\Category\LinkedCategory\LinkedCategory;
use App\Model\Category\LinkedCategory\LinkedCategoryRepository;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Category\Category as BaseCategory;
use Shopsys\FrameworkBundle\Model\Category\CategoryData as BaseCategoryData;
use Shopsys\FrameworkBundle\Model\Category\CategoryDataFactory as BaseCategoryDataFactory;

class CategoryDataFactory extends BaseCategoryDataFactory
{
    /**
     * @var \App\Model\Category\CategoryParameterRepository
     */
    private $categoryParameterRepository;

    /**
     * @var \App\Model\Category\LinkedCategory\LinkedCategoryRepository
     */
    private LinkedCategoryRepository $linkedCategoryRepository;

    /**
     * @param \App\Model\Category\CategoryRepository $categoryRepository
     * @param \App\Model\Category\CategoryParameterRepository $categoryParameterRepository
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade $pluginCrudExtensionFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Component\Image\ImageFacade $imageFacade
     * @param \App\Model\Category\LinkedCategory\LinkedCategoryRepository $linkedCategoryRepository
     */
    public function __construct(
        CategoryRepository $categoryRepository,
        CategoryParameterRepository $categoryParameterRepository,
        FriendlyUrlFacade $friendlyUrlFacade,
        PluginCrudExtensionFacade $pluginCrudExtensionFacade,
        Domain $domain,
        ImageFacade $imageFacade,
        LinkedCategoryRepository $linkedCategoryRepository
    ) {
        parent::__construct(
            $categoryRepository,
            $friendlyUrlFacade,
            $pluginCrudExtensionFacade,
            $domain,
            $imageFacade
        );

        $this->categoryParameterRepository = $categoryParameterRepository;
        $this->linkedCategoryRepository = $linkedCategoryRepository;
    }

    /**
     * @param \App\Model\Category\Category $category
     * @return \App\Model\Category\CategoryData
     */
    public function createFromCategory(BaseCategory $category): BaseCategoryData
    {
        $categoryData = new CategoryData();
        $this->fillFromCategory($categoryData, $category);

        return $categoryData;
    }

    /**
     * @return \App\Model\Category\CategoryData
     */
    public function create(): BaseCategoryData
    {
        $categoryData = new CategoryData();
        $this->fillNew($categoryData);

        return $categoryData;
    }

    /**
     * @return \App\Model\Category\CategoryData
     */
    protected function createInstance(): BaseCategoryData
    {
        return new CategoryData();
    }

    /**
     * @param \App\Model\Category\CategoryData $categoryData
     */
    protected function fillNew(BaseCategoryData $categoryData)
    {
        parent::fillNew($categoryData);

        $categoryData->parametersCollapsed = [];

        foreach ($this->domain->getAllIds() as $domainId) {
            $categoryData->shortDescription[$domainId] = null;
        }
    }

    /**
     * @param \App\Model\Category\CategoryData $categoryData
     * @param \App\Model\Category\Category $category
     */
    protected function fillFromCategory(BaseCategoryData $categoryData, BaseCategory $category)
    {
        parent::fillFromCategory($categoryData, $category);

        foreach ($this->domain->getAllIds() as $domainId) {
            $categoryData->shortDescription[$domainId] = $category->getShortDescription($domainId);
        }

        $categoryData->akeneoCode = $category->getAkeneoCode();
        $categoryData->svgIcon = $category->getSvgIcon();
        $categoryData->parametersCollapsed = $this->categoryParameterRepository->getParametersCollapsedByCategory($category);
        $categoryData->overLimitQuantity = $category->getOverLimitQuantity();
        $categoryData->parametersPosition = $this->getParametersSortedByPositionFilteredByCategory($category);
        $categoryData->isSaleCategory = $category->isSaleCategory();

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
