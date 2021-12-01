<?php

declare(strict_types=1);

namespace App\Model\Category\LinkedCategory;

use App\Model\Category\Category;
use Doctrine\ORM\EntityManagerInterface;

class LinkedCategoryFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private EntityManagerInterface $em;

    /**
     * @var \App\Model\Category\LinkedCategory\LinkedCategoryRepository
     */
    private LinkedCategoryRepository $linkedCategoryRepository;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Category\LinkedCategory\LinkedCategoryRepository $linkedCategoryRepository
     */
    public function __construct(EntityManagerInterface $em, LinkedCategoryRepository $linkedCategoryRepository)
    {
        $this->em = $em;
        $this->linkedCategoryRepository = $linkedCategoryRepository;
    }

    /**
     * @param \App\Model\Category\Category $parentCategory
     * @param \App\Model\Category\Category[] $sortedCategories
     */
    public function updateLinkedCategories(Category $parentCategory, array $sortedCategories): void
    {
        $linkedCategories = $this->linkedCategoryRepository->getAllByParentCategory($parentCategory);

        foreach ($linkedCategories as $linkedCategory) {
            if (in_array($linkedCategory->getCategory(), $sortedCategories, true) === false) {
                $this->em->remove($linkedCategory);
            }
        }

        $sortedCategories = array_values($sortedCategories);
        foreach ($sortedCategories as $position => $category) {
            $linkedCategory = $this->findLinkedCategoryByCategory($linkedCategories, $category);
            if ($linkedCategory === null) {
                $linkedCategory = new LinkedCategory($parentCategory, $category, $position);
                $this->em->persist($linkedCategory);
            } else {
                $linkedCategory->setPosition($position);
            }
        }

        $this->em->flush();
    }

    /**
     * @param \App\Model\Category\LinkedCategory\LinkedCategory[] $linkedCategories
     * @param \App\Model\Category\Category $category
     * @return \App\Model\Category\LinkedCategory\LinkedCategory|null
     */
    private function findLinkedCategoryByCategory(array $linkedCategories, Category $category): ?LinkedCategory
    {
        foreach ($linkedCategories as $linkedCategory) {
            if ($linkedCategory->getCategory() === $category) {
                return $linkedCategory;
            }
        }

        return null;
    }
}
