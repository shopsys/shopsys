<?php

declare(strict_types=1);

namespace App\Model\Category;

use App\Model\Category\LinkedCategory\LinkedCategoryFacade;
use App\Model\Product\ProductOnCurrentDomainElasticFacade;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Category\Category as BaseCategory;
use Shopsys\FrameworkBundle\Model\Category\CategoryData;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade as BaseCategoryFacade;
use Shopsys\FrameworkBundle\Model\Category\CategoryFactoryInterface;
use Shopsys\FrameworkBundle\Model\Category\CategoryParameterFacade;
use Shopsys\FrameworkBundle\Model\Category\CategoryRepository;
use Shopsys\FrameworkBundle\Model\Category\CategoryVisibilityRecalculationScheduler;
use Shopsys\FrameworkBundle\Model\Category\CategoryWithLazyLoadedVisibleChildrenFactory;
use Shopsys\FrameworkBundle\Model\Category\CategoryWithPreloadedChildrenFactory;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @property \App\Model\Category\CategoryRepository $categoryRepository
 * @method \App\Model\Category\Category getById(int $categoryId)
 * @method \App\Model\Category\Category getByUuid(string $categoryUuid)
 * @method \App\Model\Category\Category[] getAllCategoriesOfCollapsedTree(\App\Model\Category\Category[] $selectedCategories)
 * @method \App\Model\Category\Category[] getFullPathsIndexedByIdsForDomain(int $domainId, string $locale)
 * @method \App\Model\Category\Category[] getVisibleCategoriesInPathFromRootOnDomain(\App\Model\Category\Category $category, int $domainId)
 * @method \Shopsys\FrameworkBundle\Model\Category\CategoryWithLazyLoadedVisibleChildren[] getCategoriesWithLazyLoadedVisibleChildrenForParent(\App\Model\Category\Category $parentCategory, \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig)
 * @method \App\Model\Category\Category[] getVisibleByDomainAndSearchText(int $domainId, string $locale, string $searchText)
 * @method \App\Model\Category\Category[] getAllVisibleChildrenByCategoryAndDomainId(\App\Model\Category\Category $category, int $domainId)
 * @method \App\Model\Category\Category[]|null[] getProductMainCategoriesIndexedByDomainId(\App\Model\Product\Product $product)
 * @method \App\Model\Category\Category getProductMainCategoryByDomainId(\App\Model\Product\Product $product, int $domainId)
 * @method \App\Model\Category\Category|null findProductMainCategoryByDomainId(\App\Model\Product\Product $product, int $domainId)
 * @method string[] getCategoryNamesInPathFromRootToProductMainCategoryOnDomain(\App\Model\Product\Product $product, \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig)
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
 * @property \App\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
 * @method dispatchCategoryEvent(\App\Model\Category\Category $category, string $eventType)
 */
class CategoryFacade extends BaseCategoryFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Category\CategoryRepository $categoryRepository
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryVisibilityRecalculationScheduler $categoryVisibilityRecalculationScheduler
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \App\Component\Image\ImageFacade $imageFacade
     * @param \Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade $pluginCrudExtensionFacade
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryWithPreloadedChildrenFactory $categoryWithPreloadedChildrenFactory
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryWithLazyLoadedVisibleChildrenFactory $categoryWithLazyLoadedVisibleChildrenFactory
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryFactory $categoryFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher $productRecalculationDispatcher
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryParameterFacade $categoryParameterFacade
     * @param \App\Model\Category\LinkedCategory\LinkedCategoryFacade $linkedCategoryFacade
     * @param \App\Model\Product\ProductOnCurrentDomainElasticFacade $productOnCurrentDomainElasticFacade
     */
    public function __construct(
        EntityManagerInterface $em,
        CategoryRepository $categoryRepository,
        Domain $domain,
        CategoryVisibilityRecalculationScheduler $categoryVisibilityRecalculationScheduler,
        FriendlyUrlFacade $friendlyUrlFacade,
        ImageFacade $imageFacade,
        PluginCrudExtensionFacade $pluginCrudExtensionFacade,
        CategoryWithPreloadedChildrenFactory $categoryWithPreloadedChildrenFactory,
        CategoryWithLazyLoadedVisibleChildrenFactory $categoryWithLazyLoadedVisibleChildrenFactory,
        CategoryFactoryInterface $categoryFactory,
        ProductRecalculationDispatcher $productRecalculationDispatcher,
        EventDispatcherInterface $eventDispatcher,
        CategoryParameterFacade $categoryParameterFacade,
        private readonly LinkedCategoryFacade $linkedCategoryFacade,
        private readonly ProductOnCurrentDomainElasticFacade $productOnCurrentDomainElasticFacade,
    ) {
        parent::__construct(
            $em,
            $categoryRepository,
            $domain,
            $categoryVisibilityRecalculationScheduler,
            $friendlyUrlFacade,
            $imageFacade,
            $pluginCrudExtensionFacade,
            $categoryWithPreloadedChildrenFactory,
            $categoryWithLazyLoadedVisibleChildrenFactory,
            $categoryFactory,
            $productRecalculationDispatcher,
            $eventDispatcher,
            $categoryParameterFacade,
        );
    }

    /**
     * @param \App\Model\Category\CategoryData $categoryData
     * @return \App\Model\Category\Category
     */
    public function create(CategoryData $categoryData): BaseCategory
    {
        /** @var \App\Model\Category\Category $category */
        $category = parent::create($categoryData);
        $this->linkedCategoryFacade->updateLinkedCategories($category, $categoryData->linkedCategories);

        return $category;
    }

    /**
     * @param int $categoryId
     * @param \App\Model\Category\CategoryData $categoryData
     * @return \App\Model\Category\Category
     */
    public function edit($categoryId, CategoryData $categoryData): BaseCategory
    {
        /** @var \App\Model\Category\Category $category */
        $category = parent::edit($categoryId, $categoryData);
        $this->linkedCategoryFacade->updateLinkedCategories($category, $categoryData->linkedCategories);

        return $category;
    }

    /**
     * @param string $akeneoCode
     * @return \App\Model\Category\Category|null
     */
    public function findByAkeneoCode(string $akeneoCode): ?Category
    {
        return $this->categoryRepository->findByAkeneoCode($akeneoCode);
    }

    /**
     * @param string $akeneoCode
     * @return \App\Model\Category\Category
     */
    public function getByAkeneoCode(string $akeneoCode): Category
    {
        return $this->categoryRepository->getByAkeneoCode($akeneoCode);
    }

    /**
     * @return int[]
     */
    public function getAllAkeneoCategoryIds(): array
    {
        return $this->categoryRepository->getAllAkeneoCategoryIds();
    }

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
     * @param string $locale
     * @return string[]
     */
    public function getFullPathsIndexedByIds(string $locale): array
    {
        return $this->categoryRepository->getFullPathsIndexedByIds($locale);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @return array
     */
    public function getCategoriesOfProductByFilterData(ProductFilterData $productFilterData): array
    {
        $categoryIds = $this->productOnCurrentDomainElasticFacade->getCategoryIdsForFilterData($productFilterData);
        $categories = $this->categoryRepository->getCategoriesByIds($categoryIds);

        $categoriesIndexedByIds = [];

        foreach ($categories as $category) {
            $categoriesIndexedByIds[$category->getId()] = $category;
        }

        $sortedCategories = [];

        foreach ($categoryIds as $categoryId) {
            if (!array_key_exists($categoryId, $categoriesIndexedByIds)) {
                continue;
            }
            $sortedCategories[] = $categoriesIndexedByIds[$categoryId];
        }

        return $sortedCategories;
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
