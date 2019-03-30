<?php

namespace Shopsys\ShopBundle\DataFixtures\Performance;

use Doctrine\ORM\EntityManagerInterface;
use Faker\Generator as Faker;
use Shopsys\FrameworkBundle\Component\Console\ProgressBarFactory;
use Shopsys\FrameworkBundle\Component\Doctrine\SqlLoggerFacade;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Category\CategoryRepository;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityRecalculationScheduler;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculationScheduler;
use Shopsys\FrameworkBundle\Model\Product\ProductData;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductVariantFacade;
use Shopsys\ShopBundle\DataFixtures\Loader\ProductDataFixtureLoader;
use Shopsys\ShopBundle\DataFixtures\Loader\ProductParameterValueDataLoader;
use Symfony\Component\Console\Output\OutputInterface;

class ProductDataFixture
{
    const BATCH_SIZE = 1000;

    const FIRST_PERFORMANCE_PRODUCT = 'first_performance_product';
    const VARIANTS_PER_PRODUCT = 5;

    /**
     * @var int
     */
    private $productTotalCount;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductFacade
     */
    private $productFacade;

    /**
     * @var \Shopsys\ShopBundle\DataFixtures\Loader\ProductDataFixtureLoader
     */
    private $productDataFixtureLoader;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Doctrine\SqlLoggerFacade
     */
    private $sqlLoggerFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductVariantFacade
     */
    private $productVariantFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceFacade
     */
    private $persistentReferenceFacade;

    /**
     * @var int
     */
    private $demoDataIterationCounter;

    /**
     * @var \Faker\Generator
     */
    private $faker;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityRecalculationScheduler
     */
    private $productAvailabilityRecalculationScheduler;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculationScheduler
     */
    private $productPriceRecalculationScheduler;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Console\ProgressBarFactory
     */
    private $progressBarFactory;

    /**
     * @var \Shopsys\ShopBundle\DataFixtures\Loader\ProductParameterValueDataLoader
     */
    private $productParameterValueDataLoader;

    /**
     * @param int $productTotalCount
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryRepository $categoryRepository
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Faker\Generator $faker
     * @param \Shopsys\ShopBundle\DataFixtures\Loader\ProductDataFixtureLoader $productDataFixtureLoader
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductFacade $productFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductVariantFacade $productVariantFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityRecalculationScheduler $productAvailabilityRecalculationScheduler
     * @param \Shopsys\ShopBundle\DataFixtures\Loader\ProductParameterValueDataLoader $productParameterValueDataLoader
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculationScheduler $productPriceRecalculationScheduler
     * @param \Shopsys\FrameworkBundle\Component\Console\ProgressBarFactory $progressBarFactory
     * @param \Shopsys\FrameworkBundle\Component\Doctrine\SqlLoggerFacade $sqlLoggerFacade
     */
    public function __construct(
        $productTotalCount,
        CategoryRepository $categoryRepository,
        EntityManagerInterface $em,
        Faker $faker,
        ProductDataFixtureLoader $productDataFixtureLoader,
        ProductFacade $productFacade,
        ProductVariantFacade $productVariantFacade,
        ProductAvailabilityRecalculationScheduler $productAvailabilityRecalculationScheduler,
        ProductParameterValueDataLoader $productParameterValueDataLoader,
        ProductPriceRecalculationScheduler $productPriceRecalculationScheduler,
        ProgressBarFactory $progressBarFactory,
        SqlLoggerFacade $sqlLoggerFacade
    ) {
        $this->productTotalCount = $productTotalCount;
        $this->categoryRepository = $categoryRepository;
        $this->em = $em;
        $this->productDataFixtureLoader = $productDataFixtureLoader;
        $this->productFacade = $productFacade;
        $this->sqlLoggerFacade = $sqlLoggerFacade;
        $this->productVariantFacade = $productVariantFacade;
        $this->faker = $faker;
        $this->productAvailabilityRecalculationScheduler = $productAvailabilityRecalculationScheduler;
        $this->productParameterValueDataLoader = $productParameterValueDataLoader;
        $this->productPriceRecalculationScheduler = $productPriceRecalculationScheduler;
        $this->progressBarFactory = $progressBarFactory;
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function load(OutputInterface $output)
    {
        $this->productDataFixtureLoader->loadReferences();
        // Sql logging during mass data import makes memory leak
        $this->sqlLoggerFacade->temporarilyDisableLogging();

        $progressBar = $this->progressBarFactory->create($output, $this->productTotalCount);

        $countImported = 0;

        while (++$countImported < $this->productTotalCount) {
            $this->faker->seed($countImported);
            $productData = $this->productDataFixtureLoader->getProductsDataForFakerSeed($countImported);
            $this->setUniqueProductData($productData);
            $this->setRandomPerformanceCategoriesToProductData($productData);

            $hasParameters = $this->faker->boolean(40);
            if ($hasParameters) {
                $productData->parameters = $this->productParameterValueDataLoader->getParameterValueDataParametersForFakerSeed($countImported);
            }

            $product = $this->productFacade->create($productData);

            $hasVariants = $this->faker->boolean(30);
            if ($hasVariants) {
                $variants = $this->productDataFixtureLoader->createVariantsProductDataForProduct($product, self::VARIANTS_PER_PRODUCT);
                $this->productVariantFacade->createVariant($product, $variants);
            }

            if ($countImported === 0) {
                $this->persistentReferenceFacade->persistReference(self::FIRST_PERFORMANCE_PRODUCT, $product);
            }

            if ($countImported % self::BATCH_SIZE === 0) {
                $this->clearResources();
            }

            $progressBar->setProgress($countImported);
        }

        $progressBar->finish();

        $this->em->clear();
        $this->sqlLoggerFacade->reenableLogging();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductData $productData
     * @return \Shopsys\FrameworkBundle\Model\Product\ProductData
     */
    private function setUniqueProductData(ProductData $productData)
    {
        $matches = [];
        $uniqueIndex = $this->getUniqueIndex();

        if (preg_match('/^(.*) #\d+$/', $productData->catnum, $matches)) {
            $productData->catnum = $matches[1] . $uniqueIndex;
        } else {
            $productData->catnum .= $uniqueIndex;
        }

        foreach ($productData->name as $locale => $name) {
            if (preg_match('/^(.*) #\d+$/', $name, $matches)) {
                $productData->name[$locale] = $matches[1] . $uniqueIndex;
            } else {
                $productData->name[$locale] .= $uniqueIndex;
            }
        }

        return $productData;
    }

    /**
     * @return string
     */
    private function getUniqueIndex()
    {
        return ' #' . $this->demoDataIterationCounter;
    }

    private function clearResources()
    {
        $this->productAvailabilityRecalculationScheduler->cleanScheduleForImmediateRecalculation();
        $this->productPriceRecalculationScheduler->cleanScheduleForImmediateRecalculation();
        $this->em->clear();
        gc_collect_cycles();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductData $productData
     */
    private function setRandomPerformanceCategoriesToProductData(ProductData $productData)
    {
        $this->cleanPerformanceCategoriesFromProductDataByDomainId($productData, 1);
        $this->cleanPerformanceCategoriesFromProductDataByDomainId($productData, 2);
        $this->addRandomPerformanceCategoriesToProductDataByDomainId($productData, 1);
        $this->addRandomPerformanceCategoriesToProductDataByDomainId($productData, 2);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductData $productData
     * @param int $domainId
     */
    private function cleanPerformanceCategoriesFromProductDataByDomainId(ProductData $productData, $domainId)
    {
        foreach ($productData->categoriesByDomainId[$domainId] as $key => $category) {
            if ($this->isPerformanceCategory($category)) {
                unset($productData->categoriesByDomainId[$domainId][$key]);
            }
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductData $productData
     * @param int $domainId
     */
    private function addRandomPerformanceCategoriesToProductDataByDomainId(ProductData $productData, $domainId)
    {
        $performanceCategoryIds = $this->getPerformanceCategoryIds();
        $randomPerformanceCategoryIds = $this->faker->randomElements(
            $performanceCategoryIds,
            $this->faker->numberBetween(1, 4)
        );
        $randomPerformanceCategories = $this->categoryRepository->getCategoriesByIds($randomPerformanceCategoryIds);

        foreach ($randomPerformanceCategories as $performanceCategory) {
            if (!in_array($performanceCategory, $productData->categoriesByDomainId[$domainId], true)) {
                $productData->categoriesByDomainId[$domainId][] = $performanceCategory;
            }
        }
    }

    /**
     * @return int[]
     */
    private function getPerformanceCategoryIds()
    {
        $allCategoryIds = $this->categoryRepository->getAllIds();
        $firstPerformanceCategory = $this->persistentReferenceFacade->getReference(
            CategoryDataFixture::FIRST_PERFORMANCE_CATEGORY
        );
        $firstPerformanceCategoryKey = array_search($firstPerformanceCategory->getId(), $allCategoryIds, true);

        return array_slice($allCategoryIds, $firstPerformanceCategoryKey);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @return bool
     */
    private function isPerformanceCategory(Category $category)
    {
        $firstPerformanceCategory = $this->persistentReferenceFacade->getReference(
            CategoryDataFixture::FIRST_PERFORMANCE_CATEGORY
        );
        /* @var $firstPerformanceCategory \Shopsys\FrameworkBundle\Model\Category\Category */

        return $category->getId() >= $firstPerformanceCategory->getId();
    }
}
