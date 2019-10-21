<?php

namespace Shopsys\FrameworkBundle\Model\Category;

use BadMethodCallException;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;

class CategoryDataFactory implements CategoryDataFactoryInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade
     */
    protected $friendlyUrlFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade
     */
    protected $pluginCrudExtensionFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\ImageFacade|null
     */
    protected $imageFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryRepository $categoryRepository
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade $pluginCrudExtensionFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade|null $imageFacade
     */
    public function __construct(
        CategoryRepository $categoryRepository,
        FriendlyUrlFacade $friendlyUrlFacade,
        PluginCrudExtensionFacade $pluginCrudExtensionFacade,
        Domain $domain,
        ?ImageFacade $imageFacade = null
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->friendlyUrlFacade = $friendlyUrlFacade;
        $this->pluginCrudExtensionFacade = $pluginCrudExtensionFacade;
        $this->domain = $domain;
        $this->imageFacade = $imageFacade;
    }

    /**
     * @required
     * @internal This function will be replaced by constructor injection in next major
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageFacade
     */
    public function setImageFacade(ImageFacade $imageFacade): void
    {
        if ($this->imageFacade !== null && $this->imageFacade !== $imageFacade) {
            throw new BadMethodCallException(sprintf('Method "%s" has been already called and cannot be called multiple times.', __METHOD__));
        }
        if ($this->imageFacade === null) {
            @trigger_error(sprintf('The %s() method is deprecated and will be removed in the next major. Use the constructor injection instead.', __METHOD__), E_USER_DEPRECATED);
            $this->imageFacade = $imageFacade;
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @return \Shopsys\FrameworkBundle\Model\Category\CategoryData
     */
    public function createFromCategory(Category $category): CategoryData
    {
        $categoryData = new CategoryData();
        $this->fillFromCategory($categoryData, $category);

        return $categoryData;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\CategoryData
     */
    public function create(): CategoryData
    {
        $categoryData = new CategoryData();
        $this->fillNew($categoryData);

        return $categoryData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryData $categoryData
     */
    protected function fillNew(CategoryData $categoryData)
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
    protected function fillFromCategory(CategoryData $categoryData, Category $category)
    {
        $categoryData->name = $category->getNames();
        $categoryData->parent = $category->getParent();

        foreach ($this->domain->getAllIds() as $domainId) {
            $categoryData->seoMetaDescriptions[$domainId] = $category->getSeoMetaDescription($domainId);
            $categoryData->seoTitles[$domainId] = $category->getSeoTitle($domainId);
            $categoryData->seoH1s[$domainId] = $category->getSeoH1($domainId);
            $categoryData->descriptions[$domainId] = $category->getDescription($domainId);
            $categoryData->enabled[$domainId] = $category->isEnabled($domainId);

            $mainFriendlyUrl = $this->friendlyUrlFacade->findMainFriendlyUrl($domainId, 'front_product_list', $category->getId());
            $categoryData->urls->mainFriendlyUrlsByDomainId[$domainId] = $mainFriendlyUrl;
        }

        $categoryData->pluginData = $this->pluginCrudExtensionFacade->getAllData('category', $category->getId());
        $categoryData->image->orderedImages = $this->imageFacade->getImagesByEntityIndexedById($category, null);
    }
}
