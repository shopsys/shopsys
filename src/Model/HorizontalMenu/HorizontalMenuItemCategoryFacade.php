<?php

declare(strict_types=1);

namespace App\Model\HorizontalMenu;

use Doctrine\ORM\EntityManagerInterface;

class HorizontalMenuItemCategoryFacade
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \App\Model\HorizontalMenu\HorizontalMenuItemCategoryRepository
     */
    private $horizontalMenuItemCategoryRepository;

    /**
     * @param \Doctrine\ORM\EntityManager $entityManager
     * @param \App\Model\HorizontalMenu\HorizontalMenuItemCategoryRepository $horizontalMenuItemCategoryRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        HorizontalMenuItemCategoryRepository $horizontalMenuItemCategoryRepository
    ) {
        $this->em = $entityManager;
        $this->horizontalMenuItemCategoryRepository = $horizontalMenuItemCategoryRepository;
    }

    /**
     * @param \App\Model\HorizontalMenu\HorizontalMenuItem $horizontalMenuItem
     * @param \App\Model\HorizontalMenu\HorizontalMenuItemData $horizontalMenuItemData
     */
    public function refreshCategoriesForHorizontalMenuItem(
        HorizontalMenuItem $horizontalMenuItem,
        HorizontalMenuItemData $horizontalMenuItemData
    ): void {
        $horizontalMenuItemCategories = $this->horizontalMenuItemCategoryRepository
            ->getSortedHorizontalMenuItemCategoriesByHorizontalMenuItem($horizontalMenuItem);

        foreach ($horizontalMenuItemCategories as $horizontalMenuItemCategory) {
            $this->em->remove($horizontalMenuItemCategory);
            $this->em->flush($horizontalMenuItemCategory);
        }

        foreach ($horizontalMenuItemData->categoriesByColumnNumber as $columnNumber => $categories) {
            $this->saveCategoriesInColumn($horizontalMenuItem, $columnNumber, $categories);
        }
    }

    /**
     * @param \App\Model\HorizontalMenu\HorizontalMenuItem $horizontalMenuItem
     * @param int $columnNumber
     * @param \App\Model\Category\Category[] $categories
     */
    private function saveCategoriesInColumn(
        HorizontalMenuItem $horizontalMenuItem,
        int $columnNumber,
        array $categories
    ): void {
        $position = 1;
        foreach ($categories as $category) {
            $horizontalMenuItemCategory = new HorizontalMenuItemCategory(
                $horizontalMenuItem,
                $columnNumber,
                $position++,
                $category
            );

            $this->em->persist($horizontalMenuItemCategory);
            $this->em->flush($horizontalMenuItemCategory);
        }
    }

    /**
     * @param \App\Model\HorizontalMenu\HorizontalMenuItem $horizontalMenuItem
     * @return \App\Model\Category\Category[][]
     */
    public function getSortedCategoriesIndexedByColumnNumberForHorizontalMenuItem(HorizontalMenuItem $horizontalMenuItem): array
    {
        $categoriesByColumnNumber = [];

        $horizontalMenuItemCategories = $this->horizontalMenuItemCategoryRepository
            ->getSortedHorizontalMenuItemCategoriesByHorizontalMenuItem($horizontalMenuItem);

        foreach ($horizontalMenuItemCategories as $horizontalMenuItemCategory) {
            $categoriesByColumnNumber[$horizontalMenuItemCategory->getColumnNumber()][]
                = $horizontalMenuItemCategory->getCategory();
        }

        return $categoriesByColumnNumber;
    }

    /**
     * @param \App\Model\HorizontalMenu\HorizontalMenuItem $horizontalMenuItem
     * @param int $domainId
     * @return \App\Model\Category\Category[][]
     */
    public function getSortedVisibledCategoriesIndexedByColumnNumberForHorizontalMenuItem(
        HorizontalMenuItem $horizontalMenuItem,
        int $domainId
    ): array {
        $categoriesByColumnNumber = [];

        $horizontalMenuItemCategories = $this->horizontalMenuItemCategoryRepository
            ->getSortedVisibledHorizontalMenuItemCategoriesByHorizontalMenuItem($horizontalMenuItem, $domainId);

        foreach ($horizontalMenuItemCategories as $horizontalMenuItemCategory) {
            $categoriesByColumnNumber[$horizontalMenuItemCategory->getColumnNumber()][]
                = $horizontalMenuItemCategory->getCategory();
        }

        return $categoriesByColumnNumber;
    }
}
