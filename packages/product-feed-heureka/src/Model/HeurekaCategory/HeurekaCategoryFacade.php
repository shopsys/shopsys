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
    public function saveHeurekaCategories(array $newHeurekaCategoriesData): void
    {
        $existingHeurekaCategories = $this->heurekaCategoryRepository->getAllIndexedById();

        $this->removeOldHeurekaCategories($newHeurekaCategoriesData, $existingHeurekaCategories);

        foreach ($newHeurekaCategoriesData as $newHeurekaCategoryData) {
            if (!array_key_exists($newHeurekaCategoryData->id, $existingHeurekaCategories)) {
                $newHeurekaCategory = $this->heurekaCategoryFactory->create($newHeurekaCategoryData);
                $this->em->persist($newHeurekaCategory);
            } else {
                $existingHeurekaCategory = $existingHeurekaCategories[$newHeurekaCategoryData->id];
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
            $newHeurekaCategoriesIds[] = $newHeurekaCategoryData->id;
        }

        $categoryIdsToDelete = array_diff($existingHeurekaCategoriesIds, $newHeurekaCategoriesIds);

        foreach ($categoryIdsToDelete as $categoryIdToDelete) {
            $this->em->remove($existingHeurekaCategoriesIndexedByIds[$categoryIdToDelete]);
        }
    }

    public function changeHeurekaCategoryForCategoryId(int $categoryId, HeurekaCategory $heurekaCategory): void
    {
        $oldHeurekaCategoryByCategoryId = $this->heurekaCategoryRepository->findByCategoryId($categoryId);

        $category = $this->categoryRepository->getById($categoryId);

        if ($oldHeurekaCategoryByCategoryId === null) {
            $heurekaCategory->addCategory($category);
        } elseif ($oldHeurekaCategoryByCategoryId->getId() !== $heurekaCategory->getId()) {
            $oldHeurekaCategoryByCategoryId->removeCategory($category);
            $heurekaCategory->addCategory($category);
        }

        $this->em->flush();
    }

    public function findByCategoryId(
        int $categoryId,
    ): ?HeurekaCategory {
        return $this->heurekaCategoryRepository->findByCategoryId($categoryId);
    }

    public function removeHeurekaCategoryForCategoryId(int $categoryId): void
    {
        $heurekaCategory = $this->heurekaCategoryRepository->findByCategoryId($categoryId);

        if ($heurekaCategory === null) {
            return;
        }

        $category = $this->categoryRepository->getById($categoryId);
        $heurekaCategory->removeCategory($category);

        $this->em->flush();
    }

    public function getOneById(int $id): HeurekaCategory
    {
        return $this->heurekaCategoryRepository->getOneById($id);
    }

    /**
     * @return \Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory\HeurekaCategory[]
     */
    public function getAllIndexedById(): array
    {
        return $this->heurekaCategoryRepository->getAllIndexedById();
    }
}
