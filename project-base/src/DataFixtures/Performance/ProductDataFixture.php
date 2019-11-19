<?php

declare(strict_types=1);

namespace App\DataFixtures\Performance;

use App\DataFixtures\Demo\ProductDataFixture as DemoProductDataFixture;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Generator as Faker;
use Shopsys\FrameworkBundle\Component\Console\ProgressBarFactory;
use Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceFacade;
use Shopsys\FrameworkBundle\Component\Doctrine\SqlLoggerFacade;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Category\CategoryRepository;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityRecalculationScheduler;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculationScheduler;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductData;
use Shopsys\FrameworkBundle\Model\Product\ProductDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductVariantFacade;
use Symfony\Component\Console\Output\OutputInterface;

class ProductDataFixture
{
    protected const BATCH_SIZE = 1000;

    public const FIRST_PERFORMANCE_PRODUCT = 'first_performance_product';

    /**
     * @var int
     */
    private $productTotalCount;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductFacade
     */
    private $productFacade;

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
     * @var \Shopsys\FrameworkBundle\Model\Category\CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var int
     */
    private $countImported;

    /**
     * @var int
     */
    private $demoDataIterationCounter;

    /**
     * @var \App\Model\Product\Product[]
     */
    private $productsByCatnum;

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
     * @var \App\Model\Product\ProductDataFactory
     */
    private $productDataFactory;

    /**
     * @var array|\App\Model\Product\Product[]
     */
    private $productTemplates;

    /**
     * @param int $productTotalCount
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductFacade $productFacade
     * @param \Shopsys\FrameworkBundle\Component\Doctrine\SqlLoggerFacade $sqlLoggerFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductVariantFacade $productVariantFacade
     * @param \Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceFacade $persistentReferenceFacade
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryRepository $categoryRepository
     * @param \Faker\Generator $faker
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityRecalculationScheduler $productAvailabilityRecalculationScheduler
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculationScheduler $productPriceRecalculationScheduler
     * @param \Shopsys\FrameworkBundle\Component\Console\ProgressBarFactory $progressBarFactory
     * @param \App\Model\Product\ProductDataFactory $productDataFactory
     */
    public function __construct(
        $productTotalCount,
        EntityManagerInterface $em,
        ProductFacade $productFacade,
        SqlLoggerFacade $sqlLoggerFacade,
        ProductVariantFacade $productVariantFacade,
        PersistentReferenceFacade $persistentReferenceFacade,
        CategoryRepository $categoryRepository,
        Faker $faker,
        ProductAvailabilityRecalculationScheduler $productAvailabilityRecalculationScheduler,
        ProductPriceRecalculationScheduler $productPriceRecalculationScheduler,
        ProgressBarFactory $progressBarFactory,
        ProductDataFactoryInterface $productDataFactory
    ) {
        $this->productTotalCount = $productTotalCount;
        $this->em = $em;
        $this->productFacade = $productFacade;
        $this->sqlLoggerFacade = $sqlLoggerFacade;
        $this->productVariantFacade = $productVariantFacade;
        $this->persistentReferenceFacade = $persistentReferenceFacade;
        $this->categoryRepository = $categoryRepository;
        $this->countImported = 0;
        $this->demoDataIterationCounter = 0;
        $this->faker = $faker;
        $this->productAvailabilityRecalculationScheduler = $productAvailabilityRecalculationScheduler;
        $this->productPriceRecalculationScheduler = $productPriceRecalculationScheduler;
        $this->progressBarFactory = $progressBarFactory;
        $this->productDataFactory = $productDataFactory;
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function load(OutputInterface $output)
    {
        // Sql logging during mass data import makes memory leak
        $this->sqlLoggerFacade->temporarilyDisableLogging();

        $this->cleanAndLoadReferences();

        $variantCatnumsByMainVariantCatnum = DemoProductDataFixture::getVariantCatnumsByMainVariantCatnum();

        $progressBar = $this->progressBarFactory->create($output, $this->productTotalCount);

        while ($this->countImported < $this->productTotalCount) {
            $productTemplate = next($this->productTemplates);

            if ($productTemplate === false) {
                $this->createVariants($variantCatnumsByMainVariantCatnum);
                $productTemplate = reset($this->productTemplates);
                $this->demoDataIterationCounter++;
            }
            $productData = $this->productDataFactory->createFromProduct($productTemplate);
            $this->makeProductDataUnique($productData);

            $this->setRandomPerformanceCategoriesToProductData($productData);
            /** @var \App\Model\Product\Product $product */
            $product = $this->productFacade->create($productData);

            if ($this->countImported === 0) {
                $this->persistentReferenceFacade->persistReference(self::FIRST_PERFORMANCE_PRODUCT, $product);
            }

            if ($product->getCatnum() !== null) {
                $this->productsByCatnum[$product->getCatnum()] = $product;
            }

            if ($this->countImported % self::BATCH_SIZE === 0) {
                $this->cleanAndLoadReferences();
            }

            $this->countImported++;

            $progressBar->setProgress($this->countImported);
        }
        $this->createVariants($variantCatnumsByMainVariantCatnum);

        $progressBar->finish();

        $this->em->clear();
        $this->sqlLoggerFacade->reenableLogging();
    }

    /**
     * @param string[][] $variantCatnumsByMainVariantCatnum
     */
    private function createVariants(array $variantCatnumsByMainVariantCatnum)
    {
        $uniqueIndex = $this->getUniqueIndex();

        foreach ($variantCatnumsByMainVariantCatnum as $mainVariantCatnum => $variantsCatnums) {
            try {
                $mainProduct = $this->getProductByCatnum($mainVariantCatnum . $uniqueIndex);
                $variants = [];
                foreach ($variantsCatnums as $variantCatnum) {
                    $variants[] = $this->getProductByCatnum($variantCatnum . $uniqueIndex);
                }
                $this->productVariantFacade->createVariant($mainProduct, $variants);
            } catch (\Doctrine\ORM\NoResultException $e) {
                continue;
            }
        }
    }

    /**
     * @param string $catnum
     * @return \App\Model\Product\Product
     */
    private function getProductByCatnum($catnum)
    {
        if (!array_key_exists($catnum, $this->productsByCatnum)) {
            $query = $this->em->createQuery('SELECT p FROM ' . Product::class . ' p WHERE p.catnum = :catnum')
                ->setParameter('catnum', $catnum);
            $this->productsByCatnum[$catnum] = $query->getSingleResult();
        }

        return $this->productsByCatnum[$catnum];
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     */
    private function makeProductDataUnique(ProductData $productData)
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

    private function cleanAndLoadReferences()
    {
        $this->clearResources();
        $this->productsByCatnum = [];
        $this->productTemplates = [];

        $i = 1;
        while (true) {
            try {
                /** @var \App\Model\Product\Product $product */
                $product = $this->persistentReferenceFacade->getReference(DemoProductDataFixture::PRODUCT_PREFIX . $i);
                $this->productTemplates[] = $product;
                $i++;
            } catch (\Shopsys\FrameworkBundle\Component\DataFixture\Exception\PersistentReferenceNotFoundException $e) {
                break;
            }
        }
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     */
    private function setRandomPerformanceCategoriesToProductData(ProductData $productData)
    {
        $this->cleanPerformanceCategoriesFromProductDataByDomainId($productData, 1);
        $this->cleanPerformanceCategoriesFromProductDataByDomainId($productData, 2);
        $this->addRandomPerformanceCategoriesToProductDataByDomainId($productData, 1);
        $this->addRandomPerformanceCategoriesToProductDataByDomainId($productData, 2);
    }

    /**
     * @param \App\Model\Product\ProductData $productData
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
     * @param \App\Model\Product\ProductData $productData
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
     * @param \App\Model\Category\Category $category
     * @return bool
     */
    private function isPerformanceCategory(Category $category)
    {
        /** @var \App\Model\Category\Category $firstPerformanceCategory */
        $firstPerformanceCategory = $this->persistentReferenceFacade->getReference(
            CategoryDataFixture::FIRST_PERFORMANCE_CATEGORY
        );

        return $category->getId() >= $firstPerformanceCategory->getId();
    }
}
