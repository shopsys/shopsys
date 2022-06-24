<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\CategorySeo\ChoseCategorySeoMixCombination;
use App\Model\CategorySeo\ReadyCategorySeoMixDataFactory;
use App\Model\CategorySeo\ReadyCategorySeoMixFacade;
use App\Model\Product\Parameter\ParameterFacade;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Shopsys\Cdn\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListData;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig;

class ReadyCategorySeoDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    public const READY_CATEGORY_SEO_ELECTRONICS_WITHOUT_HDMI = 'ready_category_seo_electronics_without_hdmi';
    public const READY_CATEGORY_SEO_TV_FROM_CHEAPEST = 'ready_category_seo_tv_from_cheapest';
    public const READY_CATEGORY_SEO_TV_IN_SALE = 'ready_category_seo_tv_in_sale';
    public const READY_CATEGORY_SEO_TV_PLASMA_WITH_HDMI = 'ready_category_seo_tv_plasma_with_hdmi';
    public const READY_CATEGORY_SEO_PC_NEW_WITH_USB = 'ready_category_seo_pc_new_with_usb';

    /**
     * @var \App\Model\CategorySeo\ReadyCategorySeoMixDataFactory
     */
    private $readyCategorySeoMixDataFactory;

    /**
     * @var \App\Model\CategorySeo\ReadyCategorySeoMixFacade
     */
    private $readyCategorySeoMixFacade;

    /**
     * @var \Shopsys\Cdn\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @var \App\Model\Product\Parameter\ParameterFacade
     */
    private ParameterFacade $parameterFacade;

    /**
     * @param \App\Model\CategorySeo\ReadyCategorySeoMixDataFactory $readyCategorySeoMixDataFactory
     * @param \App\Model\CategorySeo\ReadyCategorySeoMixFacade $readyCategorySeoMixFacade
     * @param \Shopsys\Cdn\Component\Domain\Domain $domain
     * @param \App\Model\Product\Parameter\ParameterFacade $parameterFacade
     */
    public function __construct(
        ReadyCategorySeoMixDataFactory $readyCategorySeoMixDataFactory,
        ReadyCategorySeoMixFacade $readyCategorySeoMixFacade,
        Domain $domain,
        ParameterFacade $parameterFacade
    ) {
        $this->readyCategorySeoMixDataFactory = $readyCategorySeoMixDataFactory;
        $this->readyCategorySeoMixFacade = $readyCategorySeoMixFacade;
        $this->domain = $domain;
        $this->parameterFacade = $parameterFacade;
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
        $choseCategorySeoMixCombinationArray['ordering'] = ProductListOrderingConfig::ORDER_BY_PRIORITY;
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
            1,
            self::READY_CATEGORY_SEO_ELECTRONICS_WITHOUT_HDMI
        );

        /** @var \App\Model\Category\Category $categoryTv */
        $categoryTv = $this->getReference(CategoryDataFixture::CATEGORY_TV);
        $choseCategorySeoMixCombinationArray = [
            'domainId' => 1,
            'categoryId' => $categoryTv->getId(),
            'flagId' => null,
            'ordering' => ProductListOrderingConfig::ORDER_BY_PRICE_ASC,
            'parameterValueIdsByParameterIds' => [],
        ];
        $firstDomainLocale = $this->domain->getDomainConfigById(1)->getLocale();
        $this->createReadyCategorySeoMix(
            ChoseCategorySeoMixCombination::createFromArray($choseCategorySeoMixCombinationArray),
            t('TV, audio from the cheapest', [], 'dataFixtures', $firstDomainLocale),
            ['televize-audio-nejlevnejsi'],
            1,
            self::READY_CATEGORY_SEO_TV_FROM_CHEAPEST
        );

        /** @var \App\Model\Product\Flag\Flag $saleFlag */
        $saleFlag = $this->getReference(FlagDataFixture::FLAG_PRODUCT_SALE);
        $choseCategorySeoMixCombinationArray['flagId'] = $saleFlag->getId();
        $choseCategorySeoMixCombinationArray['ordering'] = ProductListOrderingConfig::ORDER_BY_PRIORITY;
        $this->createReadyCategorySeoMix(
            ChoseCategorySeoMixCombination::createFromArray($choseCategorySeoMixCombinationArray),
            t('TV, audio in sale', [], 'dataFixtures', $firstDomainLocale),
            ['televize-audio-vyprodej'],
            1,
            self::READY_CATEGORY_SEO_TV_IN_SALE
        );

        $choseCategorySeoMixCombinationArray['flagId'] = null;
        /** @var \App\Model\Product\Parameter\Parameter $technologyParameter */
        $technologyParameter = $this->getReference(ParameterDataFixture::PARAMETER_PREFIX . t('Technology', [], 'dataFixtures', $firstDomainLocale));
        /** @var \App\Model\Product\Parameter\Parameter $hdmiParameter */
        $hdmiParameter = $this->getReference(ParameterDataFixture::PARAMETER_PREFIX . t('HDMI', [], 'dataFixtures', $firstDomainLocale));
        $choseCategorySeoMixCombinationArray['parameterValueIdsByParameterIds'] = [
            $hdmiParameter->getId() => $this->getParameterValueId(t('Yes', [], 'dataFixtures', $firstDomainLocale), $firstDomainLocale),
            $technologyParameter->getId() => $this->getParameterValueId(t('PLASMA', [], 'dataFixtures', $firstDomainLocale), $firstDomainLocale),
        ];
        $this->createReadyCategorySeoMix(
            ChoseCategorySeoMixCombination::createFromArray($choseCategorySeoMixCombinationArray),
            t('TV, audio plasma with HDMI', [], 'dataFixtures', $firstDomainLocale),
            ['televize-audio-plasma-s-hdmi'],
            1,
            self::READY_CATEGORY_SEO_TV_PLASMA_WITH_HDMI
        );

        /** @var \App\Model\Category\Category $categoryPc */
        $categoryPc = $this->getReference(CategoryDataFixture::CATEGORY_PC);
        /** @var \App\Model\Product\Flag\Flag $newFlag */
        $newFlag = $this->getReference(FlagDataFixture::FLAG_PRODUCT_NEW);
        /** @var \App\Model\Product\Parameter\Parameter $usbParameter */
        $usbParameter = $this->getReference(ParameterDataFixture::PARAMETER_PREFIX . t('USB', [], 'dataFixtures', $firstDomainLocale));
        /** @var \App\Model\Product\Parameter\Parameter $warrantyParameter */
        $warrantyParameter = $this->getReference(ParameterDataFixture::PARAMETER_SLIDER_WARRANTY);
        $choseCategorySeoMixCombinationArray = [
            'domainId' => 1,
            'categoryId' => $categoryPc->getId(),
            'flagId' => $newFlag->getId(),
            'ordering' => ProductListOrderingConfig::ORDER_BY_PRICE_DESC,
            'parameterValueIdsByParameterIds' => [
                $usbParameter->getId() => $this->getParameterValueId(t('Yes', [], 'dataFixtures', $firstDomainLocale), $firstDomainLocale),
                $warrantyParameter->getId() => $this->getParameterValueId(t('4', [], 'dataFixtures', $firstDomainLocale), $firstDomainLocale),
            ],
        ];
        $firstDomainLocale = $this->domain->getDomainConfigById(1)->getLocale();
        $this->createReadyCategorySeoMix(
            ChoseCategorySeoMixCombination::createFromArray($choseCategorySeoMixCombinationArray),
            t('New computers with USB', [], 'dataFixtures', $firstDomainLocale),
            ['nove-pc-s-usb'],
            1,
            self::READY_CATEGORY_SEO_PC_NEW_WITH_USB
        );
    }

    /**
     * @param \App\Model\CategorySeo\ChoseCategorySeoMixCombination $choseCategorySeoMixCombination
     * @param string $h1
     * @param string[] $slugs
     * @param int $domainId
     * @param string|null $referenceName
     */
    private function createReadyCategorySeoMix(
        ChoseCategorySeoMixCombination $choseCategorySeoMixCombination,
        string $h1,
        array $slugs,
        int $domainId,
        ?string $referenceName = null
    ): void {
        $readyCategorySeoMixDataForForm = $this->readyCategorySeoMixDataFactory->createReadyCategorySeoMixDataForForm(
            $choseCategorySeoMixCombination
        );
        $readyCategorySeoMixDataForForm->h1 = $h1;

        $readyCategorySeoMixData = $this->readyCategorySeoMixDataFactory->createFromReadyCategorySeoMixDataForFormAndChoseCategorySeoMixCombination(
            $readyCategorySeoMixDataForForm,
            $choseCategorySeoMixCombination
        );
        $readyCategorySeoMixData->showInCategory = true;

        $urlListData = new UrlListData();
        $urlListData->newUrls = [];
        foreach ($slugs as $slug) {
            $urlListData->newUrls[] = [
                'domain' => $domainId,
                'slug' => $slug,
            ];
        }

        $readyCategorySeoMix = $this->readyCategorySeoMixFacade->createOrEdit(
            $choseCategorySeoMixCombination,
            $readyCategorySeoMixData,
            $urlListData
        );

        if ($referenceName !== null) {
            $this->persistentReferenceFacade->persistReferenceForDomain($referenceName, $readyCategorySeoMix, $domainId);
        }
    }

    /**
     * @param string $parameterValueTranslation
     * @param string $locale
     * @return int
     */
    private function getParameterValueId(string $parameterValueTranslation, string $locale): int
    {
        return $this->parameterFacade->getParameterValueByValueTextAndLocale($parameterValueTranslation, $locale)->getId();
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
