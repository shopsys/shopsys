<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\ZboziBundle\Model\ZboziCategory;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Category\CategoryRepository;

class ZboziCategoryFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ZboziCategoryRepository $zboziCategoryRepository,
        protected readonly CategoryRepository $categoryRepository,
        protected readonly ZboziCategoryFactory $zboziCategoryFactory,
    ) {
    }

    /**
     * @param \Shopsys\ProductFeed\ZboziBundle\Model\ZboziCategory\ZboziCategoryData[] $newZboziCategoriesData
     */
    public function saveZboziCategories(array $newZboziCategoriesData, string $locale): void
    {
        $existingZboziCategories = $this->zboziCategoryRepository->getAllIndexedByZboziId($locale);

        $this->removeOldZboziCategories($newZboziCategoriesData, $existingZboziCategories);

        foreach ($newZboziCategoriesData as $newZboziCategoryData) {
            if (!array_key_exists($newZboziCategoryData->zboziId, $existingZboziCategories)) {
                $newZboziCategory = $this->zboziCategoryFactory->create($newZboziCategoryData);
                $this->em->persist($newZboziCategory);
            } else {
                $existingZboziCategory = $existingZboziCategories[$newZboziCategoryData->zboziId];
                $newZboziCategoryData->categories = $existingZboziCategory->getCategories();
                $existingZboziCategory->edit($newZboziCategoryData);
            }
        }

        $this->em->flush();
    }

    /**
     * @param \Shopsys\ProductFeed\ZboziBundle\Model\ZboziCategory\ZboziCategoryData[] $newZboziCategoriesData
     * @param \Shopsys\ProductFeed\ZboziBundle\Model\ZboziCategory\ZboziCategory[] $existingZboziCategoriesIndexedByIds
     */
    protected function removeOldZboziCategories(
        array $newZboziCategoriesData,
        array $existingZboziCategoriesIndexedByIds,
    ): void {
        $existingZboziCategoriesIds = array_keys($existingZboziCategoriesIndexedByIds);

        $newZboziCategoriesIds = [];

        foreach ($newZboziCategoriesData as $newZboziCategoryData) {
            $newZboziCategoriesIds[] = $newZboziCategoryData->zboziId;
        }

        $categoryIdsToDelete = array_diff($existingZboziCategoriesIds, $newZboziCategoriesIds);

        foreach ($categoryIdsToDelete as $categoryIdToDelete) {
            $this->em->remove($existingZboziCategoriesIndexedByIds[$categoryIdToDelete]);
        }
    }

    public function changeZboziCategoryForCategoryId(
        int $categoryId,
        ZboziCategory $zboziCategory,
        string $locale,
    ): void {
        $oldZboziCategoryByCategoryId = $this->zboziCategoryRepository->findByCategoryIdAndLocale($categoryId, $locale);

        $category = $this->categoryRepository->getById($categoryId);

        if ($oldZboziCategoryByCategoryId === null) {
            $zboziCategory->addCategory($category);
        } elseif ($oldZboziCategoryByCategoryId->getZboziId() !== $zboziCategory->getZboziId()) {
            $oldZboziCategoryByCategoryId->removeCategory($category);
            $zboziCategory->addCategory($category);
        }

        $this->em->flush();
    }

    public function findByCategoryIdAndLocale(int $categoryId, string $locale): ?ZboziCategory
    {
        return $this->zboziCategoryRepository->findByCategoryIdAndLocale($categoryId, $locale);
    }

    public function findFullNameByCategoryIdAndLocale(int $categoryId, string $locale): ?string
    {
        return $this->findByCategoryIdAndLocale($categoryId, $locale)?->getFullName();
    }

    public function removeZboziCategoryForCategoryId(int $categoryId, string $locale): void
    {
        $zboziCategory = $this->zboziCategoryRepository->findByCategoryIdAndLocale($categoryId, $locale);

        if ($zboziCategory === null) {
            return;
        }

        $category = $this->categoryRepository->getById($categoryId);
        $zboziCategory->removeCategory($category);

        $this->em->flush();
    }

    public function getOneByZboziIdAndLocale(int $zboziId, string $locale): ZboziCategory
    {
        return $this->zboziCategoryRepository->getOneByZboziIdAndLocale($zboziId, $locale);
    }

    /**
     * @return \Shopsys\ProductFeed\ZboziBundle\Model\ZboziCategory\ZboziCategory[]
     */
    public function getAllIndexedByZboziId(string $locale): array
    {
        return $this->zboziCategoryRepository->getAllIndexedByZboziId($locale);
    }
}
