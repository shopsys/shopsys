<?php

namespace Shopsys\FrameworkBundle\Model\Category;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Product;

class CategoryFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\CategoryService
     */
    protected $categoryService;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\CategoryVisibilityRecalculationScheduler
     */
    protected $categoryVisibilityRecalculationScheduler;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade
     */
    protected $friendlyUrlFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\ImageFacade
     */
    protected $imageFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade
     */
    protected $pluginCrudExtensionFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\CategoryWithPreloadedChildrenFactory
     */
    protected $categoryWithPreloadedChildrenFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\CategoryWithLazyLoadedVisibleChildrenFactory
     */
    protected $categoryWithLazyLoadedVisibleChildrenFactory;

    public function __construct(
        EntityManagerInterface $em,
        CategoryRepository $categoryRepository,
        CategoryService $categoryService,
        Domain $domain,
        CategoryVisibilityRecalculationScheduler $categoryVisibilityRecalculationScheduler,
        FriendlyUrlFacade $friendlyUrlFacade,
        ImageFacade $imageFacade,
        PluginCrudExtensionFacade $pluginCrudExtensionFacade,
        CategoryWithPreloadedChildrenFactory $categoryWithPreloadedChildrenFactory,
        CategoryWithLazyLoadedVisibleChildrenFactory $categoryWithLazyLoadedVisibleChildrenFactory
    ) {
        $this->em = $em;
        $this->categoryRepository = $categoryRepository;
        $this->categoryService = $categoryService;
        $this->domain = $domain;
        $this->categoryVisibilityRecalculationScheduler = $categoryVisibilityRecalculationScheduler;
        $this->friendlyUrlFacade = $friendlyUrlFacade;
        $this->imageFacade = $imageFacade;
        $this->pluginCrudExtensionFacade = $pluginCrudExtensionFacade;
        $this->categoryWithPreloadedChildrenFactory = $categoryWithPreloadedChildrenFactory;
        $this->categoryWithLazyLoadedVisibleChildrenFactory = $categoryWithLazyLoadedVisibleChildrenFactory;
    }

    /**
     * @param int $categoryId
     */
    public function getById($categoryId): \Shopsys\FrameworkBundle\Model\Category\Category
    {
        return $this->categoryRepository->getById($categoryId);
    }

    public function create(CategoryData $categoryData): \Shopsys\FrameworkBundle\Model\Category\Category
    {
        $rootCategory = $this->getRootCategory();
        $category = $this->categoryService->create($categoryData, $rootCategory);
        $this->em->persist($category);
        $this->em->flush($category);
        $this->friendlyUrlFacade->createFriendlyUrls('front_product_list', $category->getId(), $category->getNames());
        $this->imageFacade->uploadImage($category, $categoryData->image->uploadedFiles, null);

        $this->pluginCrudExtensionFacade->saveAllData('category', $category->getId(), $categoryData->pluginData);

        $this->categoryVisibilityRecalculationScheduler->scheduleRecalculationWithoutDependencies();

        return $category;
    }

    /**
     * @param int $categoryId
     */
    public function edit($categoryId, CategoryData $categoryData): \Shopsys\FrameworkBundle\Model\Category\Category
    {
        $rootCategory = $this->getRootCategory();
        $category = $this->categoryRepository->getById($categoryId);
        $category = $this->categoryService->edit($category, $categoryData, $rootCategory);
        $this->em->flush();
        $this->friendlyUrlFacade->saveUrlListFormData('front_product_list', $category->getId(), $categoryData->urls);
        $this->friendlyUrlFacade->createFriendlyUrls('front_product_list', $category->getId(), $category->getNames());
        $this->imageFacade->uploadImage($category, $categoryData->image->uploadedFiles, null);

        $this->pluginCrudExtensionFacade->saveAllData('category', $category->getId(), $categoryData->pluginData);

        $this->categoryVisibilityRecalculationScheduler->scheduleRecalculation($category);

        return $category;
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
    public function getTranslatedAll(DomainConfig $domainConfig): array
    {
        return $this->categoryRepository->getTranslatedAll($domainConfig);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category[] $selectedCategories
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getAllCategoriesOfCollapsedTree(array $selectedCategories): array
    {
        $categories = $this->categoryRepository->getAllCategoriesOfCollapsedTree($selectedCategories);

        return $categories;
    }

    /**
     * @param int $domainId
     * @param string $locale
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getFullPathsIndexedByIdsForDomain($domainId, $locale): array
    {
        return $this->categoryRepository->getFullPathsIndexedByIdsForDomain($domainId, $locale);
    }

    /**
     * @param string $locale
     * @return \Shopsys\FrameworkBundle\Model\Category\CategoryWithPreloadedChildren[]
     */
    public function getAllCategoriesWithPreloadedChildren($locale): array
    {
        $categories = $this->categoryRepository->getPreOrderTreeTraversalForAllCategories($locale);
        $categoriesWithPreloadedChildren = $this->categoryWithPreloadedChildrenFactory->createCategoriesWithPreloadedChildren($categories);

        return $categoriesWithPreloadedChildren;
    }

    /**
     * @param int $domainId
     * @param string $locale
     * @return \Shopsys\FrameworkBundle\Model\Category\CategoryWithPreloadedChildren[]
     */
    public function getVisibleCategoriesWithPreloadedChildrenForDomain($domainId, $locale): array
    {
        $categories = $this->categoryRepository->getPreOrderTreeTraversalForVisibleCategoriesByDomain($domainId, $locale);

        $categoriesWithPreloadedChildren = $this->categoryWithPreloadedChildrenFactory->createCategoriesWithPreloadedChildren($categories);

        return $categoriesWithPreloadedChildren;
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getVisibleCategoriesInPathFromRootOnDomain(Category $category, $domainId): array
    {
        return $this->categoryRepository->getVisibleCategoriesInPathFromRootOnDomain($category, $domainId);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\CategoryWithLazyLoadedVisibleChildren[]
     */
    public function getCategoriesWithLazyLoadedVisibleChildrenForParent(Category $parentCategory, DomainConfig $domainConfig): array
    {
        $categories = $this->categoryRepository->getTranslatedVisibleSubcategoriesByDomain($parentCategory, $domainConfig);

        $categoriesWithLazyLoadedVisibleChildren = $this->categoryWithLazyLoadedVisibleChildrenFactory
            ->createCategoriesWithLazyLoadedVisibleChildren($categories, $domainConfig);

        return $categoriesWithLazyLoadedVisibleChildren;
    }

    /**
     * @param int $domainId
     * @param string $locale
     * @param string $searchText
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getVisibleByDomainAndSearchText($domainId, $locale, $searchText): array
    {
        $categories = $this->categoryRepository->getVisibleByDomainIdAndSearchText(
            $domainId,
            $locale,
            $searchText
        );

        return $categories;
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getAllVisibleChildrenByCategoryAndDomainId(Category $category, $domainId): array
    {
        return $this->categoryRepository->getAllVisibleChildrenByCategoryAndDomainId($category, $domainId);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getTranslatedAllWithoutBranch(Category $category, DomainConfig $domainConfig): array
    {
        return $this->categoryRepository->getTranslatedAllWithoutBranch($category, $domainConfig);
    }

    /**
     * @param string|null $searchText
     * @param int $limit
     */
    public function getSearchAutocompleteCategories($searchText, $limit): \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult
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
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getProductMainCategoriesIndexedByDomainId(Product $product): array
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
     * @param int $domainId
     */
    public function getProductMainCategoryByDomainId(Product $product, $domainId): \Shopsys\FrameworkBundle\Model\Category\Category
    {
        return $this->categoryRepository->getProductMainCategoryOnDomain($product, $domainId);
    }

    /**
     * @param int $domainId
     */
    public function findProductMainCategoryByDomainId(Product $product, $domainId): ?\Shopsys\FrameworkBundle\Model\Category\Category
    {
        return $this->categoryRepository->findProductMainCategoryOnDomain($product, $domainId);
    }

    /**
     * @return string[]
     */
    public function getCategoryNamesInPathFromRootToProductMainCategoryOnDomain(Product $product, DomainConfig $domainConfig): array
    {
        return $this->categoryRepository->getCategoryNamesInPathFromRootToProductMainCategoryOnDomain($product, $domainConfig);
    }

    public function getRootCategory(): \Shopsys\FrameworkBundle\Model\Category\Category
    {
        return $this->categoryRepository->getRootCategory();
    }

    /**
     * @param int $domainId
     * @param int $categoryId
     */
    public function getVisibleOnDomainById($domainId, $categoryId): \Shopsys\FrameworkBundle\Model\Category\Category
    {
        $category = $this->getById($categoryId);
        if (!$category->isVisible($domainId)) {
            $message = 'Category ID ' . $categoryId . ' is not visible on domain ID ' . $domainId;
            throw new \Shopsys\FrameworkBundle\Model\Category\Exception\CategoryNotFoundException($message);
        }

        return $category;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category[] $categories
     * @param int $domainId
     * @return int[]
     */
    public function getListableProductCountsIndexedByCategoryId(
        array $categories,
        PricingGroup $pricingGroup,
        $domainId
    ): array {
        return $this->categoryRepository->getListableProductCountsIndexedByCategoryId(
            $categories,
            $pricingGroup,
            $domainId
        );
    }
}
