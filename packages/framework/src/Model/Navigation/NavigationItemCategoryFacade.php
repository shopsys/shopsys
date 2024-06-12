<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Navigation;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;

class NavigationItemCategoryFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Navigation\NavigationItemCategoryRepository $navigationItemCategoryRepository
     * @param \Shopsys\FrameworkBundle\Model\Navigation\NavigationItemCategoryFactory $navigationItemCategoryFactory
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly NavigationItemCategoryRepository $navigationItemCategoryRepository,
        protected readonly NavigationItemCategoryFactory $navigationItemCategoryFactory,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Navigation\NavigationItem $navigationItem
     * @param \Shopsys\FrameworkBundle\Model\Navigation\NavigationItemData $navigationItemData
     */
    public function refreshCategoriesForNavigationItem(
        NavigationItem $navigationItem,
        NavigationItemData $navigationItemData,
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
     * @param \Shopsys\FrameworkBundle\Model\Navigation\NavigationItem $navigationItem
     * @param int $columnNumber
     * @param \Shopsys\FrameworkBundle\Model\Category\Category[] $categories
     */
    protected function saveCategoriesInColumn(
        NavigationItem $navigationItem,
        int $columnNumber,
        array $categories,
    ): void {
        $position = 1;

        foreach ($categories as $category) {
            $navigationItemCategory = $this->navigationItemCategoryFactory->create(
                $navigationItem,
                $columnNumber,
                $position++,
                $category,
            );

            $this->em->persist($navigationItemCategory);
        }

        $this->em->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Navigation\NavigationItem $navigationItem
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[][]
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
     * @param \Shopsys\FrameworkBundle\Model\Navigation\NavigationItem[] $navigationItems
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[][][]
     */
    public function getSortedVisibleCategoriesIndexedByNavigationItemIdAndColumnNumber(
        array $navigationItems,
        DomainConfig $domainConfig,
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
