<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Category;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Parameter;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Category\Exception\CategoryNotFoundException;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainElasticFacade;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CategoryFacade
{
    protected const int INCREMENT_DUE_TO_MISSING_ROOT_CATEGORY = 1;

    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly CategoryRepository $categoryRepository,
        protected readonly Domain $domain,
        protected readonly CategoryVisibilityRecalculationScheduler $categoryVisibilityRecalculationScheduler,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly ImageFacade $imageFacade,
        protected readonly PluginCrudExtensionFacade $pluginCrudExtensionFacade,
        protected readonly CategoryWithPreloadedChildrenFactory $categoryWithPreloadedChildrenFactory,
        protected readonly CategoryWithLazyLoadedVisibleChildrenFactory $categoryWithLazyLoadedVisibleChildrenFactory,
        protected readonly CategoryFactory $categoryFactory,
        protected readonly ProductRecalculationDispatcher $productRecalculationDispatcher,
        protected readonly EventDispatcherInterface $eventDispatcher,
        protected readonly CategoryParameterFacade $categoryParameterFacade,
        protected readonly ProductOnCurrentDomainElasticFacade $productOnCurrentDomainElasticFacade,
    ) {
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
     * @param int[] $categoryIds
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getByIds(array $categoryIds): array
    {
        return $this->categoryRepository->getCategoriesByIds($categoryIds);
    }

    public function getByUuid(string $categoryUuid): Category
    {
        return $this->categoryRepository->getOneByUuid($categoryUuid);
    }

    public function getVisibleOnDomainByUuid(int $domainId, string $categoryUuid): Category
    {
        $category = $this->getByUuid($categoryUuid);

        if (!$category->isVisible($domainId)) {
            throw new CategoryNotFoundException(
                sprintf('Category with UUID "%s" is not visible on domain ID "%s"', $categoryUuid, $domainId),
            );
        }

        return $category;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\Category
     */
    public function create(CategoryData $categoryData)
    {
        $rootCategory = $this->getRootCategory();
        $category = $this->categoryFactory->create($categoryData, $rootCategory);
        $this->em->persist($category);
        $this->em->flush();
        $this->friendlyUrlFacade->createFriendlyUrls('front_product_list', $category->getId(), $category->getNames());
        $this->imageFacade->manageImages($category, $categoryData->image);

        $this->categoryParameterFacade->saveRelation($category, $categoryData->parametersPosition, $categoryData->parametersCollapsed);

        $this->pluginCrudExtensionFacade->saveAllData('category', $category->getId(), $categoryData->pluginData);

        $this->categoryVisibilityRecalculationScheduler->scheduleRecalculation();

        $this->dispatchCategoryEvent($category, CategoryEvent::CREATE);

        return $category;
    }

    /**
     * @param int $categoryId
     * @return \Shopsys\FrameworkBundle\Model\Category\Category
     */
    public function edit($categoryId, CategoryData $categoryData)
    {
        $rootCategory = $this->getRootCategory();
        $category = $this->categoryRepository->getById($categoryId);
        $originalNames = $category->getNames();

        $category->edit($categoryData);

        if ($category->getParent() === null) {
            $category->setParent($rootCategory);
        }
        $this->em->flush();
        $this->friendlyUrlFacade->saveUrlListFormData('front_product_list', $category->getId(), $categoryData->urls);
        $this->createFriendlyUrlsWhenRenamed($category, $originalNames);

        $this->imageFacade->manageImages($category, $categoryData->image);

        $this->categoryParameterFacade->saveRelation($category, $categoryData->parametersPosition, $categoryData->parametersCollapsed);

        $this->pluginCrudExtensionFacade->saveAllData('category', $category->getId(), $categoryData->pluginData);

        $this->categoryVisibilityRecalculationScheduler->scheduleRecalculation();

        $this->dispatchCategoryEvent($category, CategoryEvent::UPDATE);

        return $category;
    }

    /**
     * @param int $categoryId
     */
    public function deleteById($categoryId): void
    {
        $category = $this->categoryRepository->getById($categoryId);

        $this->dispatchCategoryEvent($category, CategoryEvent::DELETE);

        $this->categoryVisibilityRecalculationScheduler->scheduleRecalculation();

        foreach ($category->getChildren() as $child) {
            $child->setParent($category->getParent());
        }
        // Normally, UnitOfWork performs UPDATEs on children after DELETE of main entity.
        // We need to update `parent` attribute of children first.
        $this->em->flush();

        $this->pluginCrudExtensionFacade->removeAllData('category', $category->getId());

        $this->em->remove($category);
        $this->em->flush();
    }

    /**
     * @param array<int, array{id: string|int, parent_id: string|int|null, depth: int, left: int, right: int}> $categoriesOrderingData
     */
    public function reorderByNestedSetValues(array $categoriesOrderingData): void
    {
        $rootCategoryId = $this->getRootCategory()->getId();

        $query = $this->em->createQuery('
            UPDATE ' . Category::class . ' c 
            SET c.parent = :parent, c.level = :level, c.lft = :lft, c.rgt = :rgt 
            WHERE c.id = :id
        ');

        foreach ($categoriesOrderingData as $categoryOrderingData) {
            $parameters = new ArrayCollection([
                new Parameter('id', (int)$categoryOrderingData['id']),
                new Parameter('parent', $categoryOrderingData['parent_id'] ? (int)$categoryOrderingData['parent_id'] : $rootCategoryId),
                new Parameter('level', $categoryOrderingData['depth'] + static::INCREMENT_DUE_TO_MISSING_ROOT_CATEGORY),
                new Parameter('lft', $categoryOrderingData['left'] + static::INCREMENT_DUE_TO_MISSING_ROOT_CATEGORY),
                new Parameter('rgt', $categoryOrderingData['right'] + static::INCREMENT_DUE_TO_MISSING_ROOT_CATEGORY),
            ]);
            $query->execute($parameters);
        }

        $this->productRecalculationDispatcher->dispatchAllProducts();
    }

    public function recalculateNestedSet(): bool
    {
        $errors = $this->categoryRepository->verify();

        $this->categoryRepository->recover();
        $this->em->flush();

        return $errors !== true;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getAllTranslated(string $locale): array
    {
        return $this->categoryRepository->getAllTranslated($locale);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category[] $selectedCategories
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getAllCategoriesOfCollapsedTree(array $selectedCategories)
    {
        return $this->categoryRepository->getAllCategoriesOfCollapsedTree($selectedCategories);
    }

    /**
     * @param int $domainId
     * @param string $locale
     * @return string[]
     */
    public function getFullPathsIndexedByIdsForDomain($domainId, $locale)
    {
        return $this->categoryRepository->getFullPathsIndexedByIdsForDomain($domainId, $locale);
    }

    /**
     * @param string $locale
     * @return \Shopsys\FrameworkBundle\Model\Category\CategoryWithPreloadedChildren[]
     */
    public function getAllCategoriesWithPreloadedChildren($locale)
    {
        $categories = $this->categoryRepository->getPreOrderTreeTraversalForAllCategories($locale);

        return $this->categoryWithPreloadedChildrenFactory->createCategoriesWithPreloadedChildren($categories);
    }

    /**
     * @param int $domainId
     * @param string $locale
     * @return \Shopsys\FrameworkBundle\Model\Category\CategoryWithPreloadedChildren[]
     */
    public function getVisibleCategoriesWithPreloadedChildrenForDomain($domainId, $locale)
    {
        $categories = $this->categoryRepository->getPreOrderTreeTraversalForVisibleCategoriesByDomain(
            $domainId,
            $locale,
        );

        return $this->categoryWithPreloadedChildrenFactory->createCategoriesWithPreloadedChildren($categories);
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getVisibleCategoriesInPathFromRootOnDomain(Category $category, $domainId)
    {
        return $this->categoryRepository->getVisibleCategoriesInPathFromRootOnDomain($category, $domainId);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\CategoryWithLazyLoadedVisibleChildren[]
     */
    public function getCategoriesWithLazyLoadedVisibleChildrenForParent(
        Category $parentCategory,
        DomainConfig $domainConfig,
    ) {
        $categories = $this->categoryRepository->getTranslatedVisibleSubcategoriesByDomain(
            $parentCategory,
            $domainConfig,
        );

        return $this->categoryWithLazyLoadedVisibleChildrenFactory
            ->createCategoriesWithLazyLoadedVisibleChildren($categories, $domainConfig);
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getAllVisibleChildrenByCategoryAndDomainId(Category $category, $domainId)
    {
        return $this->categoryRepository->getAllVisibleChildrenByCategoryAndDomainId($category, $domainId);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getAllTranslatedWithoutBranch(Category $category, string $locale): array
    {
        return $this->categoryRepository->getAllTranslatedWithoutBranch($category, $locale);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]|null[]
     */
    public function getProductMainCategoriesIndexedByDomainId(Product $product)
    {
        $mainCategoriesIndexedByDomainId = [];

        foreach ($this->domain->getAll() as $domainConfig) {
            $mainCategoriesIndexedByDomainId[$domainConfig->getId()] = $this->categoryRepository->findProductMainCategoryOnDomain(
                $product,
                $domainConfig->getId(),
            );
        }

        return $mainCategoriesIndexedByDomainId;
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Category\Category
     */
    public function getProductMainCategoryByDomainId(Product $product, $domainId)
    {
        return $this->categoryRepository->getProductMainCategoryOnDomain($product, $domainId);
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Category\Category|null
     */
    public function findProductMainCategoryByDomainId(Product $product, $domainId)
    {
        return $this->categoryRepository->findProductMainCategoryOnDomain($product, $domainId);
    }

    /**
     * @return string[]
     */
    public function getCategoryNamesInPathFromRootToProductMainCategoryOnDomain(
        Product $product,
        DomainConfig $domainConfig,
        ?string $locale = null,
    ): array {
        return $this->categoryRepository->getCategoryNamesInPathFromRootToProductMainCategoryOnDomain(
            $product,
            $domainConfig,
            $locale,
        );
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

        if (!$category->isVisible($domainId)) {
            $message = 'Category ID ' . $categoryId . ' is not visible on domain ID ' . $domainId;

            throw new CategoryNotFoundException($message);
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
        $domainId,
    ) {
        return $this->categoryRepository->getListableProductCountsIndexedByCategoryId(
            $categories,
            $pricingGroup,
            $domainId,
        );
    }

    protected function createFriendlyUrlsWhenRenamed(Category $category, array $originalNames): void
    {
        $changedNames = $this->getChangedNamesByLocale($category, $originalNames);

        if (count($changedNames) === 0) {
            return;
        }

        $this->friendlyUrlFacade->createFriendlyUrls(
            'front_product_list',
            $category->getId(),
            $changedNames,
        );
    }

    protected function getChangedNamesByLocale(Category $category, array $originalNames): array
    {
        $changedCategoryNames = [];

        foreach ($category->getNames() as $locale => $name) {
            if ($name !== $originalNames[$locale]) {
                $changedCategoryNames[$locale] = $name;
            }
        }

        return $changedCategoryNames;
    }

    public function getProductMainCategoryOnCurrentDomain(Product $product): Category
    {
        return $this->getProductMainCategoryByDomainId($product, $this->domain->getId());
    }

    /**
     * @see \Shopsys\FrameworkBundle\Model\Category\CategoryEvent class
     */
    protected function dispatchCategoryEvent(Category $category, string $eventType): void
    {
        $this->eventDispatcher->dispatch(new CategoryEvent($category), $eventType);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
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
     * @return string[]
     */
    public function getFullPathsIndexedByIds(string $locale): array
    {
        return $this->categoryRepository->getFullPathsIndexedByIds($locale);
    }

    /**
     * @return array<int, string>
     */
    public function getVisibilityOfCategoriesIndexedById(int $domainsCount): array
    {
        return $this->categoryRepository->getVisibilityOfCategoriesIndexedById($domainsCount);
    }
}
