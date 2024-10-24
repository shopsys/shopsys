<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Category\Category;
use App\Model\Product\Flag\Flag;
use App\Model\Product\Parameter\Parameter;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListData;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\CategorySeo\ChoseCategorySeoMixCombination;
use Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMixDataFactory;
use Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMixFacade;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade;

class ReadyCategorySeoDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    public const string READY_CATEGORY_SEO_ELECTRONICS_WITHOUT_HDMI_PROMOTION = 'ready_category_seo_electronics_without_hdmi_promotion';
    public const string READY_CATEGORY_SEO_TV_FROM_CHEAPEST = 'ready_category_seo_tv_from_cheapest';
    public const string READY_CATEGORY_SEO_TV_IN_SALE = 'ready_category_seo_tv_in_sale';
    public const string READY_CATEGORY_SEO_TV_PLASMA_WITH_HDMI = 'ready_category_seo_tv_plasma_with_hdmi';
    public const string READY_CATEGORY_SEO_PC_NEW_WITH_USB = 'ready_category_seo_pc_new_with_usb';
    public const string READY_CATEGORY_SEO_BLACK_ELECTRONICS = 'ready_category_seo_black_electronics';

    /**
     * @param \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMixDataFactory $readyCategorySeoMixDataFactory
     * @param \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMixFacade $readyCategorySeoMixFacade
     * @param \App\Model\Product\Parameter\ParameterFacade $parameterFacade
     */
    public function __construct(
        private readonly ReadyCategorySeoMixDataFactory $readyCategorySeoMixDataFactory,
        private readonly ReadyCategorySeoMixFacade $readyCategorySeoMixFacade,
        private readonly ParameterFacade $parameterFacade,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager): void
    {
        $firstDomain = $this->domainsForDataFixtureProvider->getFirstAllowedDomainConfig();
        $firstDomainId = $firstDomain->getId();
        $firstDomainLocale = $firstDomain->getLocale();

        $choseCategorySeoMixCombinationArray = [
            'domainId' => $firstDomainId,
            'categoryId' => 8,
            'flagId' => 3,
            'ordering' => ProductListOrderingConfig::ORDER_BY_PRIORITY,
            'parameterValueIdsByParameterIds' => [
                $this->getReference(ParameterDataFixture::PARAM_WATER_RESERVOIR_CAPACITY, Parameter::class)->getId() => $this->parameterFacade->getParameterValueByValueTextAndLocale(t('2 l', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale), $firstDomainLocale)->getId(),
                $this->getReference(ParameterDataFixture::PARAM_MAGAZINE_CAPACITY_FOR_BEANS, Parameter::class)->getId() => $this->parameterFacade->getParameterValueByValueTextAndLocale(t('400 g', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale), $firstDomainLocale)->getId(),
                $this->getReference(ParameterDataFixture::PARAM_PRESSURE, Parameter::class)->getId() => $this->parameterFacade->getParameterValueByValueTextAndLocale(t('15 bar', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale), $firstDomainLocale)->getId(),
                $this->getReference(ParameterDataFixture::PARAM_MILK_RESERVOIR_CAPACITY, Parameter::class)->getId() => $this->parameterFacade->getParameterValueByValueTextAndLocale(t('600 ml', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale), $firstDomainLocale)->getId(),
            ],
        ];

        $this->createReadyCategorySeoMix(
            ChoseCategorySeoMixCombination::createFromArray($choseCategorySeoMixCombinationArray),
            t('2 litre coffeemakers in sale', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            ['2-litrove-kavovary-v-akci'],
            $firstDomainId,
            null,
            t('description of 2 litre coffeemakers in sale seo category', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            t('short description of 2 litre coffeemakers in sale seo category', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            t('title of 2 litre coffeemakers in sale seo category', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            t('meta description of 2 litre coffeemakers in sale seo category', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
        );

        $categoryElectronics = $this->getReference(CategoryDataFixture::CATEGORY_ELECTRONICS, Category::class);

        $choseCategorySeoMixCombinationArray = [
            'domainId' => $firstDomainId,
            'categoryId' => $categoryElectronics->getId(),
        ];
        $choseCategorySeoMixCombinationArray['flagId'] = 2;
        $choseCategorySeoMixCombinationArray['ordering'] = ProductListOrderingConfig::ORDER_BY_PRIORITY;
        $choseCategorySeoMixCombinationArray['parameterValueIdsByParameterIds'] = [
            $this->getReference(ParameterDataFixture::PARAM_HDMI, Parameter::class)->getId() => $this->parameterFacade->getParameterValueByValueTextAndLocale(t('No', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale), $firstDomainLocale)->getId(),
        ];
        $this->createReadyCategorySeoMix(
            ChoseCategorySeoMixCombination::createFromArray($choseCategorySeoMixCombinationArray),
            t('Electronics without HDMI in sale', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            ['elektro-bez-hdmi-akce'],
            $firstDomainId,
            self::READY_CATEGORY_SEO_ELECTRONICS_WITHOUT_HDMI_PROMOTION,
            t('description of Electronics without HDMI in sale seo category', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            t('short description of Electronics without HDMI in sale seo category', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
        );

        $choseCategorySeoMixCombinationArray['parameterValueIdsByParameterIds'] = [
            $this->getReference(ParameterDataFixture::PARAM_SCREEN_SIZE, Parameter::class)->getId() => $this->parameterFacade->getParameterValueByValueTextAndLocale(t('30"', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale), $firstDomainLocale)->getId(),
            $this->getReference(ParameterDataFixture::PARAM_TECHNOLOGY, Parameter::class)->getId() => $this->parameterFacade->getParameterValueByValueTextAndLocale(t('LED', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale), $firstDomainLocale)->getId(),
        ];
        $this->createReadyCategorySeoMix(
            ChoseCategorySeoMixCombination::createFromArray($choseCategorySeoMixCombinationArray),
            t('Electronics with LED technology and size 30 inch in sale', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            ['elektro-led-uhlopricka-30-akce'],
            $firstDomainId,
            null,
            t('description of Electronics with LED technology and size 30 inch in sale seo category', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            t('short description of Electronics with LED technology and size 30 inch in sale seo category', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            t('title of Electronics with LED technology and size 30 inch in sale seo category', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            t('meta description of Electronics with LED technology and size 30 inch in sale seo category', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
        );

        $choseCategorySeoMixCombinationArray['flagId'] = null;
        $choseCategorySeoMixCombinationArray['ordering'] = ProductListOrderingConfig::ORDER_BY_PRICE_DESC;
        $choseCategorySeoMixCombinationArray['parameterValueIdsByParameterIds'] = [];
        $this->createReadyCategorySeoMix(
            ChoseCategorySeoMixCombination::createFromArray($choseCategorySeoMixCombinationArray),
            t('Electronics from most expensive', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            ['elektro-od-nejdrazsiho'],
            $firstDomainId,
            null,
            t('description of Electronics from most expensive seo category', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            t('short description of Electronics from most expensive seo category', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            t('title of Electronics from most expensive seo category', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            t('meta description of Electronics from most expensive seo category', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
        );

        $choseCategorySeoMixCombinationArray['ordering'] = ProductListOrderingConfig::ORDER_BY_PRIORITY;
        $choseCategorySeoMixCombinationArray['flagId'] = null;
        $choseCategorySeoMixCombinationArray['parameterValueIdsByParameterIds'] = [
            $this->getReference(ParameterDataFixture::PARAM_USB, Parameter::class)->getId() => $this->parameterFacade->getParameterValueByValueTextAndLocale(t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale), $firstDomainLocale)->getId(),
            $this->getReference(ParameterDataFixture::PARAM_TECHNOLOGY, Parameter::class)->getId() => $this->parameterFacade->getParameterValueByValueTextAndLocale(t('LED', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale), $firstDomainLocale)->getId(),
            $this->getReference(ParameterDataFixture::PARAM_RESOLUTION, Parameter::class)->getId() => $this->parameterFacade->getParameterValueByValueTextAndLocale(t('1920×1080 (Full HD)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale), $firstDomainLocale)->getId(),
        ];
        $this->createReadyCategorySeoMix(
            ChoseCategorySeoMixCombination::createFromArray($choseCategorySeoMixCombinationArray),
            t('Full HD Electronics with LED technology and USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            ['elektro-full-hd-led-usb'],
            $firstDomainId,
            null,
            t('description of Full HD Electronics with LED technology and USB seo category', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            t('short description of Full HD Electronics with LED technology and USB seo category', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            t('title of Full HD Electronics with LED technology and USB seo category', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            t('meta description of Full HD Electronics with LED technology and USB seo category', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
        );

        $choseCategorySeoMixCombinationArray['flagId'] = null;
        $choseCategorySeoMixCombinationArray['parameterValueIdsByParameterIds'] = [
            $this->getReference(ParameterDataFixture::PARAM_COLOR, Parameter::class)->getId() => $this->parameterFacade->getParameterValueByValueTextAndLocale(t('black', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale), $firstDomainLocale)->getId(),
        ];
        $this->createReadyCategorySeoMix(
            ChoseCategorySeoMixCombination::createFromArray($choseCategorySeoMixCombinationArray),
            t('Electronics in black', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            ['elektro-barva-cerna'],
            $firstDomainId,
            self::READY_CATEGORY_SEO_BLACK_ELECTRONICS,
            t('description of Electronics in black seo category', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            t('short description of Electronics in black seo category', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            t('title of Electronics in black seo category', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            t('meta description of Electronics in black seo category', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
        );

        $choseCategorySeoMixCombinationArray['parameterValueIdsByParameterIds'] = [
            $this->getReference(ParameterDataFixture::PARAM_COLOR, Parameter::class)->getId() => $this->parameterFacade->getParameterValueByValueTextAndLocale(t('red', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale), $firstDomainLocale)->getId(),
        ];
        $this->createReadyCategorySeoMix(
            ChoseCategorySeoMixCombination::createFromArray($choseCategorySeoMixCombinationArray),
            t('Electronics in red', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            ['elektro-barva-cervena'],
            $firstDomainId,
            null,
            t('description of Electronics in red seo category', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            t('short description of Electronics in red seo category', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            t('title of Electronics in red seo category', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            t('meta description of Electronics in red seo category', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
        );

        $categoryTv = $this->getReference(CategoryDataFixture::CATEGORY_TV, Category::class);
        $choseCategorySeoMixCombinationArray = [
            'domainId' => $firstDomainId,
            'categoryId' => $categoryTv->getId(),
            'flagId' => null,
            'ordering' => ProductListOrderingConfig::ORDER_BY_PRICE_ASC,
            'parameterValueIdsByParameterIds' => [],
        ];

        $this->createReadyCategorySeoMix(
            ChoseCategorySeoMixCombination::createFromArray($choseCategorySeoMixCombinationArray),
            t('TV, audio from the cheapest', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            ['televize-audio-nejlevnejsi'],
            $firstDomainId,
            self::READY_CATEGORY_SEO_TV_FROM_CHEAPEST,
            t('description of TV, audio from the cheapest seo category', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            t('short description of TV, audio from the cheapest seo category', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            t('title of TV, audio from the cheapest seo category', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            t('meta description of TV, audio from the cheapest seo category', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
        );

        $saleFlag = $this->getReference(FlagDataFixture::FLAG_PRODUCT_SALE, Flag::class);
        $choseCategorySeoMixCombinationArray['flagId'] = $saleFlag->getId();
        $choseCategorySeoMixCombinationArray['ordering'] = ProductListOrderingConfig::ORDER_BY_PRIORITY;
        $this->createReadyCategorySeoMix(
            ChoseCategorySeoMixCombination::createFromArray($choseCategorySeoMixCombinationArray),
            t('TV, audio in sale', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            ['televize-audio-vyprodej'],
            $firstDomainId,
            self::READY_CATEGORY_SEO_TV_IN_SALE,
            t('description of TV, audio in sale seo category', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            t('short description of TV, audio in sale seo category', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            t('title of TV, audio in sale seo category', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            t('meta description of TV, audio in sale seo category', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
        );

        $choseCategorySeoMixCombinationArray['flagId'] = null;
        $technologyParameter = $this->getReference(ParameterDataFixture::PARAM_TECHNOLOGY, Parameter::class);
        $hdmiParameter = $this->getReference(ParameterDataFixture::PARAM_HDMI, Parameter::class);
        $choseCategorySeoMixCombinationArray['parameterValueIdsByParameterIds'] = [
            $hdmiParameter->getId() => $this->getParameterValueId(t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale), $firstDomainLocale),
            $technologyParameter->getId() => $this->getParameterValueId(t('PLASMA', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale), $firstDomainLocale),
        ];
        $this->createReadyCategorySeoMix(
            ChoseCategorySeoMixCombination::createFromArray($choseCategorySeoMixCombinationArray),
            t('TV, audio plasma with HDMI', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            ['televize-audio-plasma-s-hdmi'],
            $firstDomainId,
            self::READY_CATEGORY_SEO_TV_PLASMA_WITH_HDMI,
            t('description of TV, audio plasma with HDMI seo category', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            t('short description of TV, audio plasma with HDMI seo category', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            t('title of TV, audio plasma with HDMI seo category', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            t('meta description of TV, audio plasma with HDMI seo category', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
        );

        $categoryPc = $this->getReference(CategoryDataFixture::CATEGORY_PC, Category::class);
        $newFlag = $this->getReference(FlagDataFixture::FLAG_PRODUCT_NEW, Flag::class);
        $usbParameter = $this->getReference(ParameterDataFixture::PARAM_USB, Parameter::class);
        $choseCategorySeoMixCombinationArray = [
            'domainId' => $firstDomainId,
            'categoryId' => $categoryPc->getId(),
            'flagId' => $newFlag->getId(),
            'ordering' => ProductListOrderingConfig::ORDER_BY_PRICE_DESC,
            'parameterValueIdsByParameterIds' => [
                $usbParameter->getId() => $this->getParameterValueId(t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale), $firstDomainLocale),
            ],
        ];
        $this->createReadyCategorySeoMix(
            ChoseCategorySeoMixCombination::createFromArray($choseCategorySeoMixCombinationArray),
            t('New computers with USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            ['nove-pc-s-usb'],
            $firstDomainId,
            self::READY_CATEGORY_SEO_PC_NEW_WITH_USB,
            t('description of New computers with USB seo category', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            t('short description of New computers with USB seo category', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            t('title of New computers with USB seo category', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            t('meta description of New computers with USB seo category', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\CategorySeo\ChoseCategorySeoMixCombination $choseCategorySeoMixCombination
     * @param string $h1
     * @param string[] $slugs
     * @param int $domainId
     * @param string|null $referenceName
     * @param string|null $description
     * @param string|null $shortDescription
     * @param string|null $title
     * @param string|null $metaDescription
     */
    private function createReadyCategorySeoMix(
        ChoseCategorySeoMixCombination $choseCategorySeoMixCombination,
        string $h1,
        array $slugs,
        int $domainId,
        ?string $referenceName = null,
        ?string $description = null,
        ?string $shortDescription = null,
        ?string $title = null,
        ?string $metaDescription = null,
    ): void {
        $readyCategorySeoMixData = $this->readyCategorySeoMixDataFactory->createReadyCategorySeoMixData(
            $choseCategorySeoMixCombination,
        );
        $readyCategorySeoMixData->h1 = $h1;

        $this->readyCategorySeoMixDataFactory->fillValuesFromChoseCategorySeoMixCombination(
            $readyCategorySeoMixData,
            $choseCategorySeoMixCombination,
        );
        $readyCategorySeoMixData->showInCategory = true;
        $readyCategorySeoMixData->description = $description;
        $readyCategorySeoMixData->shortDescription = $shortDescription;
        $readyCategorySeoMixData->title = $title;
        $readyCategorySeoMixData->metaDescription = $metaDescription;

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
            $urlListData,
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
     * {@inheritdoc}
     */
    public function getDependencies(): array
    {
        return [
            CategoryDataFixture::class,
            FlagDataFixture::class,
            ProductDataFixture::class,
            ParameterDataFixture::class,
        ];
    }
}
