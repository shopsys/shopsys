<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Product\Series\Category\ProductSeriesCategoryData;
use App\Model\Product\Series\Category\ProductSeriesCategoryDataFactory;
use App\Model\Product\Series\Category\ProductSeriesCategoryFacade;
use App\Model\Product\Series\ProductSeriesData;
use App\Model\Product\Series\ProductSeriesDataFactoryInterface;
use App\Model\Product\Series\ProductSeriesFacadeInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class ProductSeriesDataFixture extends AbstractReferenceFixture
{
    protected const ATTRIBUTE_NAME_KEY = 'name';
    protected const ATTRIBUTE_DESCRIPTION_KEY = 'description';
    protected const ATTRIBUTE_SEO_H1_KEY = 'seoH1';
    protected const ATTRIBUTE_SEO_TITLE_KEY = 'seoTitle';
    protected const ATTRIBUTE_SEO_META_DESCRIPTION_KEY = 'seoMetaDescription';
    protected const ATTRIBUTE_HIDDEN_KEY = 'hidden';

    public const PRODUCT_SERIES_DANIELA = 'product_series_daniela';
    public const PRODUCT_SERIES_KARIN = 'product_series_karin';
    public const PRODUCT_SERIES_CIRRI = 'product_series_cirri';
    public const PRODUCT_SERIES_GERALT = 'product_series_geralt';
    public const PRODUCT_SERIES_YENNEFER = 'product_series_yennefer';
    public const PRODUCT_SERIES_TISIA = 'product_series_tisia';

    /**
     * @var \App\Model\Product\Series\ProductSeriesFacadeInterface
     */
    private $productSeriesFacade;

    /**
     * @var \App\Model\Product\Series\ProductSeriesDataFactoryInterface
     */
    private $productSeriesDataFactory;

    /**
     * @var \App\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \Faker\Generator
     */
    private $generator;

    /**
     * @var \App\Model\Product\Series\Category\ProductSeriesCategoryFacade
     */
    private $productSeriesCategoryFacade;

    /**
     * @var \App\Model\Product\Series\Category\ProductSeriesCategoryDataFactory
     */
    private $productSeriesCategoryDataFactory;

    /**
     * @param \App\Model\Product\Series\ProductSeriesFacadeInterface $productSeriesFacade
     * @param \App\Model\Product\Series\ProductSeriesDataFactoryInterface $productSeriesDataFactory
     * @param \App\Component\Domain\Domain $domain
     * @param \Faker\Generator $generator
     * @param \App\Model\Product\Series\Category\ProductSeriesCategoryFacade $productSeriesCategoryFacade
     * @param \App\Model\Product\Series\Category\ProductSeriesCategoryDataFactory $productSeriesCategoryDataFactory
     */
    public function __construct(
        ProductSeriesFacadeInterface $productSeriesFacade,
        ProductSeriesDataFactoryInterface $productSeriesDataFactory,
        Domain $domain,
        Generator $generator,
        ProductSeriesCategoryFacade $productSeriesCategoryFacade,
        ProductSeriesCategoryDataFactory $productSeriesCategoryDataFactory
    ) {
        $this->productSeriesFacade = $productSeriesFacade;
        $this->productSeriesDataFactory = $productSeriesDataFactory;
        $this->domain = $domain;
        $this->generator = $generator;
        $this->productSeriesCategoryFacade = $productSeriesCategoryFacade;
        $this->productSeriesCategoryDataFactory = $productSeriesCategoryDataFactory;
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $productSeriesCategories = $this->getProductSeriesCategories();
        $productSeriesName = ['Daniela', 'Karin', 'Cirri', 'Geralt', 'Yennefer', 'Tisaia'];
        $productSeriesConstants = [
            self::PRODUCT_SERIES_DANIELA,
            self::PRODUCT_SERIES_KARIN,
            self::PRODUCT_SERIES_CIRRI,
            self::PRODUCT_SERIES_GERALT,
            self::PRODUCT_SERIES_YENNEFER,
            self::PRODUCT_SERIES_TISIA,
        ];

        foreach ($productSeriesName as $productSeriesIndex => $name) {
            $productSeriesData = $this->productSeriesDataFactory->create();
            foreach ($this->domain->getAll() as $domainConfig) {
                $data = $this->getDataForProductSeries($name);
                $this->fillProductSeriesData($productSeriesData, $data, $domainConfig->getId(), $domainConfig->getLocale());
            }
            foreach ($this->getProductSeriesCategoriesSetup() as $categoryName => $relationSetup) {
                if (in_array($productSeriesIndex, $relationSetup, true) === true) {
                    $productSeriesData->productSeriesCategories[] = $productSeriesCategories[$categoryName];
                }
            }
            $productSeries = $this->productSeriesFacade->create($productSeriesData);

            $this->addReference($productSeriesConstants[$productSeriesIndex], $productSeries);
        }
    }

    /**
     * @param \App\Model\Product\Series\ProductSeriesData $productSeriesData
     * @param array $data
     * @param int $domainId
     * @param string $locale
     */
    private function fillProductSeriesData(ProductSeriesData $productSeriesData, array $data, int $domainId, string $locale)
    {
        $productSeriesData->hidden[$domainId] = $data[self::ATTRIBUTE_HIDDEN_KEY];
        $productSeriesData->seoH1[$domainId] = $data[self::ATTRIBUTE_SEO_H1_KEY];
        $productSeriesData->seoTitle[$domainId] = $data[self::ATTRIBUTE_SEO_TITLE_KEY];
        $productSeriesData->seoMetaDescription[$domainId] = $data[self::ATTRIBUTE_SEO_META_DESCRIPTION_KEY];
        $productSeriesData->name[$locale] = $data[self::ATTRIBUTE_NAME_KEY];
        $productSeriesData->description[$locale] = $data[self::ATTRIBUTE_DESCRIPTION_KEY];
        $productSeriesData->akeneoCode = 'demo_' . strtolower($data[self::ATTRIBUTE_NAME_KEY]);
    }

    /**
     * @param string $name
     * @return array
     */
    private function getDataForProductSeries(string $name): array
    {
        return [
            self::ATTRIBUTE_NAME_KEY => $name,
            self::ATTRIBUTE_DESCRIPTION_KEY => 'Nábytkový program ' . $name . ' - ' . $this->generator->paragraph(),
            self::ATTRIBUTE_SEO_H1_KEY => $name,
            self::ATTRIBUTE_SEO_TITLE_KEY => 'Nábytkový program ' . $name,
            self::ATTRIBUTE_SEO_META_DESCRIPTION_KEY => 'Nábytkový program ' . $name . ' meta description',
            self::ATTRIBUTE_HIDDEN_KEY => false,
        ];
    }

    /**
     * @return \App\Model\Product\Series\Category\ProductSeriesCategory[]
     */
    private function getProductSeriesCategories(): array
    {
        $productSeriesCategories = [];
        foreach ($this->getProductSeriesCategoriesSetup() as $name => $series) {
            $productSeriesCategoryData = $this->productSeriesCategoryDataFactory->create();
            foreach ($this->domain->getAll() as $domainConfig) {
                $data = $this->getDataForProductSeriesCategory($name);
                $this->fillProductSeriesCategory($productSeriesCategoryData, $data, $domainConfig->getId(), $domainConfig->getLocale());
            }
            $productSeriesCategories[$name] = $this->productSeriesCategoryFacade->create($productSeriesCategoryData);
        }
        return $productSeriesCategories;
    }

    /**
     * @return array
     */
    private function getProductSeriesCategoriesSetup(): array
    {
        return ['Ložnice' => [0, 1, 5], 'Kuchyně' => [1, 2], 'Obývací pokoje' => [2, 3, 5], 'Koupelny' => [3, 4]];
    }

    /**
     * @param \App\Model\Product\Series\Category\ProductSeriesCategoryData $productSeriesCategoryData
     * @param array $data
     * @param int $domainId
     * @param string $locale
     */
    private function fillProductSeriesCategory(ProductSeriesCategoryData $productSeriesCategoryData, array $data, int $domainId, string $locale): void
    {
        $productSeriesCategoryData->seoH1[$domainId] = $data[self::ATTRIBUTE_SEO_H1_KEY];
        $productSeriesCategoryData->seoTitle[$domainId] = $data[self::ATTRIBUTE_SEO_TITLE_KEY];
        $productSeriesCategoryData->seoMetaDescription[$domainId] = $data[self::ATTRIBUTE_SEO_META_DESCRIPTION_KEY];
        $productSeriesCategoryData->name[$locale] = $data[self::ATTRIBUTE_NAME_KEY];
        $productSeriesCategoryData->description[$locale] = $data[self::ATTRIBUTE_DESCRIPTION_KEY];
    }

    /**
     * @param string $name
     * @return array
     */
    private function getDataForProductSeriesCategory(string $name): array
    {
        return [
            self::ATTRIBUTE_NAME_KEY => $name,
            self::ATTRIBUTE_DESCRIPTION_KEY => 'Popis kategorie nábytkového programu ' . $name . ' - ' . $this->generator->paragraph(),
            self::ATTRIBUTE_SEO_H1_KEY => $name,
            self::ATTRIBUTE_SEO_TITLE_KEY => 'Kategorie nábytkového programu ' . $name,
            self::ATTRIBUTE_SEO_META_DESCRIPTION_KEY => 'Kategorie nábytkového programu ' . $name . ' meta description',
        ];
    }
}
