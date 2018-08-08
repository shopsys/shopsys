<?php

namespace Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Category\CategoryRepository;

class HeurekaCategoryFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory\HeurekaCategoryRepository
     */
    protected $heurekaCategoryRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\CategoryRepository
     */
    protected $categoryRepository;

    public function __construct(
        EntityManagerInterface $em,
        HeurekaCategoryRepository $heurekaCategoryRepository,
        CategoryRepository $categoryRepository
    ) {
        $this->em = $em;
        $this->heurekaCategoryRepository = $heurekaCategoryRepository;
        $this->categoryRepository = $categoryRepository;
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
                $newHeurekaCategory = new HeurekaCategory($newHeurekaCategoryData);
                $this->em->persist($newHeurekaCategory);
            } else {
                $existingHeurekaCategory = $existingHeurekaCategories[$newHeurekaCategoryData->id];
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
        array $existingHeurekaCategoriesIndexedByIds
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

    public function findByCategoryId(int $categoryId): ?\Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory\HeurekaCategory
    {
        return $this->heurekaCategoryRepository->findByCategoryId($categoryId);
    }

    public function removeHeurekaCategoryForCategoryId(int $categoryId): void
    {
        $heurekaCategory = $this->heurekaCategoryRepository->findByCategoryId($categoryId);

        if ($heurekaCategory !== null) {
            $category = $this->categoryRepository->getById($categoryId);
            $heurekaCategory->removeCategory($category);

            $this->em->flush();
        }
    }

    public function getOneById(int $id): \Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory\HeurekaCategory
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
