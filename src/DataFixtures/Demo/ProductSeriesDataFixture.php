<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Product\Series\ProductSeriesData;
use App\Model\Product\Series\ProductSeriesDataFactoryInterface;
use App\Model\Product\Series\ProductSeriesFacadeInterface;
use Doctrine\Common\Persistence\ObjectManager;
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

    /**
     * @var \App\Model\Product\Series\ProductSeriesFacadeInterface
     */
    private $productSeriesFacade;

    /**
     * @var \App\Model\Product\Series\ProductSeriesDataFactoryInterface
     */
    private $productSeriesDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \Faker\Generator
     */
    private $generator;

    /**
     * @param \App\Model\Product\Series\ProductSeriesFacadeInterface $productSeriesFacade
     * @param \App\Model\Product\Series\ProductSeriesDataFactoryInterface $productSeriesDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Faker\Generator $generator
     */
    public function __construct(
        ProductSeriesFacadeInterface $productSeriesFacade,
        ProductSeriesDataFactoryInterface $productSeriesDataFactory,
        Domain $domain,
        Generator $generator
    ) {
        $this->productSeriesFacade = $productSeriesFacade;
        $this->productSeriesDataFactory = $productSeriesDataFactory;
        $this->domain = $domain;
        $this->generator = $generator;
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $productSeriesName = ['Daniela', 'Karin', 'Cirri', 'Geralt', 'Yennefer', 'Tisaia'];

        foreach ($productSeriesName as $name) {
            $productSeriesData = $this->productSeriesDataFactory->create();
            foreach ($this->domain->getAll() as $domainConfig) {
                $data = $this->getDataForProductSeries($name);
                $this->fillProductSeriesData($productSeriesData, $data, $domainConfig->getId(), $domainConfig->getLocale());
            }
            $this->productSeriesFacade->create($productSeriesData);
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
    }

    /**
     * @param string $name
     * @return array
     */
    protected function getDataForProductSeries(string $name): array
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
}
