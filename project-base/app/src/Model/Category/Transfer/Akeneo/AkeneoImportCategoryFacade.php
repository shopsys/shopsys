<?php

declare(strict_types=1);

namespace App\Model\Category\Transfer\Akeneo;

use App\Component\Akeneo\Transfer\AbstractAkeneoImportTransfer;
use App\Component\Akeneo\Transfer\AkeneoImportTransferDependency;
use App\Model\Category\Category;
use App\Model\Category\CategoryDataFactory;
use App\Model\Category\CategoryFacade;
use Generator;
use Shopsys\FrameworkBundle\Model\Category\CategoryNestedSetCalculator;
use Shopsys\FrameworkBundle\Model\Category\CategoryVisibilityRepository;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade;

class AkeneoImportCategoryFacade extends AbstractAkeneoImportTransfer
{
    public const ROOT_CATEGORY_CODE = 'eshop__ecommere';
    public const ROOT_CATEGORY_CODE_PROD = 'eshop__ecommerce';
    public const PREFIX_CATEGORY_CODE = 'eshop__';

    /**
     * @var array<int, mixed>
     */
    private array $akeneoCategoriesDataForOrdering;

    /**
     * @var int[]
     */
    private array $notTransferredCategoriesIds = [];

    private int $categoriesFromAkeneoCountBeforeTransfer = 0;

    /**
     * @param \App\Component\Akeneo\Transfer\AkeneoImportTransferDependency $akeneoImportTransferDependency
     * @param \App\Model\Category\Transfer\Akeneo\CategoryTransferAkeneoFacade $categoryTransferAkeneoFacade
     * @param \App\Model\Category\Transfer\Akeneo\CategoryTransferAkeneoValidator $categoryTransferAkeneoValidator
     * @param \App\Model\Category\Transfer\Akeneo\CategoryTransferAkeneoMapper $categoryTransferAkeneoMapper
     * @param \App\Model\Category\CategoryFacade $categoryFacade
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryVisibilityRepository $categoryVisibilityRepository
     * @param \App\Model\Product\ProductVisibilityFacade $productVisibilityFacade
     * @param \App\Model\Category\CategoryDataFactory $categoryDataFactory
     */
    public function __construct(
        AkeneoImportTransferDependency $akeneoImportTransferDependency,
        private CategoryTransferAkeneoFacade $categoryTransferAkeneoFacade,
        private CategoryTransferAkeneoValidator $categoryTransferAkeneoValidator,
        private CategoryTransferAkeneoMapper $categoryTransferAkeneoMapper,
        private CategoryFacade $categoryFacade,
        private CategoryVisibilityRepository $categoryVisibilityRepository,
        private ProductVisibilityFacade $productVisibilityFacade,
        private CategoryDataFactory $categoryDataFactory,
    ) {
        parent::__construct($akeneoImportTransferDependency);
    }

    /**
     * @return \Generator
     */
    protected function getData(): Generator
    {
        foreach ($this->categoryTransferAkeneoFacade->getAllCategories() as $category) {
            yield $category;
        }
    }

    protected function doBeforeTransfer(): void
    {
        $this->logger->info('Transfer categories data from Akeneo ...');
        $this->loadAkeneoCategoryIds();
    }

    /**
     * {@inheritdoc}
     */
    protected function processItem($akeneoCategoryData): void
    {
        if ($akeneoCategoryData['code'] === self::ROOT_CATEGORY_CODE || $akeneoCategoryData['code'] === self::ROOT_CATEGORY_CODE_PROD) {
            return;
        }

        if (strpos($akeneoCategoryData['code'], self::PREFIX_CATEGORY_CODE) === false) {
            return;
        }

        $this->categoryTransferAkeneoValidator->validate($akeneoCategoryData);

        $category = $this->categoryFacade->findByAkeneoCode($akeneoCategoryData['code']);
        $categoryData = $this->categoryTransferAkeneoMapper->mapAkeneoCategoryDataToCategoryData($akeneoCategoryData, $category);

        if ($category === null) {
            $this->logger->info(sprintf('Creating category code: %s', $categoryData->akeneoCode));
            $this->categoryFacade->create($categoryData);
        } else {
            $this->logger->info(sprintf('Updating category code: %s', $category->getAkeneoCode()));
            $this->categoryFacade->edit($category->getId(), $categoryData);
            $this->dropTransferredAkeneoCategory($category);
        }

        $this->akeneoCategoriesDataForOrdering[] = $akeneoCategoryData;
    }

    protected function doAfterTransfer(): void
    {
        $this->logger->info('Save ordering for categories...');
        $this->deleteRestNotTransferredCategories();
        $this->saveOrderingCategories();

        $this->logger->info('Refreshing categories and products visibility...');
        $this->categoryVisibilityRepository->refreshCategoriesVisibility();
        $this->productVisibilityFacade->refreshProductsVisibility();

        $this->logger->info('Transfer is done.');
    }

    private function saveOrderingCategories(): void
    {
        foreach ($this->akeneoCategoriesDataForOrdering as $akeneoCategoryData) {
            $category = $this->categoryFacade->getByAkeneoCode($akeneoCategoryData['code']);
            $categoryId = $category->getId();

            if ($akeneoCategoryData['parent'] === self::ROOT_CATEGORY_CODE || $akeneoCategoryData['parent'] === self::ROOT_CATEGORY_CODE_PROD) {
                $parentCategoryId = null;
            } else {
                $parentCategory = $this->categoryFacade->findByAkeneoCode($akeneoCategoryData['parent']);

                if ($parentCategory === null) {
                    $this->logger->warning(sprintf('Parent category with akeneo code %s not found in eshop', $akeneoCategoryData['parent']));
                    $this->logger->warning(sprintf('Hiding category with akeneo code %s', $akeneoCategoryData['code']));

                    $categoryData = $this->categoryDataFactory->createFromCategory($category);

                    $categoryData->enabled = array_map(static function () {
                        return false;
                    }, $categoryData->enabled);

                    $this->categoryFacade->edit($category->getId(), $categoryData);

                    continue;
                }

                $parentCategoryId = $parentCategory->getId();
            }

            $ordering = [
                $categoryId => $parentCategoryId,
            ];

            $this->categoryFacade->reorderByNestedSetValues(
                CategoryNestedSetCalculator::calculateNestedSetFromAdjacencyList($ordering),
            );
        }
    }

    private function loadAkeneoCategoryIds(): void
    {
        $allAkeneoCategoryIds = $this->categoryFacade->getAllAkeneoCategoryIds();
        $this->notTransferredCategoriesIds = array_combine($allAkeneoCategoryIds, $allAkeneoCategoryIds);
        $this->categoriesFromAkeneoCountBeforeTransfer = count($this->notTransferredCategoriesIds);
    }

    /**
     * @param \App\Model\Category\Category $category
     */
    private function dropTransferredAkeneoCategory(Category $category): void
    {
        if (array_key_exists($category->getId(), $this->notTransferredCategoriesIds)) {
            unset($this->notTransferredCategoriesIds[$category->getId()]);
        }
    }

    private function deleteRestNotTransferredCategories(): void
    {
        if ($this->categoriesFromAkeneoCountBeforeTransfer === 0 && count($this->notTransferredCategoriesIds) === 0) {
            return;
        }

        if ($this->categoriesFromAkeneoCountBeforeTransfer === count($this->notTransferredCategoriesIds)) {
            $this->logger->error(sprintf('Import categories from Akeneo probably failed, because all categories with akeneo code should be deleted. Deletion was aborted.'));

            return;
        }

        foreach ($this->notTransferredCategoriesIds as $categoryId) {
            $this->categoryFacade->deleteById($categoryId);
            $this->logger->warning(sprintf('Deleted category with ID: %s', $categoryId));
        }
    }

    /**
     * @return string
     */
    public function getTransferIdentifier(): string
    {
        return 'categoryTransfer';
    }

    /**
     * @return string
     */
    public function getTransferName(): string
    {
        return t('Categories transfer');
    }
}
