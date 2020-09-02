<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\CategorySeo\ChoseCategorySeoMixCombination;
use App\Model\CategorySeo\ReadyCategorySeoMixDataFactory;
use App\Model\CategorySeo\ReadyCategorySeoMixFacade;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListData;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig;

class ReadyCategorySeoDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    /**
     * @var \App\Model\CategorySeo\ReadyCategorySeoMixDataFactory
     */
    private $readyCategorySeoMixDataFactory;

    /**
     * @var \App\Model\CategorySeo\ReadyCategorySeoMixFacade
     */
    private $readyCategorySeoMixFacade;

    /**
     * @param \App\Model\CategorySeo\ReadyCategorySeoMixDataFactory $readyCategorySeoMixDataFactory
     * @param \App\Model\CategorySeo\ReadyCategorySeoMixFacade $readyCategorySeoMixFacade
     */
    public function __construct(
        ReadyCategorySeoMixDataFactory $readyCategorySeoMixDataFactory,
        ReadyCategorySeoMixFacade $readyCategorySeoMixFacade
    ) {
        $this->readyCategorySeoMixDataFactory = $readyCategorySeoMixDataFactory;
        $this->readyCategorySeoMixFacade = $readyCategorySeoMixFacade;
    }

    /**
     * @inheritDoc
     */
    public function load(ObjectManager $manager)
    {
        $choseCategorySeoMixCombinationArray = [
            'domainId' => 1,
            'categoryId' => 8,
            'flagId' => 1,
            'ordering' => ProductListOrderingConfig::ORDER_BY_PRIORITY,
            'parameterValueIdsByParameterIds' => [
                38 => 75,
                40 => 79,
                37 => 73,
                39 => 77,
            ],
        ];

        $this->createReadyCategorySeoMix(
            ChoseCategorySeoMixCombination::createFromArray($choseCategorySeoMixCombinationArray),
            '2 litrové kávovary - novinky',
            ['2-litrove-kavovary-novinky', 'nove-2-litrove-kavovary-vedlejsi-adresa-ktera-by-mela-byt-presmerovana-na-hlavni'],
            1
        );

        $choseCategorySeoMixCombinationArray['flagId'] = 2;
        $this->createReadyCategorySeoMix(
            ChoseCategorySeoMixCombination::createFromArray($choseCategorySeoMixCombinationArray),
            'Nejprodávanější 2 litrové kávovary',
            ['nejprodavanejsi-2-litrove-kavovary'],
            1
        );

        $choseCategorySeoMixCombinationArray['flagId'] = 3;
        $this->createReadyCategorySeoMix(
            ChoseCategorySeoMixCombination::createFromArray($choseCategorySeoMixCombinationArray),
            '2 litrové kávovary v akci',
            ['2-litrove-kavovary-v-akci'],
            1
        );

        $choseCategorySeoMixCombinationArray = [
            'domainId' => 1,
            'categoryId' => 2,
        ];

        $choseCategorySeoMixCombinationArray['flagId'] = 1;
        $choseCategorySeoMixCombinationArray['ordering'] = ProductListOrderingConfig::ORDER_BY_PRIORITY;
        $choseCategorySeoMixCombinationArray['parameterValueIdsByParameterIds'] = [
            1 => 1,
            5 => 7,
        ];
        $this->createReadyCategorySeoMix(
            ChoseCategorySeoMixCombination::createFromArray($choseCategorySeoMixCombinationArray),
            'Elektro Novinky - TOP - 27" - HDMI',
            ['elektro-novinky-top-27-hdmi', 'nakupte-url-elektro-novinky-top-27-hdmi'],
            1
        );

        $choseCategorySeoMixCombinationArray['flagId'] = 2;
        $choseCategorySeoMixCombinationArray['ordering'] = ProductListOrderingConfig::ORDER_BY_NAME_ASC;
        $choseCategorySeoMixCombinationArray['parameterValueIdsByParameterIds'] = [
            1 => 1,
            5 => 11,
        ];
        $this->createReadyCategorySeoMix(
            ChoseCategorySeoMixCombination::createFromArray($choseCategorySeoMixCombinationArray),
            'Elektro nejprodávanější - A-Z - 27" - bez HDMI',
            ['elektro-nejprodavanejsi-a-z-27-bez-hdmi', 'nakupte-elektro-nejprodavanejsi-a-z-27-bez-hdmi'],
            1
        );

        $choseCategorySeoMixCombinationArray['flagId'] = 3;
        $choseCategorySeoMixCombinationArray['ordering'] = ProductListOrderingConfig::ORDER_BY_PRICE_ASC;
        $choseCategorySeoMixCombinationArray['parameterValueIdsByParameterIds'] = [
            1 => 13,
            5 => 11,
        ];
        $this->createReadyCategorySeoMix(
            ChoseCategorySeoMixCombination::createFromArray($choseCategorySeoMixCombinationArray),
            'Elektro Akce - od nejlevnějšího - 47 - bez hdmi',
            ['elektro-akce-od-nejlevnejsiho-47-bez-hdmi', 'nakupte-akce-nejprodavanejsi-od-nejlevnejsiho-47-bez-hdmi'],
            1
        );

        $choseCategorySeoMixCombinationArray['flagId'] = null;
        $choseCategorySeoMixCombinationArray['ordering'] = ProductListOrderingConfig::ORDER_BY_PRIORITY;
        $choseCategorySeoMixCombinationArray['parameterValueIdsByParameterIds'] = [
            5 => 7,
        ];
        $this->createReadyCategorySeoMix(
            ChoseCategorySeoMixCombination::createFromArray($choseCategorySeoMixCombinationArray),
            'Elektro s HDMI',
            ['elektro-s-hdmi', 'nakupte-elektro-s-hdmi'],
            1
        );

        $choseCategorySeoMixCombinationArray['parameterValueIdsByParameterIds'] = [
            5 => 11,
        ];
        $this->createReadyCategorySeoMix(
            ChoseCategorySeoMixCombination::createFromArray($choseCategorySeoMixCombinationArray),
            'Elektro bez HDMI',
            ['elektro-bez-hdmi', 'nakupte-elektro-bez-hdmi'],
            1
        );
    }

    /**
     * @param \App\Model\CategorySeo\ChoseCategorySeoMixCombination $choseCategorySeoMixCombination
     * @param string $h1
     * @param string[] $slugs
     * @param int $domainId
     */
    private function createReadyCategorySeoMix(
        ChoseCategorySeoMixCombination $choseCategorySeoMixCombination,
        string $h1,
        array $slugs,
        int $domainId
    ): void {
        $readyCategorySeoMixDataForForm = $this->readyCategorySeoMixDataFactory->createReadyCategorySeoMixDataForForm(
            $choseCategorySeoMixCombination
        );
        $readyCategorySeoMixDataForForm->h1 = $h1;

        $readyCategorySeoMixData = $this->readyCategorySeoMixDataFactory->createFromReadyCategorySeoMixDataForFormAndChoseCategorySeoMixCombination(
            $readyCategorySeoMixDataForForm,
            $choseCategorySeoMixCombination
        );

        $urlListData = new UrlListData();
        $urlListData->newUrls = [];
        foreach ($slugs as $slug) {
            $urlListData->newUrls[] = [
                'domain' => $domainId,
                'slug' => $slug,
            ];
        }

        $this->readyCategorySeoMixFacade->createOrEdit(
            $choseCategorySeoMixCombination,
            $readyCategorySeoMixData,
            $urlListData
        );
    }

    /**
     * @inheritDoc
     */
    public function getDependencies()
    {
        return [
            CategoryDataFixture::class,
            FlagDataFixture::class,
            ProductDataFixture::class,
        ];
    }
}
