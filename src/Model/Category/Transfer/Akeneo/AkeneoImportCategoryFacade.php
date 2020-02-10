<?php

declare(strict_types=1);

namespace App\Model\Category\Transfer\Akeneo;

use App\Component\Akeneo\Transfer\AbstractAkeneoImportTransfer;
use App\Component\Akeneo\Transfer\AkeneoImportTransferDependency;
use App\Model\Category\Category;
use App\Model\Category\CategoryDataFactory;
use App\Model\Category\CategoryFacade;
use Generator;
use Shopsys\FrameworkBundle\Model\Category\CategoryVisibilityRepository;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade;

class AkeneoImportCategoryFacade extends AbstractAkeneoImportTransfer
{
    public const ROOT_CATEGORY_CODE = 'eshop__ecommere';
    public const PREFIX_CATEGORY_CODE = 'eshop__';

    /**
     * @var \App\Model\Category\Transfer\Akeneo\CategoryTransferAkeneoFacade
     */
    private $categoryTransferAkeneoFacade;

    /**
     * @var \App\Model\Category\Transfer\Akeneo\CategoryTransferAkeneoValidator
     */
    private $categoryTransferAkeneoValidator;

    /**
     * @var \App\Model\Category\Transfer\Akeneo\CategoryTransferAkeneoMapper
     */
    private $categoryTransferAkeneoMapper;

    /**
     * @var \App\Model\Category\CategoryFacade
     */
    private $categoryFacade;

    /**
     * @var array
     */
    private $akeneoCategoriesDataForOrdering;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\CategoryVisibilityRepository
     */
    private $categoryVisibilityRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade
     */
    private $productVisibilityFacade;

    /**
     * @var \App\Model\Category\CategoryDataFactory
     */
    private $categoryDataFactory;

    /**
     * @var int[]
     */
    private $notTransferredCategoriesIds = [];

    /**
     * @var int
     */
    private $categoriesFromAkeneoCountBeforeTransfer = 0;

    /**
     * @param \App\Component\Akeneo\Transfer\AkeneoImportTransferDependency $akeneoImportTransferDependency
     * @param \App\Model\Category\Transfer\Akeneo\CategoryTransferAkeneoFacade $categoryTransferAkeneoFacade
     * @param \App\Model\Category\Transfer\Akeneo\CategoryTransferAkeneoValidator $categoryTransferAkeneoValidator
     * @param \App\Model\Category\Transfer\Akeneo\CategoryTransferAkeneoMapper $categoryTransferAkeneoMapper
     * @param \App\Model\Category\CategoryFacade $categoryFacade
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryVisibilityRepository $categoryVisibilityRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade $productVisibilityFacade
     * @param \App\Model\Category\CategoryDataFactory $categoryDataFactory
     */
    public function __construct(
        AkeneoImportTransferDependency $akeneoImportTransferDependency,
        CategoryTransferAkeneoFacade $categoryTransferAkeneoFacade,
        CategoryTransferAkeneoValidator $categoryTransferAkeneoValidator,
        CategoryTransferAkeneoMapper $categoryTransferAkeneoMapper,
        CategoryFacade $categoryFacade,
        CategoryVisibilityRepository $categoryVisibilityRepository,
        ProductVisibilityFacade $productVisibilityFacade,
        CategoryDataFactory $categoryDataFactory
    ) {
        parent::__construct($akeneoImportTransferDependency);

        $this->categoryTransferAkeneoFacade = $categoryTransferAkeneoFacade;
        $this->categoryTransferAkeneoValidator = $categoryTransferAkeneoValidator;
        $this->categoryTransferAkeneoMapper = $categoryTransferAkeneoMapper;
        $this->categoryFacade = $categoryFacade;
        $this->categoryVisibilityRepository = $categoryVisibilityRepository;
        $this->productVisibilityFacade = $productVisibilityFacade;
        $this->categoryDataFactory = $categoryDataFactory;
    }

    /**
     * @return \Generator
     */
    protected function getData(): Generator
    {
        return $this->categoryTransferAkeneoFacade->getAllCategories();
    }

    protected function doBeforeTransfer(): void
    {
        $this->logger->addInfo('Transfer categories data from Akeneo ...');
        $this->loadAkeneoCategoryIds();
    }

    /**
     * @inheritDoc
     */
    protected function processItem(array $akeneoCategoryData): void
    {
        if ($akeneoCategoryData['code'] === self::ROOT_CATEGORY_CODE) {
            return;
        }

        if (strpos($akeneoCategoryData['code'], self::PREFIX_CATEGORY_CODE) === false) {
            return;
        }

        $this->categoryTransferAkeneoValidator->validate($akeneoCategoryData);

        $category = $this->categoryFacade->findByAkeneoCode($akeneoCategoryData['code']);
        $categoryData = $this->categoryTransferAkeneoMapper->mapAkeneoCategoryDataToCategoryData($akeneoCategoryData, $category);

        if ($category === null) {
            $this->logger->addInfo(sprintf('Creating category code: %s', $categoryData->akeneoCode));
            $this->categoryFacade->create($categoryData);
        } else {
            $this->logger->addInfo(sprintf('Updating category code: %s', $category->getAkeneoCode()));
            $this->categoryFacade->edit($category->getId(), $categoryData);
            $this->dropTransferredAkeneoCategory($category);
        }

        $this->akeneoCategoriesDataForOrdering[] = $akeneoCategoryData;
    }

    protected function doAfterTransfer(): void
    {
        $this->logger->addInfo('Save ordering for categories...');
        $this->deleteRestNotTransferredCategories();
        $this->saveOrderingCategories();

        $this->logger->addInfo('Refreshing categories and products visibility...');
        $this->categoryVisibilityRepository->refreshCategoriesVisibility();
        $this->productVisibilityFacade->refreshProductsVisibility();

        $this->logger->addInfo('Transfer is done.');
    }

    private function saveOrderingCategories(): void
    {
        foreach ($this->akeneoCategoriesDataForOrdering as $akeneoCategoryData) {
            $category = $this->categoryFacade->getByAkeneoCode($akeneoCategoryData['code']);
            $categoryId = $category->getId();

            if ($akeneoCategoryData['parent'] === self::ROOT_CATEGORY_CODE) {
                $parentCategoryId = null;
            } else {
                $parentCategory = $this->categoryFacade->findByAkeneoCode($akeneoCategoryData['parent']);
                if ($parentCategory === null) {
                    $this->logger->addWarning(sprintf('Parent category with akeneo code %s not found in eshop', $akeneoCategoryData['parent']));
                    $this->logger->addWarning(sprintf('Hiding category with akeneo code %s', $akeneoCategoryData['code']));

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
            $this->categoryFacade->editOrdering($ordering);
        }
    }

    private function loadAkeneoCategoryIds(): void
    {
        $this->notTransferredCategoriesIds = array_flip($this->categoryFacade->getAllAkeneoCategoryIds());
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
        if ($this->categoriesFromAkeneoCountBeforeTransfer === count($this->notTransferredCategoriesIds)) {
            $this->logger->addError(sprintf('Import categories from Akeneo probably failed, because all categories with akeneo code should be deleted. Deletion was aborted.'));
            return;
        }
        foreach ($this->notTransferredCategoriesIds as $akeneoCode => $categoryId) {
            $this->categoryFacade->deleteById($categoryId);
            $this->logger->addWarning(sprintf('Deleted category with ID: %s', $akeneoCode));
        }
    }
}
