<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Category\CategoryRepository;

class HeurekaCategoryFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly HeurekaCategoryRepository $heurekaCategoryRepository,
        protected readonly CategoryRepository $categoryRepository,
        protected readonly HeurekaCategoryFactory $heurekaCategoryFactory,
    ) {
    }

    /**
     * @param \Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory\HeurekaCategoryData[] $newHeurekaCategoriesData
     */
    public function saveHeurekaCategories(array $newHeurekaCategoriesData, string $locale): void
    {
        $existingHeurekaCategories = $this->heurekaCategoryRepository->getAllIndexedByHeurekaId($locale);

        $this->removeOldHeurekaCategories($newHeurekaCategoriesData, $existingHeurekaCategories);

        foreach ($newHeurekaCategoriesData as $newHeurekaCategoryData) {
            if (!array_key_exists($newHeurekaCategoryData->heurekaId, $existingHeurekaCategories)) {
                $newHeurekaCategory = $this->heurekaCategoryFactory->create($newHeurekaCategoryData);
                $this->em->persist($newHeurekaCategory);
            } else {
                $existingHeurekaCategory = $existingHeurekaCategories[$newHeurekaCategoryData->heurekaId];
                $newHeurekaCategoryData->categories = $existingHeurekaCategory->getCategories();
                $existingHeurekaCategory->edit($newHeurekaCategoryData);
            }
        }

        $this->em->flush();
    }

    /**
     * @param \Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory\HeurekaCategoryData[] $newHeurekaCategoriesData
     * @param \Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory\HeurekaCategory[] $existingHeurekaCategoriesIndexedByIds
     */
    protected function removeOldHeurekaCategories(
        array $newHeurekaCategoriesData,
        array $existingHeurekaCategoriesIndexedByIds,
    ): void {
        $existingHeurekaCategoriesIds = array_keys($existingHeurekaCategoriesIndexedByIds);

        $newHeurekaCategoriesIds = [];

        foreach ($newHeurekaCategoriesData as $newHeurekaCategoryData) {
            $newHeurekaCategoriesIds[] = $newHeurekaCategoryData->heurekaId;
        }

        $categoryIdsToDelete = array_diff($existingHeurekaCategoriesIds, $newHeurekaCategoriesIds);

        foreach ($categoryIdsToDelete as $categoryIdToDelete) {
            $this->em->remove($existingHeurekaCategoriesIndexedByIds[$categoryIdToDelete]);
        }
    }

    public function changeHeurekaCategoryForCategoryId(
        int $categoryId,
        HeurekaCategory $heurekaCategory,
        string $locale,
    ): void {
        $oldHeurekaCategoryByCategoryId = $this->heurekaCategoryRepository->findByCategoryIdAndLocale($categoryId, $locale);

        $category = $this->categoryRepository->getById($categoryId);

        if ($oldHeurekaCategoryByCategoryId === null) {
            $heurekaCategory->addCategory($category);
        } elseif ($oldHeurekaCategoryByCategoryId->getHeurekaId() !== $heurekaCategory->getHeurekaId()) {
            $oldHeurekaCategoryByCategoryId->removeCategory($category);
            $heurekaCategory->addCategory($category);
        }

        $this->em->flush();
    }

    public function findByCategoryIdAndLocale(int $categoryId, string $locale): ?HeurekaCategory
    {
        return $this->heurekaCategoryRepository->findByCategoryIdAndLocale($categoryId, $locale);
    }

    public function removeHeurekaCategoryForCategoryId(int $categoryId, string $locale): void
    {
        $heurekaCategory = $this->heurekaCategoryRepository->findByCategoryIdAndLocale($categoryId, $locale);

        if ($heurekaCategory === null) {
            return;
        }

        $category = $this->categoryRepository->getById($categoryId);
        $heurekaCategory->removeCategory($category);

        $this->em->flush();
    }

    public function getOneByHeurekaIdAndLocale(int $heurekaId, string $locale): HeurekaCategory
    {
        return $this->heurekaCategoryRepository->getOneByHeurekaIdAndLocale($heurekaId, $locale);
    }

    /**
     * @return \Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory\HeurekaCategory[]
     */
    public function getAllIndexedByHeurekaId(string $locale): array
    {
        return $this->heurekaCategoryRepository->getAllIndexedByHeurekaId($locale);
    }
}
