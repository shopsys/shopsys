<?php

declare(strict_types=1);

namespace App\DataFixtures\Performance;

use App\Model\Category\Category;
use App\Model\Category\CategoryData;
use App\Model\Category\CategoryDataFactory;
use App\Model\Category\CategoryFacade;
use Faker\Generator as Faker;
use Shopsys\FrameworkBundle\Component\Console\ProgressBarFactory;
use Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceFacade;
use Shopsys\FrameworkBundle\Component\Doctrine\SqlLoggerFacade;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class CategoryDataFixture
{
    public const FIRST_PERFORMANCE_CATEGORY = 'first_performance_category';

    private int $categoriesCreated;

    /**
     * @param int[] $categoryCountsByLevel
     */
    public function __construct(
        private array $categoryCountsByLevel,
        private readonly CategoryDataFactory $categoryDataFactory,
        private readonly CategoryFacade $categoryFacade,
        private readonly SqlLoggerFacade $sqlLoggerFacade,
        private readonly PersistentReferenceFacade $persistentReferenceFacade,
        private readonly Faker $faker,
        private readonly ProgressBarFactory $progressBarFactory,
    ) {
        $this->categoriesCreated = 0;
    }

    public function load(OutputInterface $output): void
    {
        $progressBar = $this->progressBarFactory->create($output, $this->recursivelyCountCategoriesInCategoryTree());

        $rootCategory = $this->categoryFacade->getRootCategory();
        $this->sqlLoggerFacade->temporarilyDisableLogging();
        $this->recursivelyCreateCategoryTree($rootCategory, $progressBar);
        $this->sqlLoggerFacade->reenableLogging();
    }

    private function recursivelyCreateCategoryTree(
        Category $parentCategory,
        ProgressBar $progressBar,
        int $categoryLevel = 0,
    ): void {
        for ($i = 0; $i < $this->categoryCountsByLevel[$categoryLevel]; $i++) {
            $categoryData = $this->getRandomCategoryDataByParentCategory($parentCategory);
            $newCategory = $this->categoryFacade->create($categoryData);
            $progressBar->advance();
            $this->categoriesCreated++;

            if ($this->categoriesCreated === 1) {
                $this->persistentReferenceFacade->persistReference(self::FIRST_PERFORMANCE_CATEGORY, $newCategory);
            }

            if (array_key_exists($categoryLevel + 1, $this->categoryCountsByLevel)) {
                $this->recursivelyCreateCategoryTree($newCategory, $progressBar, $categoryLevel + 1);
            }
        }
    }

    private function recursivelyCountCategoriesInCategoryTree(int $categoryLevel = 0): int
    {
        $count = 0;

        for ($i = 0; $i < $this->categoryCountsByLevel[$categoryLevel]; $i++) {
            $count++;

            if (array_key_exists($categoryLevel + 1, $this->categoryCountsByLevel)) {
                $count += $this->recursivelyCountCategoriesInCategoryTree($categoryLevel + 1);
            }
        }

        return $count;
    }

    private function getRandomCategoryDataByParentCategory(Category $parentCategory): CategoryData
    {
        $categoryData = $this->categoryDataFactory->create();
        $categoryName = $this->faker->word . ' #' . $this->categoriesCreated;
        $categoryData->name = [
            'cs' => $categoryName,
            'en' => $categoryName,
        ];
        $categoryData->descriptions = [
            1 => $this->faker->paragraph(3, false),
            2 => $this->faker->paragraph(3, false),
        ];
        $categoryData->parent = $parentCategory;

        return $categoryData;
    }
}
