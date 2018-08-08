<?php

namespace Shopsys\FrameworkBundle\DataFixtures\Performance;

use Faker\Generator as Faker;
use Shopsys\FrameworkBundle\Component\Console\ProgressBarFactory;
use Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceFacade;
use Shopsys\FrameworkBundle\Component\Doctrine\SqlLoggerFacade;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Category\CategoryDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class CategoryDataFixture
{
    const FIRST_PERFORMANCE_CATEGORY = 'first_performance_category';

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\CategoryDataFactoryInterface
     */
    private $categoryDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\CategoryFacade
     */
    private $categoryFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Doctrine\SqlLoggerFacade
     */
    private $sqlLoggerFacade;

    /**
     * @var \Faker\Generator
     */
    private $faker;

    /**
     * @var int[]
     */
    private $categoryCountsByLevel;

    /**
     * @var int
     */
    private $categoriesCreated;

    /**
     * @var \Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceFacade
     */
    private $persistentReferenceFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Console\ProgressBarFactory
     */
    private $progressBarFactory;

    /**
     * @param int[] $categoryCountsByLevel
     * @param \Faker\Generator $faker
     */
    public function __construct(
        $categoryCountsByLevel,
        CategoryDataFactoryInterface $categoryDataFactory,
        CategoryFacade $categoryFacade,
        SqlLoggerFacade $sqlLoggerFacade,
        PersistentReferenceFacade $persistentReferenceFacade,
        Faker $faker,
        ProgressBarFactory $progressBarFactory
    ) {
        $this->categoryCountsByLevel = $categoryCountsByLevel;
        $this->categoryDataFactory = $categoryDataFactory;
        $this->categoryFacade = $categoryFacade;
        $this->sqlLoggerFacade = $sqlLoggerFacade;
        $this->faker = $faker;
        $this->categoriesCreated = 0;
        $this->persistentReferenceFacade = $persistentReferenceFacade;
        $this->progressBarFactory = $progressBarFactory;
    }

    public function load(OutputInterface $output): void
    {
        $progressBar = $this->progressBarFactory->create($output, array_sum($this->categoryCountsByLevel));

        $rootCategory = $this->categoryFacade->getRootCategory();
        $this->sqlLoggerFacade->temporarilyDisableLogging();
        $this->recursivelyCreateCategoryTree($rootCategory, $progressBar);
        $this->sqlLoggerFacade->reenableLogging();
    }
    
    private function recursivelyCreateCategoryTree(\Shopsys\FrameworkBundle\Model\Category\Category $parentCategory, ProgressBar $progressBar, int $categoryLevel = 0): void
    {
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

    private function getRandomCategoryDataByParentCategory(Category $parentCategory): \Shopsys\FrameworkBundle\Model\Category\CategoryData
    {
        $categoryData = $this->categoryDataFactory->create();
        $categoryName = $this->faker->word . ' #' . $this->categoriesCreated;
        $categoryData->name = ['cs' => $categoryName, 'en' => $categoryName];
        $categoryData->descriptions = [
            1 => $this->faker->paragraph(3, false),
            2 => $this->faker->paragraph(3, false),
        ];
        $categoryData->parent = $parentCategory;

        return $categoryData;
    }
}
