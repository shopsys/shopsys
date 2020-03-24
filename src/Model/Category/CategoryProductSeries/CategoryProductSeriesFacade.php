<?php

declare(strict_types=1);

namespace App\Model\Category\CategoryProductSeries;

use App\Model\Category\Category;
use Doctrine\ORM\EntityManagerInterface;

class CategoryProductSeriesFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \App\Model\Category\CategoryProductSeries\CategoryProductSeriesRepository
     */
    protected $categoryProductSeriesRepository;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Category\CategoryProductSeries\CategoryProductSeriesRepository $categoryProductSeriesRepository
     */
    public function __construct(
        EntityManagerInterface $em,
        CategoryProductSeriesRepository $categoryProductSeriesRepository
    ) {
        $this->em = $em;
        $this->categoryProductSeriesRepository = $categoryProductSeriesRepository;
    }

    /**
     * @param \App\Model\Category\Category $category
     * @param \App\Model\Product\Series\ProductSeries[] $productSeriesList
     */
    public function saveProductSeriesForCategory(Category $category, array $productSeriesList): void
    {
        $oldProductSeriesList = $this->categoryProductSeriesRepository->getAllProductSeriesByCategory($category);
        foreach ($oldProductSeriesList as $oldProductSeries) {
            $this->em->remove($oldProductSeries);
        }

        $this->em->flush();

        $categoryProductSeriesList = [];
        $position = 1;
        foreach ($productSeriesList as $productSeries) {
            $productSeriesId = $productSeries->getId();
            $position++;
            $categoryProductSeries = new CategoryProductSeries($category, $productSeriesId, $position);
            $this->em->persist($categoryProductSeries);
            $categoryProductSeriesList[] = $categoryProductSeries;
        }
        $this->em->flush();
    }

    /**
     * @param \App\Model\Category\Category $category
     * @return \App\Model\Product\Series\ProductSeries[]
     */
    public function getAllCategoryProductSeriesByCategory(Category $category): array
    {
        return $this->categoryProductSeriesRepository->getAllAssignedProductSeriesByCategory($category);
    }
}
