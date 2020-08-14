<?php

declare(strict_types=1);

namespace App\Model\Category;

use App\Component\Cache\TwigCachedMenuFacade;
use App\Model\Category\CategoryProductSeries\CategoryProductSeriesFacade;
use App\Model\Product\Product;
use App\Twig\Cache\TwigCacheFacade;
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
use Shopsys\FrameworkBundle\Model\Category\CategoryRepository;
use Shopsys\FrameworkBundle\Model\Category\CategoryVisibilityRecalculationScheduler;
use Shopsys\FrameworkBundle\Model\Category\CategoryWithLazyLoadedVisibleChildrenFactory;
use Shopsys\FrameworkBundle\Model\Category\CategoryWithPreloadedChildrenFactory;

/**
 * @property \App\Model\Category\CategoryRepository $categoryRepository
 * @method \App\Model\Category\Category getById(int $categoryId)
 * @method \App\Model\Category\Category getByUuid(string $categoryUuid)
 * @method \App\Model\Category\Category[] getTranslatedAll(\Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig)
 * @method \App\Model\Category\Category[] getAllCategoriesOfCollapsedTree(\App\Model\Category\Category[] $selectedCategories)
 * @method \App\Model\Category\Category[] getFullPathsIndexedByIdsForDomain(int $domainId, string $locale)
 * @method \App\Model\Category\Category[] getVisibleCategoriesInPathFromRootOnDomain(\App\Model\Category\Category $category, int $domainId)
 * @method \Shopsys\FrameworkBundle\Model\Category\CategoryWithLazyLoadedVisibleChildren[] getCategoriesWithLazyLoadedVisibleChildrenForParent(\App\Model\Category\Category $parentCategory, \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig)
 * @method \App\Model\Category\Category[] getVisibleByDomainAndSearchText(int $domainId, string $locale, string $searchText)
 * @method \App\Model\Category\Category[] getAllVisibleChildrenByCategoryAndDomainId(\App\Model\Category\Category $category, int $domainId)
 * @method \App\Model\Category\Category[] getTranslatedAllWithoutBranch(\App\Model\Category\Category $category, \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig)
 * @method \App\Model\Category\Category[]|null[] getProductMainCategoriesIndexedByDomainId(\App\Model\Product\Product $product)
 * @method \App\Model\Category\Category getProductMainCategoryByDomainId(\App\Model\Product\Product $product, int $domainId)
 * @method \App\Model\Category\Category|null findProductMainCategoryByDomainId(\App\Model\Product\Product $product, int $domainId)
 * @method string[] getCategoryNamesInPathFromRootToProductMainCategoryOnDomain(\App\Model\Product\Product $product, \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig)
 * @method \App\Model\Category\Category getRootCategory()
 * @method \App\Model\Category\Category getVisibleOnDomainById(int $domainId, int $categoryId)
 * @method int[] getListableProductCountsIndexedByCategoryId(\App\Model\Category\Category[] $categories, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup, int $domainId)
 * @property \App\Component\Domain\Domain $domain
 * @property \App\Component\Image\ImageFacade $imageFacade
 */
class CategoryFacade extends BaseCategoryFacade
{
    /**
     * @var \App\Model\Category\CategoryParameterFacade
     */
    private $categoryParameterFacade;

    /**
     * @var \App\Model\Category\CategoryProductSeries\CategoryProductSeriesFacade
     */
    private $categoryProductSeriesFacade;

    /**
     * @var \App\Component\Cache\TwigCachedMenuFacade
     */
    private $twigCachedMenuFacade;

    /**
     * @var \App\Twig\Cache\TwigCacheFacade
     */
    private TwigCacheFacade $twigCacheFacade;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Category\CategoryRepository $categoryRepository
     * @param \App\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryVisibilityRecalculationScheduler $categoryVisibilityRecalculationScheduler
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \App\Component\Image\ImageFacade $imageFacade
     * @param \Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade $pluginCrudExtensionFacade
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryWithPreloadedChildrenFactory $categoryWithPreloadedChildrenFactory
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryWithLazyLoadedVisibleChildrenFactory $categoryWithLazyLoadedVisibleChildrenFactory
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryFactoryInterface $categoryFactory
     * @param \App\Model\Category\CategoryParameterFacade $categoryParameterFacade
     * @param \App\Model\Category\CategoryProductSeries\CategoryProductSeriesFacade $categoryProductSeriesFacade
     * @param \App\Component\Cache\TwigCachedMenuFacade $twigCachedMenuFacade
     * @param \App\Twig\Cache\TwigCacheFacade $twigCacheFacade
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
        CategoryParameterFacade $categoryParameterFacade,
        CategoryProductSeriesFacade $categoryProductSeriesFacade,
        TwigCachedMenuFacade $twigCachedMenuFacade,
        TwigCacheFacade $twigCacheFacade
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
            $categoryFactory
        );
        $this->categoryParameterFacade = $categoryParameterFacade;
        $this->categoryProductSeriesFacade = $categoryProductSeriesFacade;
        $this->twigCachedMenuFacade = $twigCachedMenuFacade;
        $this->twigCacheFacade = $twigCacheFacade;
    }

    /**
     * @param \App\Model\Category\CategoryData $categoryData
     * @return \App\Model\Category\Category
     */
    public function create(CategoryData $categoryData): BaseCategory
    {
        /** @var \App\Model\Category\Category $category */
        $category = parent::create($categoryData);
        $this->categoryParameterFacade->saveRelation($category, $categoryData->parametersCollapsed, $categoryData->parametersCollapsed);
        $this->categoryProductSeriesFacade->saveProductSeriesForCategory($category, $categoryData->categoryProductSeries);
        $this->twigCachedMenuFacade->invalidateCachedMenuByCategory($category);

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
        $this->categoryParameterFacade->saveRelation($category, $categoryData->parametersPosition, $categoryData->parametersCollapsed);
        $this->categoryProductSeriesFacade->saveProductSeriesForCategory($category, $categoryData->categoryProductSeries);
        $this->twigCachedMenuFacade->invalidateCachedMenuByCategory($category);

        foreach ($this->domain->getAllIds() as $domainId) {
            $this->twigCacheFacade->invalidateByKey($this->twigCacheFacade::SLIGHTLY_CHANGING_PARTS_ON_HOMEPAGE, $domainId);
        }

        return $category;
    }

    /**
     * @param int[]|null[] $parentIdByCategoryId
     */
    public function editOrdering($parentIdByCategoryId)
    {
        parent::editOrdering($parentIdByCategoryId);
        $this->twigCachedMenuFacade->invalidateCachedMenuByCategory($this->getRootCategory());
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

    /*
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @return \App\Model\Category\Category[]
     */

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @return array
     */
    public function getAllProductCategoriesByProductAndDomainId(Product $product, int $domainId): array
    {
        return $this->categoryRepository->getAllProductCategoriesOnDomain($product, $domainId);
    }

    /**
     * @param \App\Model\Category\Category $parentCategory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \App\Model\Category\Category[]
     */
    public function getTranslatedVisibleSubcategoriesByDomain(Category $parentCategory, DomainConfig $domainConfig): array
    {
        return  $this->categoryRepository->getTranslatedVisibleSubcategoriesByDomain($parentCategory, $domainConfig);
    }

    /**
     * @param \App\Model\Category\Category $destinationCategory
     * @return array
     */
    public function getCategoriesInPath(Category $destinationCategory): array
    {
        $categoriesInPathWithoutRoot = array_slice($this->categoryRepository->getPath($destinationCategory), 1);
        return $categoriesInPathWithoutRoot;
    }

    /**
     * @param \App\Model\Category\Category $destinationCategory
     * @param string $locale
     * @param string $delimiter
     * @return string
     */
    public function getCategoriesNamesInPathAsString(Category $destinationCategory, string $locale, string $delimiter = '/'): string
    {
        $categoriesInPath = $this->getCategoriesInPath($destinationCategory);

        $categoriesNamesInPath = [];
        foreach ($categoriesInPath as $category) {
            $categoriesNamesInPath[] = $category->getName($locale);
        }

        return implode($delimiter, $categoriesNamesInPath);
    }
}
