<?php

declare(strict_types=1);

namespace App\Model\Category;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade as BaseCategoryFacade;

/**
 * @property \App\Model\Category\CategoryRepository $categoryRepository
 * @method \App\Model\Category\Category getById(int $categoryId)
 * @method \App\Model\Category\Category getByUuid(string $categoryUuid)
 * @method \App\Model\Category\Category[] getAllCategoriesOfCollapsedTree(\App\Model\Category\Category[] $selectedCategories)
 * @method \App\Model\Category\Category[] getFullPathsIndexedByIdsForDomain(int $domainId, string $locale)
 * @method \App\Model\Category\Category[] getVisibleCategoriesInPathFromRootOnDomain(\App\Model\Category\Category $category, int $domainId)
 * @method \Shopsys\FrameworkBundle\Model\Category\CategoryWithLazyLoadedVisibleChildren[] getCategoriesWithLazyLoadedVisibleChildrenForParent(\App\Model\Category\Category $parentCategory, \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig)
 * @method \App\Model\Category\Category[] getAllVisibleChildrenByCategoryAndDomainId(\App\Model\Category\Category $category, int $domainId)
 * @method \App\Model\Category\Category[]|null[] getProductMainCategoriesIndexedByDomainId(\App\Model\Product\Product $product)
 * @method \App\Model\Category\Category getProductMainCategoryByDomainId(\App\Model\Product\Product $product, int $domainId)
 * @method \App\Model\Category\Category|null findProductMainCategoryByDomainId(\App\Model\Product\Product $product, int $domainId)
 * @method string[] getCategoryNamesInPathFromRootToProductMainCategoryOnDomain(\App\Model\Product\Product $product, \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig, string|null $locale = null)
 * @method \App\Model\Category\Category getRootCategory()
 * @method \App\Model\Category\Category getVisibleOnDomainById(int $domainId, int $categoryId)
 * @method int[] getListableProductCountsIndexedByCategoryId(\App\Model\Category\Category[] $categories, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup, int $domainId)
 * @property \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
 * @property \App\Component\Image\ImageFacade $imageFacade
 * @method \App\Model\Category\Category[] getAllTranslated(string $locale)
 * @method \App\Model\Category\Category[] getAllTranslatedWithoutBranch(\App\Model\Category\Category $category, string $locale)
 * @method createFriendlyUrlsWhenRenamed(\App\Model\Category\Category $category, array $originalNames)
 * @method array getChangedNamesByLocale(\App\Model\Category\Category $category, array $originalNames)
 * @method \App\Model\Category\Category[] getByIds(int[] $categoryIds)
 * @method \App\Model\Category\Category getVisibleOnDomainByUuid(int $domainId, string $categoryUuid)
 * @method \App\Model\Category\Category getProductMainCategoryOnCurrentDomain(\App\Model\Product\Product $product)
 * @method dispatchCategoryEvent(\App\Model\Category\Category $category, string $eventType)
 * @method \App\Model\Category\Category[] getCategoriesOfProductByFilterData(\Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData)
 * @method __construct(\Doctrine\ORM\EntityManagerInterface $em, \App\Model\Category\CategoryRepository $categoryRepository, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \Shopsys\FrameworkBundle\Model\Category\CategoryVisibilityRecalculationScheduler $categoryVisibilityRecalculationScheduler, \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade, \App\Component\Image\ImageFacade $imageFacade, \Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade $pluginCrudExtensionFacade, \Shopsys\FrameworkBundle\Model\Category\CategoryWithPreloadedChildrenFactory $categoryWithPreloadedChildrenFactory, \Shopsys\FrameworkBundle\Model\Category\CategoryWithLazyLoadedVisibleChildrenFactory $categoryWithLazyLoadedVisibleChildrenFactory, \Shopsys\FrameworkBundle\Model\Category\CategoryFactory $categoryFactory, \Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher $productRecalculationDispatcher, \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher, \Shopsys\FrameworkBundle\Model\Category\CategoryParameterFacade $categoryParameterFacade, \Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainElasticFacade $productOnCurrentDomainElasticFacade)
 * @method \App\Model\Category\Category create(\App\Model\Category\CategoryData $categoryData)
 * @method \App\Model\Category\Category edit(int $categoryId, \App\Model\Category\CategoryData $categoryData)
 */
class CategoryFacade extends BaseCategoryFacade
{
    /**
     * @param \App\Model\Category\Category $destinationCategory
     * @return array
     */
    public function getCategoriesInPath(Category $destinationCategory): array
    {
        return array_slice($this->categoryRepository->getPath($destinationCategory), 1);
    }

    /**
     * @param \App\Model\Category\Category $destinationCategory
     * @param string $locale
     * @param string $delimiter
     * @return string
     */
    public function getCategoriesNamesInPathAsString(
        Category $destinationCategory,
        string $locale,
        string $delimiter = '/',
    ): string {
        $categoriesInPath = $this->getCategoriesInPath($destinationCategory);

        $categoriesNamesInPath = [];

        foreach ($categoriesInPath as $category) {
            $categoriesNamesInPath[] = $category->getName($locale);
        }

        return implode($delimiter, $categoriesNamesInPath);
    }

    /**
     * @param \App\Model\Category\Category $category
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \App\Model\Category\Category[]
     */
    public function getAllVisibleChildrenByCategoryAndDomainConfig(
        Category $category,
        DomainConfig $domainConfig,
    ): array {
        return $this->categoryRepository->getAllVisibleChildrenByCategoryAndDomainConfig($category, $domainConfig);
    }
}
