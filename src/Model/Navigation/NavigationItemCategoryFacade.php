<?php

declare(strict_types=1);

namespace App\Model\Navigation;

use Doctrine\ORM\EntityManagerInterface;

class NavigationItemCategoryFacade
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \App\Model\Navigation\NavigationItemCategoryRepository
     */
    private NavigationItemCategoryRepository $navigationItemCategoryRepository;

    /**
     * @param \Doctrine\ORM\EntityManager $entityManager
     * @param \App\Model\Navigation\NavigationItemCategoryRepository $navigationItemCategoryRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        NavigationItemCategoryRepository $navigationItemCategoryRepository
    ) {
        $this->em = $entityManager;
        $this->navigationItemCategoryRepository = $navigationItemCategoryRepository;
    }

    /**
     * @param \App\Model\Navigation\NavigationItem $navigationItem
     * @param \App\Model\Navigation\NavigationItemData $navigationItemData
     */
    public function refreshCategoriesForNavigationItem(
        NavigationItem $navigationItem,
        NavigationItemData $navigationItemData
    ): void {
        $navigationItemCategories = $this->navigationItemCategoryRepository
            ->getSortedNavigationItemCategoriesByNavigationItem($navigationItem);

        foreach ($navigationItemCategories as $navigationItemCategory) {
            $this->em->remove($navigationItemCategory);
            $this->em->flush($navigationItemCategory);
        }

        foreach ($navigationItemData->categoriesByColumnNumber as $columnNumber => $categories) {
            $this->saveCategoriesInColumn($navigationItem, $columnNumber, $categories);
        }
    }

    /**
     * @param \App\Model\Navigation\NavigationItem $navigationItem
     * @param int $columnNumber
     * @param \App\Model\Category\Category[] $categories
     */
    private function saveCategoriesInColumn(
        NavigationItem $navigationItem,
        int $columnNumber,
        array $categories
    ): void {
        $position = 1;
        foreach ($categories as $category) {
            $navigationItemCategory = new NavigationItemCategory(
                $navigationItem,
                $columnNumber,
                $position++,
                $category
            );

            $this->em->persist($navigationItemCategory);
            $this->em->flush($navigationItemCategory);
        }
    }

    /**
     * @param \App\Model\Navigation\NavigationItem $navigationItem
     * @return \App\Model\Category\Category[][]
     */
    public function getSortedCategoriesIndexedByColumnNumberForNavigationItem(NavigationItem $navigationItem): array
    {
        $categoriesByColumnNumber = [];

        $navigationItemCategories = $this->navigationItemCategoryRepository
            ->getSortedNavigationItemCategoriesByNavigationItem($navigationItem);

        foreach ($navigationItemCategories as $navigationItemCategory) {
            $categoriesByColumnNumber[$navigationItemCategory->getColumnNumber()][]
                = $navigationItemCategory->getCategory();
        }

        return $categoriesByColumnNumber;
    }

    /**
     * @param \App\Model\Navigation\NavigationItem $navigationItem
     * @param int $domainId
     * @return \App\Model\Category\Category[][]
     */
    public function getSortedVisibleCategoriesIndexedByColumnNumberForNavigationItem(
        NavigationItem $navigationItem,
        int $domainId
    ): array {
        $categoriesByColumnNumber = [];

        $navigationItemCategories = $this->navigationItemCategoryRepository
            ->getSortedVisibleNavigationItemCategoriesByNavigationItem($navigationItem, $domainId);

        foreach ($navigationItemCategories as $navigationItemCategory) {
            $categoriesByColumnNumber[$navigationItemCategory->getColumnNumber()][]
                = $navigationItemCategory->getCategory();
        }

        return $categoriesByColumnNumber;
    }
}
