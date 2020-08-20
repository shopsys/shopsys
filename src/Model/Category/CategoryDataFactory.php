<?php

declare(strict_types=1);

namespace App\Model\Category;

use App\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use App\Model\Category\CategoryProductSeries\CategoryProductSeriesFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade;
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
     * @var \App\Model\Category\CategoryProductSeries\CategoryProductSeriesFacade
     */
    private $categoryProductSeriesFacade;

    /**
     * @param \App\Model\Category\CategoryRepository $categoryRepository
     * @param \App\Model\Category\CategoryParameterRepository $categoryParameterRepository
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade $pluginCrudExtensionFacade
     * @param \App\Component\Domain\Domain $domain
     * @param \App\Component\Image\ImageFacade $imageFacade
     * @param \App\Model\Category\CategoryProductSeries\CategoryProductSeriesFacade $categoryProductSeriesFacade
     */
    public function __construct(
        CategoryRepository $categoryRepository,
        CategoryParameterRepository $categoryParameterRepository,
        FriendlyUrlFacade $friendlyUrlFacade,
        PluginCrudExtensionFacade $pluginCrudExtensionFacade,
        Domain $domain,
        ImageFacade $imageFacade,
        CategoryProductSeriesFacade $categoryProductSeriesFacade
    ) {
        parent::__construct(
            $categoryRepository,
            $friendlyUrlFacade,
            $pluginCrudExtensionFacade,
            $domain,
            $imageFacade
        );
        $this->categoryParameterRepository = $categoryParameterRepository;
        $this->categoryProductSeriesFacade = $categoryProductSeriesFacade;
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
            $categoryData->productSeriesListTitle[$domainId] = null;
            $categoryData->productSeriesListDescription[$domainId] = null;
            $categoryData->productSeriesListLink[$domainId] = null;
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
            $categoryData->productSeriesListTitle[$domainId] = $category->getProductSeriesListTitle($domainId);
            $categoryData->productSeriesListDescription[$domainId] = $category->getProductSeriesListDescription($domainId);
            $categoryData->productSeriesListLink[$domainId] = $category->getProductSeriesListLink($domainId);
        }

        $categoryData->akeneoCode = $category->getAkeneoCode();
        $categoryData->svgIcon = $category->getSvgIcon();
        $categoryData->parametersCollapsed = $this->categoryParameterRepository->getParametersCollapsedByCategory($category);
        $categoryData->categoryProductSeries = $this->categoryProductSeriesFacade->getAllCategoryProductSeriesByCategory($category);
        $categoryData->parametersPosition = $this->getParametersSortedByPositionFilteredByCategory($category);
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
