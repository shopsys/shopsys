<?php

declare(strict_types=1);

namespace App\Model\Navigation;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;

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
            ->getSortedNavigationItemCategoriesByNavigationItems([$navigationItem]);

        foreach ($navigationItemCategories as $navigationItemCategory) {
            $this->em->remove($navigationItemCategory);
            $this->em->flush();
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
        }

        $this->em->flush();
    }

    /**
     * @param \App\Model\Navigation\NavigationItem $navigationItem
     * @return \App\Model\Category\Category[][]
     */
    public function getSortedCategoriesIndexedByColumnNumberForNavigationItem(NavigationItem $navigationItem): array
    {
        $categoriesByColumnNumber = [];

        $navigationItemCategories = $this->navigationItemCategoryRepository
            ->getSortedNavigationItemCategoriesByNavigationItems([$navigationItem]);

        foreach ($navigationItemCategories as $navigationItemCategory) {
            $categoriesByColumnNumber[$navigationItemCategory->getColumnNumber()][]
                = $navigationItemCategory->getCategory();
        }

        return $categoriesByColumnNumber;
    }

    /**
     * @param \App\Model\Navigation\NavigationItem[] $navigationItems
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \App\Model\Category\Category[][][]
     */
    public function getSortedVisibleCategoriesIndexedByNavigationItemIdAndColumnNumber(
        array $navigationItems,
        DomainConfig $domainConfig
    ): array {
        $categoriesIndexedByNavigationItemIdAndColumnNumber = [];

        $navigationItemCategories = $this->navigationItemCategoryRepository
            ->getSortedVisibleNavigationItemCategoriesByNavigationItems($navigationItems, $domainConfig);

        foreach ($navigationItems as $navigationItem) {
            $navigationItemId = $navigationItem->getId();
            $categoriesIndexedByNavigationItemIdAndColumnNumber[$navigationItemId] = [];
            foreach ($navigationItemCategories as $navigationItemCategory) {
                if ($navigationItemCategory->getNavigationItem()->getId() === $navigationItemId) {
                    $categoriesIndexedByNavigationItemIdAndColumnNumber[$navigationItemId][$navigationItemCategory->getColumnNumber()][]
                        = $navigationItemCategory->getCategory();
                }
            }
        }

        return $categoriesIndexedByNavigationItemIdAndColumnNumber;
    }
}
