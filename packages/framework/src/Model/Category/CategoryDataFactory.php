<?php

namespace Shopsys\FrameworkBundle\Model\Category;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory;
use Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;

class CategoryDataFactory implements CategoryDataFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryRepository $categoryRepository
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade $pluginCrudExtensionFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory $imageUploadDataFactory
     */
    public function __construct(
        protected readonly CategoryRepository $categoryRepository,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly PluginCrudExtensionFacade $pluginCrudExtensionFacade,
        protected readonly Domain $domain,
        protected readonly ImageUploadDataFactory $imageUploadDataFactory,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\CategoryData
     */
    protected function createInstance(): CategoryData
    {
        $categoryData = new CategoryData();
        $categoryData->image = $this->imageUploadDataFactory->create();

        return $categoryData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @return \Shopsys\FrameworkBundle\Model\Category\CategoryData
     */
    public function createFromCategory(Category $category): CategoryData
    {
        $categoryData = $this->createInstance();
        $this->fillFromCategory($categoryData, $category);

        return $categoryData;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\CategoryData
     */
    public function create(): CategoryData
    {
        $categoryData = $this->createInstance();
        $this->fillNew($categoryData);

        return $categoryData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryData $categoryData
     */
    protected function fillNew(CategoryData $categoryData): void
    {
        foreach ($this->domain->getAllIds() as $domainId) {
            $categoryData->seoMetaDescriptions[$domainId] = null;
            $categoryData->seoTitles[$domainId] = null;
            $categoryData->seoH1s[$domainId] = null;
            $categoryData->descriptions[$domainId] = null;
            $categoryData->enabled[$domainId] = true;
        }

        foreach ($this->domain->getAllLocales() as $locale) {
            $categoryData->name[$locale] = null;
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryData $categoryData
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     */
    protected function fillFromCategory(CategoryData $categoryData, Category $category): void
    {
        $categoryData->name = $category->getNames();
        $categoryData->parent = $category->getParent();

        foreach ($this->domain->getAllIds() as $domainId) {
            $categoryData->seoMetaDescriptions[$domainId] = $category->getSeoMetaDescription($domainId);
            $categoryData->seoTitles[$domainId] = $category->getSeoTitle($domainId);
            $categoryData->seoH1s[$domainId] = $category->getSeoH1($domainId);
            $categoryData->descriptions[$domainId] = $category->getDescription($domainId);
            $categoryData->enabled[$domainId] = $category->isEnabled($domainId);

            $mainFriendlyUrl = $this->friendlyUrlFacade->findMainFriendlyUrl(
                $domainId,
                'front_product_list',
                $category->getId()
            );
            $categoryData->urls->mainFriendlyUrlsByDomainId[$domainId] = $mainFriendlyUrl;
        }

        $categoryData->pluginData = $this->pluginCrudExtensionFacade->getAllData('category', $category->getId());
        $categoryData->image = $this->imageUploadDataFactory->createFromEntityAndType($category);
    }
}
