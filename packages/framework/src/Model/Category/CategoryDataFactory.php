<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Category;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory;
use Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Seo\SeoAttributesDataFactory;

class CategoryDataFactory
{
    public function __construct(
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly PluginCrudExtensionFacade $pluginCrudExtensionFacade,
        protected readonly Domain $domain,
        protected readonly ImageUploadDataFactory $imageUploadDataFactory,
        protected readonly CategoryParameterRepository $categoryParameterRepository,
        protected readonly SeoAttributesDataFactory $seoAttributesDataFactory,
    ) {
    }

    protected function createInstance(): CategoryData
    {
        return new CategoryData();
    }

    public function createFromCategory(Category $category): CategoryData
    {
        $categoryData = $this->createInstance();
        $this->fillFromCategory($categoryData, $category);

        return $categoryData;
    }

    public function create(): CategoryData
    {
        $categoryData = $this->createInstance();
        $this->fillNew($categoryData);

        return $categoryData;
    }

    protected function fillNew(CategoryData $categoryData): void
    {
        $categoryData->image = $this->imageUploadDataFactory->create();

        foreach ($this->domain->getAllIds() as $domainId) {
            $categoryData->seo[$domainId] = $this->seoAttributesDataFactory->create();
            $categoryData->descriptions[$domainId] = null;
            $categoryData->enabled[$domainId] = true;
        }

        foreach ($this->domain->getAllLocales() as $locale) {
            $categoryData->name[$locale] = null;
        }
    }

    protected function fillFromCategory(CategoryData $categoryData, Category $category): void
    {
        $categoryData->name = $category->getNames();
        $categoryData->parent = $category->getParent();

        foreach ($this->domain->getAllIds() as $domainId) {
            $categoryData->seo[$domainId] = $this->seoAttributesDataFactory->createFromSeoAttributes(
                $category->getSeoAttributes($domainId),
            );
            $categoryData->descriptions[$domainId] = $category->getDescription($domainId);
            $categoryData->enabled[$domainId] = $category->isEnabled($domainId);

            $mainFriendlyUrl = $this->friendlyUrlFacade->findMainFriendlyUrl(
                $domainId,
                'front_product_list',
                $category->getId(),
            );
            $categoryData->urls->mainFriendlyUrlsByDomainId[$domainId] = $mainFriendlyUrl;
        }

        $parameters = $this->categoryParameterRepository->getParametersCollapsedByCategory($category);
        $categoryData->parametersCollapsed = $parameters;
        $categoryData->parametersPosition = $this->getParametersSortedByPositionFilteredByCategory($category);

        $categoryData->pluginData = $this->pluginCrudExtensionFacade->getAllData('category', $category->getId());
        $categoryData->image = $this->imageUploadDataFactory->createFromEntityAndType($category);
        $categoryData->automatedFilters = $category->getAutomatedFilters();
    }

    /**
     * @return int[]
     */
    protected function getParametersSortedByPositionFilteredByCategory(Category $category): array
    {
        $parameterIdsSortedByPosition = [];
        $categoryParameters = $this->categoryParameterRepository->getCategoryParametersByCategorySortedByPosition($category);

        foreach ($categoryParameters as $categoryParameter) {
            $parameterIdsSortedByPosition[] = $categoryParameter->getParameter()->getId();
        }

        return $parameterIdsSortedByPosition;
    }
}
