<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Product\Series\ProductSeriesData;
use App\Model\Product\Series\ProductSeriesDataFactoryInterface;
use App\Model\Product\Series\ProductSeriesFacadeInterface;
use Doctrine\Common\Persistence\ObjectManager;
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
     * @param \App\Model\Product\Series\ProductSeriesFacadeInterface $productSeriesFacade
     * @param \App\Model\Product\Series\ProductSeriesDataFactoryInterface $productSeriesDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        ProductSeriesFacadeInterface $productSeriesFacade,
        ProductSeriesDataFactoryInterface $productSeriesDataFactory,
        Domain $domain
    ) {
        $this->productSeriesFacade = $productSeriesFacade;
        $this->productSeriesDataFactory = $productSeriesDataFactory;
        $this->domain = $domain;
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
        $productSeriesData->seoH1s[$domainId] = $data[self::ATTRIBUTE_SEO_H1_KEY];
        $productSeriesData->seoTitles[$domainId] = $data[self::ATTRIBUTE_SEO_TITLE_KEY];
        $productSeriesData->seoMetaDescriptions[$domainId] = $data[self::ATTRIBUTE_SEO_META_DESCRIPTION_KEY];
        $productSeriesData->names[$locale] = $data[self::ATTRIBUTE_NAME_KEY];
        $productSeriesData->descriptions[$locale] = $data[self::ATTRIBUTE_DESCRIPTION_KEY];
    }

    /**
     * @param string $name
     * @return array
     */
    protected function getDataForProductSeries(string $name): array
    {
        return [
            self::ATTRIBUTE_NAME_KEY => $name,
            self::ATTRIBUTE_DESCRIPTION_KEY => 'Nábytkový program ' . $name . ' - Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus felis nisi, tincidunt sollicitudin augue eu, laoreet blandit sem. Donec rutrum augue a elit imperdiet, eu vehicula tortor porta. Vivamus pulvinar sem non auctor dictum. Morbi eleifend semper enim, eu faucibus tortor posuere vitae. Donec tincidunt ipsum ullamcorper nisi accumsan tincidunt. Aenean sed velit massa. Nullam interdum eget est ut convallis. Vestibulum et mauris condimentum, rutrum sem congue, suscipit arcu.\nSed tristique vehicula ipsum, ut vulputate tortor feugiat eu. Vivamus convallis quam vulputate faucibus facilisis. Curabitur tincidunt pulvinar leo, eu dapibus augue lacinia a. Fusce sed tincidunt nunc. Morbi a nisi a odio pharetra laoreet nec eget quam. In in nisl tortor. Ut fringilla vitae lectus eu venenatis. Nullam interdum sed odio a posuere. Fusce pellentesque dui vel tortor blandit, a dictum nunc congue.',
            self::ATTRIBUTE_SEO_H1_KEY => $name,
            self::ATTRIBUTE_SEO_TITLE_KEY => 'Nábytkový program ' . $name,
            self::ATTRIBUTE_SEO_META_DESCRIPTION_KEY => 'Nábytkový program ' . $name . ' meta description',
            self::ATTRIBUTE_HIDDEN_KEY => false,
        ];
    }
}
