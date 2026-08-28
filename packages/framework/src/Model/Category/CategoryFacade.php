<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Category;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Parameter;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Form\TreeSelection\TreeSelectionDataProviderInterface;
use Shopsys\FrameworkBundle\Model\Category\Exception\CategoryNotFoundException;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainElasticFacade;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CategoryFacade implements TreeSelectionDataProviderInterface
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
        protected readonly CategoryFactory $categoryFactory,
        protected readonly ProductRecalculationDispatcher $productRecalculationDispatcher,
        protected readonly EventDispatcherInterface $eventDispatcher,
        protected readonly CategoryParameterFacade $categoryParameterFacade,
        protected readonly ProductOnCurrentDomainElasticFacade $productOnCurrentDomainElasticFacade,
    ) {
    }

    public function getById(int $categoryId): Category
    {
        return $this->categoryRepository->getById($categoryId);
    }

    /**
     * @param int[] $categoryIds
     * @return array<int, \Shopsys\FrameworkBundle\Model\Category\Category>
     */
    #[Override]
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

    public function create(CategoryData $categoryData): Category
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

    public function edit(int $categoryId, CategoryData $categoryData): Category
    {
        $rootCategory = $this->getRootCategory();
        $category = $this->categoryRepository->getById($categoryId);

        $category->edit($categoryData);

        if ($category->getParent() === null) {
            $category->setParent($rootCategory);
        }
        $this->em->flush();
        $this->friendlyUrlFacade->saveUrlListFormData('front_product_list', $category->getId(), $categoryData->urls);
        $this->friendlyUrlFacade->createFriendlyUrls('front_product_list', $category->getId(), $category->getNames());

        $this->imageFacade->manageImages($category, $categoryData->image);

        $this->categoryParameterFacade->saveRelation($category, $categoryData->parametersPosition, $categoryData->parametersCollapsed);

        $this->pluginCrudExtensionFacade->saveAllData('category', $category->getId(), $categoryData->pluginData);

        $this->categoryVisibilityRecalculationScheduler->scheduleRecalculation();

        $this->dispatchCategoryEvent($category, CategoryEvent::UPDATE);

        return $category;
    }

    public function deleteById(int $categoryId): void
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
    #[Override]
    public function getCollapsedTree(array $selectedCategories): array
    {
        return $this->categoryRepository->getAllCategoriesOfCollapsedTree($selectedCategories);
    }

    /**
     * @return string[]
     */
    public function getFullPathsIndexedByIdsForDomain(int $domainId, string $locale): array
    {
        return $this->categoryRepository->getFullPathsIndexedByIdsForDomain($domainId, $locale);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\CategoryWithPreloadedChildren[]
     */
    public function getAllCategoriesWithPreloadedChildren(string $locale): array
    {
        $categories = $this->categoryRepository->getPreOrderTreeTraversalForAllCategories($locale);

        return $this->categoryWithPreloadedChildrenFactory->createCategoriesWithPreloadedChildren($categories);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\CategoryWithPreloadedChildren[]
     */
    public function getVisibleCategoriesWithPreloadedChildrenForDomain(int $domainId, string $locale): array
    {
        $categories = $this->categoryRepository->getPreOrderTreeTraversalForVisibleCategoriesByDomain(
            $domainId,
            $locale,
        );

        return $this->categoryWithPreloadedChildrenFactory->createCategoriesWithPreloadedChildren($categories);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getVisibleCategoriesInPathFromRootOnDomain(Category $category, int $domainId): array
    {
        return $this->categoryRepository->getVisibleCategoriesInPathFromRootOnDomain($category, $domainId);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getAllVisibleChildrenByCategoryAndDomainId(Category $category, int $domainId): array
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
    public function getProductMainCategoriesIndexedByDomainId(Product $product): array
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

    public function getProductMainCategoryByDomainId(
        Product $product,
        int $domainId,
    ): Category {
        return $this->categoryRepository->getProductMainCategoryOnDomain($product, $domainId);
    }

    public function findProductMainCategoryByDomainId(
        Product $product,
        int $domainId,
    ): ?Category {
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

    public function getRootCategory(): Category
    {
        return $this->categoryRepository->getRootCategory();
    }

    public function getVisibleOnDomainById(
        int $domainId,
        int $categoryId,
    ): Category {
        $category = $this->getById($categoryId);

        if (!$category->isVisible($domainId)) {
            $message = 'Category ID ' . $categoryId . ' is not visible on domain ID ' . $domainId;

            throw new CategoryNotFoundException($message);
        }

        return $category;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category[] $categories
     * @return int[]
     */
    public function getListableProductCountsIndexedByCategoryId(
        array $categories,
        PricingGroup $pricingGroup,
        int $domainId,
    ): array {
        return $this->categoryRepository->getListableProductCountsIndexedByCategoryId(
            $categories,
            $pricingGroup,
            $domainId,
        );
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
