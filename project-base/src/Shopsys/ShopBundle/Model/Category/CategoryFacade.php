<?php

namespace Shopsys\FrameworkBundle\Model\Category;

use Doctrine\ORM\EntityManager;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Component\Utils;
use Shopsys\FrameworkBundle\Model\Category\Detail\CategoryDetailFactory;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Product;

class CategoryFacade
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\CategoryService
     */
    private $categoryService;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\CategoryVisibilityRecalculationScheduler
     */
    private $categoryVisibilityRecalculationScheduler;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\Detail\CategoryDetailFactory
     */
    private $categoryDetailFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade
     */
    private $friendlyUrlFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\ImageFacade
     */
    private $imageFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade
     */
    private $pluginCrudExtensionFacade;

    public function __construct(
        EntityManager $em,
        CategoryRepository $categoryRepository,
        CategoryService $categoryService,
        Domain $domain,
        CategoryVisibilityRecalculationScheduler $categoryVisibilityRecalculationScheduler,
        CategoryDetailFactory $categoryDetailFactory,
        FriendlyUrlFacade $friendlyUrlFacade,
        ImageFacade $imageFacade,
        PluginCrudExtensionFacade $pluginCrudExtensionFacade
    ) {
        $this->em = $em;
        $this->categoryRepository = $categoryRepository;
        $this->categoryService = $categoryService;
        $this->domain = $domain;
        $this->categoryVisibilityRecalculationScheduler = $categoryVisibilityRecalculationScheduler;
        $this->categoryDetailFactory = $categoryDetailFactory;
        $this->friendlyUrlFacade = $friendlyUrlFacade;
        $this->imageFacade = $imageFacade;
        $this->pluginCrudExtensionFacade = $pluginCrudExtensionFacade;
    }

    /**
     * @param int $categoryId
     * @return \Shopsys\FrameworkBundle\Model\Category\Category
     */
    public function getById($categoryId)
    {
        return $this->categoryRepository->getById($categoryId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryData $categoryData
     * @return \Shopsys\FrameworkBundle\Model\Category\Category
     */
    public function create(CategoryData $categoryData)
    {
        $rootCategory = $this->getRootCategory();
        $category = $this->categoryService->create($categoryData, $rootCategory);
        $this->em->persist($category);
        $this->em->flush($category);
        $this->createCategoryDomains($category, $this->domain->getAll());
        $this->refreshCategoryDomains($category, $categoryData);
        $this->friendlyUrlFacade->createFriendlyUrls('front_product_list', $category->getId(), $category->getNames());
        $this->imageFacade->uploadImage($category, $categoryData->image, null);

        $this->pluginCrudExtensionFacade->saveAllData('category', $category->getId(), $categoryData->pluginData);

        $this->categoryVisibilityRecalculationScheduler->scheduleRecalculationWithoutDependencies();

        return $category;
    }

    /**
     * @param int $categoryId
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryData $categoryData
     * @return \Shopsys\FrameworkBundle\Model\Category\Category
     */
    public function edit($categoryId, CategoryData $categoryData)
    {
        $rootCategory = $this->getRootCategory();
        $category = $this->categoryRepository->getById($categoryId);
        $this->categoryService->edit($category, $categoryData, $rootCategory);
        $this->em->flush();
        $this->refreshCategoryDomains($category, $categoryData);
        $this->friendlyUrlFacade->saveUrlListFormData('front_product_list', $category->getId(), $categoryData->urls);
        $this->friendlyUrlFacade->createFriendlyUrls('front_product_list', $category->getId(), $category->getNames());
        $this->imageFacade->uploadImage($category, $categoryData->image, null);

        $this->pluginCrudExtensionFacade->saveAllData('category', $category->getId(), $categoryData->pluginData);

        $this->categoryVisibilityRecalculationScheduler->scheduleRecalculation($category);

        return $category;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig[] $domainConfigs
     */
    private function createCategoryDomains(Category $category, array $domainConfigs)
    {
        $toFlush = [];

        foreach ($domainConfigs as $domainConfig) {
            $domainId = $domainConfig->getId();
            $categoryDomain = new CategoryDomain($category, $domainId);

            $this->em->persist($categoryDomain);
            $toFlush[] = $categoryDomain;
        }

        $this->em->flush($toFlush);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryData $categoryData
     */
    private function refreshCategoryDomains(Category $category, CategoryData $categoryData)
    {
        $toFlush = [];
        $categoryDomains = $this->categoryRepository->getCategoryDomainsByCategory($category);

        foreach ($categoryDomains as $categoryDomain) {
            $domainId = $categoryDomain->getDomainId();

            $categoryDomain->setSeoTitle(Utils::getArrayValue($categoryData->seoTitles, $domainId));
            $categoryDomain->setSeoMetaDescription(Utils::getArrayValue($categoryData->seoMetaDescriptions, $domainId));
            $categoryDomain->setSeoH1(Utils::getArrayValue($categoryData->seoH1s, $domainId));
            $categoryDomain->setDescription(Utils::getArrayValue($categoryData->descriptions, $domainId));
            $categoryDomain->setHidden(in_array($domainId, $categoryData->hiddenOnDomains, true));

            $toFlush[] = $categoryDomain;
        }

        $this->em->flush($toFlush);
    }

    /**
     * @param int $categoryId
     */
    public function deleteById($categoryId)
    {
        $category = $this->categoryRepository->getById($categoryId);
        $this->categoryService->setChildrenAsSiblings($category);
        // Normally, UnitOfWork performs UPDATEs on children after DELETE of main entity.
        // We need to update `parent` attribute of children first.
        $this->em->flush();

        $this->pluginCrudExtensionFacade->removeAllData('category', $category->getId());

        $this->em->remove($category);
        $this->em->flush();
    }

    /**
     * @param int[] $parentIdByCategoryId
     */
    public function editOrdering($parentIdByCategoryId)
    {
        // eager-load all categories into identity map
        $this->categoryRepository->getAll();

        $rootCategory = $this->getRootCategory();
        foreach ($parentIdByCategoryId as $categoryId => $parentId) {
            if ($parentId === null) {
                $parent = $rootCategory;
            } else {
                $parent = $this->categoryRepository->getById($parentId);
            }

            $category = $this->categoryRepository->getById($categoryId);
            $category->setParent($parent);
            // Category must be flushed after parent change before calling moveDown for correct calculation of lft and rgt
            $this->em->flush($category);

            $this->categoryRepository->moveDown($category, CategoryRepository::MOVE_DOWN_TO_BOTTOM);
        }

        $this->em->flush();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getAll()
    {
        return $this->categoryRepository->getAll();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category[] $selectedCategories
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getAllCategoriesOfCollapsedTree(array $selectedCategories)
    {
        $categories = $this->categoryRepository->getAllCategoriesOfCollapsedTree($selectedCategories);

        return $categories;
    }

    /**
     * @param int $domainId
     * @param string $locale
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getFullPathsIndexedByIdsForDomain($domainId, $locale)
    {
        return $this->categoryRepository->getFullPathsIndexedByIdsForDomain($domainId, $locale);
    }

    /**
     * @param string $locale
     * @return \Shopsys\FrameworkBundle\Model\Category\Detail\CategoryDetail[]
     */
    public function getAllCategoryDetails($locale)
    {
        $categories = $this->categoryRepository->getPreOrderTreeTraversalForAllCategories($locale);
        $categoryDetails = $this->categoryDetailFactory->createDetailsHierarchy($categories);

        return $categoryDetails;
    }

    /**
     * @param int $domainId
     * @param string $locale
     * @return \Shopsys\FrameworkBundle\Model\Category\Detail\CategoryDetail[]
     */
    public function getVisibleCategoryDetailsForDomain($domainId, $locale)
    {
        $categories = $this->categoryRepository->getPreOrderTreeTraversalForVisibleCategoriesByDomain($domainId, $locale);

        $categoryDetails = $this->categoryDetailFactory->createDetailsHierarchy($categories);

        return $categoryDetails;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getVisibleCategoriesInPathFromRootOnDomain(Category $category, $domainId)
    {
        return $this->categoryRepository->getVisibleCategoriesInPathFromRootOnDomain($category, $domainId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $parentCategory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\FrameworkBundle\Model\Category\Detail\LazyLoadedCategoryDetail[]
     */
    public function getVisibleLazyLoadedCategoryDetailsForParent(Category $parentCategory, DomainConfig $domainConfig)
    {
        $categories = $this->categoryRepository->getTranslatedVisibleSubcategoriesByDomain($parentCategory, $domainConfig);

        $categoryDetails = $this->categoryDetailFactory->createLazyLoadedDetails($categories, $domainConfig);

        return $categoryDetails;
    }

    /**
     * @param int $domainId
     * @param string $locale
     * @param string $searchText
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getVisibleByDomainAndSearchText($domainId, $locale, $searchText)
    {
        $categories = $this->categoryRepository->getVisibleByDomainIdAndSearchText(
            $domainId,
            $locale,
            $searchText
        );

        return $categories;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getAllVisibleChildrenByCategoryAndDomainId(Category $category, $domainId)
    {
        return $this->categoryRepository->getAllVisibleChildrenByCategoryAndDomainId($category, $domainId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getAllWithoutBranch(Category $category)
    {
        return $this->categoryRepository->getAllWithoutBranch($category);
    }

    /**
     * @param string|null $searchText
     * @param int $limit
     * @return \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult
     */
    public function getSearchAutocompleteCategories($searchText, $limit)
    {
        $page = 1;

        $paginationResult = $this->categoryRepository->getPaginationResultForSearchVisible(
            $searchText,
            $this->domain->getId(),
            $this->domain->getLocale(),
            $page,
            $limit
        );

        return $paginationResult;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getProductMainCategoriesIndexedByDomainId(Product $product)
    {
        $mainCategoriesIndexedByDomainId = [];
        foreach ($this->domain->getAll() as $domainConfig) {
            $mainCategoriesIndexedByDomainId[$domainConfig->getId()] = $this->categoryRepository->findProductMainCategoryOnDomain(
                $product,
                $domainConfig->getId()
            );
        }

        return $mainCategoriesIndexedByDomainId;
    }

    /**
     * @param Product $product
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Category\Category
     */
    public function getProductMainCategoryByDomainId(Product $product, $domainId)
    {
        return $this->categoryRepository->getProductMainCategoryOnDomain($product, $domainId);
    }

    /**
     * @param Product $product
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Category\Category|null
     */
    public function findProductMainCategoryByDomainId(Product $product, $domainId)
    {
        return $this->categoryRepository->findProductMainCategoryOnDomain($product, $domainId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return string[]
     */
    public function getCategoryNamesInPathFromRootToProductMainCategoryOnDomain(Product $product, DomainConfig $domainConfig)
    {
        return $this->categoryRepository->getCategoryNamesInPathFromRootToProductMainCategoryOnDomain($product, $domainConfig);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\Category
     */
    public function getRootCategory()
    {
        return $this->categoryRepository->getRootCategory();
    }

    /**
     * @param int $domainId
     * @param int $categoryId
     * @return \Shopsys\FrameworkBundle\Model\Category\Category
     */
    public function getVisibleOnDomainById($domainId, $categoryId)
    {
        $category = $this->getById($categoryId);
        $categoryDomain = $category->getCategoryDomain($domainId);
        if (!$categoryDomain->isVisible()) {
            $message = 'Category ID ' . $categoryId . ' is not visible on domain ID ' . $domainId;
            throw new \Shopsys\FrameworkBundle\Model\Category\Exception\CategoryNotFoundException($message);
        }

        return $category;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category[] $categories
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @param int $domainId
     * @return int[]
     */
    public function getListableProductCountsIndexedByCategoryId(
        array $categories,
        PricingGroup $pricingGroup,
        $domainId
    ) {
        return $this->categoryRepository->getListableProductCountsIndexedByCategoryId(
            $categories,
            $pricingGroup,
            $domainId
        );
    }
}
