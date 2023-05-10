<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Product\Parameter\Parameter;
use App\Model\Product\Parameter\ParameterDataFactory;
use App\Model\Product\Parameter\ParameterGroupDataFactory;
use App\Model\Product\Parameter\ParameterGroupFacade;
use App\Model\Product\Product;
use App\Model\Product\ProductData;
use App\Model\Stock\ProductStockDataFactory;
use App\Model\Stock\StockRepository;
use App\Model\Store\ProductStoreDataFactory;
use DateTime;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade;
use Shopsys\FrameworkBundle\Model\Pricing\PriceConverter;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter as BaseParameter;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueDataFactory;
use Shopsys\FrameworkBundle\Model\Product\ProductDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;

class ProductDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    public const PRODUCT_PREFIX = 'product_';

    /**
     * @var \App\Model\Product\ProductFacade
     */
    private $productFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade
     */
    private $pricingGroupFacade;

    /**
     * @var \App\Model\Product\ProductDataFactory
     */
    private $productDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueDataFactory
     */
    private $productParameterValueDataFactory;

    /**
     * @var \App\Model\Product\Parameter\ParameterValueDataFactory
     */
    private $parameterValueDataFactory;

    /**
     * @var \App\Model\Product\Parameter\ParameterFacade
     */
    private $parameterFacade;

    /**
     * @var \App\Model\Product\Parameter\ParameterDataFactory
     */
    private $parameterDataFactory;

    /**
     * @var int
     */
    private $productNo = 1;

    /**
     * @var int[]
     */
    private $productIdsByCatnum = [];

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\PriceConverter
     */
    private $priceConverter;

    /**
     * @var \App\Model\Product\Parameter\ParameterGroupDataFactory|\App\Model\Product\Parameter\ParameterGroupDataFactory
     */
    private $parameterGroupDataFactory;

    /**
     * @var \Faker\Generator
     */
    private $generator;

    /**
     * @var \App\Model\Product\Parameter\ParameterGroupFacade
     */
    private $parameterGroupFacade;

    /**
     * @var \App\Model\Stock\StockRepository
     */
    private $stockRepository;

    /**
     * @var \App\Model\Stock\ProductStockDataFactory
     */
    private $productStockDataFactory;

    /**
     * @var \App\Model\Store\ProductStoreDataFactory
     */
    private ProductStoreDataFactory $productStoreDataFactory;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @var string[]
     */
    private array $uuidPool = [
        '5d92301d-1583-4505-842a-27fe6854f587', '06cdd679-9a04-439c-b950-c9de7392f142', '789d9b6e-c40a-4462-af66-c7aa1131d427',
        '261e430c-dc24-488b-ade8-d34ae13a56f5', 'c8284983-1158-42be-bf6c-2821427242ae', 'f4ee85cc-ad0b-463d-9f3e-427d7c27f166',
        '65410d11-c223-49d3-8dc8-31627ce2c81e', 'c710b55d-b4c2-45e6-bed2-6fd25c8d9200', '69d7de81-9811-44ef-aeed-e8f59c3ef896',
        'bcaaf5a4-e7f1-4288-bf83-2b074a4be011', '2259d657-2b8e-495f-9684-38b92b823b24', '1ee088f5-6717-4182-8b95-03a2e74bb4a2',
        '4fecaec6-b25d-4ae6-91e0-f114925c8c4b', '84cf4963-b747-432c-9c06-300c6ef32046', '97999af8-b1c1-4f72-a07c-288ea3f2f259',
        'd5ea5f11-dd11-4867-867d-4df71a063b1e', '13d862a6-7856-46ed-ac7c-181bf6376cf5', '96faf2b6-aae4-488b-b653-326d41e70ffe',
        'd924182d-0d0d-434f-a099-1f69a70ec0ae', '0e6693ae-3c2a-4615-bc3c-5e7eadd81a5b', '686baf7a-1688-42ba-a21d-e0e1090ae7d1',
        'fc5a9f22-2425-4af4-a97a-4964b057791f', 'd50d80c2-dbba-4b4b-bad9-cf0b6204b0fd', '352ee59b-4e9a-4421-bddb-2543cd5def1e',
        'a3bf9a5d-017b-4574-99d8-f906347e69a8', 'a55c54fc-1720-4683-8a6a-979a8368d6cb', 'cc8b7c72-b109-4a7a-96de-75db9a0b243d',
        '2514a42f-535a-48e5-9ef3-1cceb2a2547f', 'af59a3f3-dbe8-4b12-ba3b-53fc73903e72', '1b1f7b38-f8a3-4898-9162-09321fdd2f3c',
        '06ec8224-f068-4e4c-ab1b-807f0e7f825c', 'ff43c17c-c2af-479e-a9cc-3b382dd67969', '95479dc7-da74-44d6-b65b-a5a73ade8e40',
        'd187efb2-6278-45fe-9653-55876bf19fcd', '98385437-9120-4dde-b064-7e2a439ce6d8', '288b69fb-2760-4135-b99a-2f5eb5a497f3',
        '2aff0ccc-8ea1-44e3-9928-c8172db515cf', '1a86597d-acfa-4c30-b86e-ce462a3d3c9b', '8805f5c6-835b-41e0-b30d-0e8ca959e056',
        '7c66e321-b1e6-4545-93f4-1e252aea2ef5', '3091cdbd-8d79-47ab-a858-53212eb3a91e', '6f392be6-1b71-431a-be6d-56297c0f978a',
        'e20ce277-8208-4a96-8700-7ce0342a25f6', '3d3b7eab-5fb5-455b-922b-648654524327', '43d8cd8c-4871-4068-b0f0-7c7e7d582671',
        'ec98aa55-908d-4cc2-bfa1-7aca317c7ef1', 'b620db85-b7c0-4d19-b1d0-9fdcd94ea01a', '8f2c75b6-16ac-4339-8541-94ce0ff5177e',
        'bb9d70f3-1df7-4daf-a7b5-ec856aadecb1', '75f273b4-574d-4b8c-a757-de919984eeda', '04d4abb2-375b-4a99-a77c-444d8a24227f',
        '0d37507d-7c5e-4bdb-a8ca-80d0253a143f', 'ead53d15-44db-448d-9794-48977953fc43', 'a34fd0fe-eff8-4897-a3be-3b0f293b9507',
        '4f5fcfe0-b8cc-4801-a3e9-920e8b5d9064', '25ce34ff-c441-4a2d-add4-2c8b0488c0cd', '25c2746d-f0d5-4a98-a6c4-12dbf62f9b2d',
        '278e16f3-0a5c-490b-ba94-3215d93600bd', '21f49338-63dd-41a4-a3f7-ab4657edae54', 'a27735e0-6baa-475d-9532-480f161f1803',
        'aa1dd79f-8c41-479f-96ea-8ec4e129fa06', '04c7ce3a-ab7c-4ab7-9a91-fb388397ddd1', '4907b9ba-30b7-44d0-9d0a-ae6dcc426192',
        '4948d5bd-dd6b-417c-8c38-d78d843ceb94', '8374c3be-6781-4223-a9c9-816d73cd3373', '991756a8-6488-4d45-b808-eadf33d1fa97',
        'd96af534-9797-42a9-8441-e2ee18c25467', '260e3314-4612-4eff-aefd-fc6547255708', 'a1c4b3c0-a043-4428-8bb7-076f38d76f89',
        '7ac686ce-4db8-4242-957e-b65bcff6f427', '4e86dacf-d0db-43f5-9fe8-3846409acc3f', '25693e70-2566-4338-ac2b-5d4c392aaf4e',
        '0141c798-e90f-4c7d-8f73-e4966d00f267', 'ca18d0fa-5814-4a88-8f6c-fec6af7c77da', 'ed8373c6-2ec2-46a6-8fd3-b33c67dfd1ee',
        'bafb2eed-9713-4a8b-a624-1009d095ad5d', 'e411c045-5a1f-4d62-9050-a6d28aca77be', '3fa5039b-83c5-4d4b-85c0-1c8ec52dc03e',
        'c4ed61a4-b9eb-4e41-9802-a02cf20d9a1a', '4d1f4265-2715-4385-8d6a-45dd5b42d716', 'dc7249ff-7a50-45ef-8212-f66a511bbe74',
        '765b50c2-b470-42a6-a904-8db468d12792', '9434a5ad-c880-402c-93e2-727f67619f31', 'ef51e43d-d2d1-4ce0-b9f1-4be762df6616',
        '47650da2-2b67-4669-b303-cd464cb4b732', '388c2721-432f-4ca7-a971-7bdb2b7de06e', 'a29d990c-dde5-49c1-bc1f-15741fb68965',
        '74ce26de-c2b9-4a0f-a027-eff70122df77', '6d87eac1-7aca-454d-8753-bee3be0c097a', '167acf4d-de3a-40a8-a2d1-76dcd4c03d54',
        '86379dd8-211d-44b7-a3d2-e3b88a7fa830', '2fffee69-8027-4d60-a262-be0594bdbd04', 'ab4ca0c8-6bb6-4c55-b757-64e9ed36c1d4',
        'aa2654e3-2c44-447c-9df7-563347fed03e', '33a38bea-8646-47c6-9d25-286e188bc5c7', 'dd76892c-35bf-4e5c-9b9a-82e8b4645f9c',
        '51da5060-4ead-45eb-99b0-3a1aafb39b9d', 'fd4900bf-f709-4025-bc39-c0a48f26e9fa', '42bb397d-5e1d-475f-94bf-51c1d9afc16f',
        '346f52f0-1e4e-45ad-9931-8f89a0102bb1', '028aa298-a140-4d07-b6a3-f13928dd6622', '5cd30df1-6ec5-49a9-a13f-84a3c66eb186',
        'f2153d68-24d5-4be1-bbbf-ce1a6912b70b', 'ffc33582-f784-44b7-9841-89a46bd67708', '5786be24-6fad-43a7-b8d5-788f882b7a69',
        '4ef37ea7-f820-4441-a3e5-b83d6bd55456', '0bfd6623-6ed9-48ae-bbff-bd09c3d31365', 'e8ef6e8e-4f13-4e53-ae38-3e3d3b448dde',
        '784c253e-ff9d-4eff-8356-176262c2d46d', '19741bef-a9d7-4e02-b142-1e97a6472188', '010fcce7-2360-4253-b154-6a5b54baebc5',
        'ee25f5de-4968-4046-b259-58e277e86ae2', 'c8cf94dc-5834-4131-98cc-970b189a621b', '27a89829-d5e9-47e2-a100-7a514006f1b4',
        '8a46c0ae-caf3-4fcd-be47-dcad38b61f2e', '3650928f-6657-47db-adaf-d175a5ad1bb4', 'e9d7a12d-d6f7-4bb9-968b-7efd6952708c',
        '95b9af97-c9fd-47fc-bb69-5e938648e107', '582da6f1-6cda-4642-a50e-0a66755833eb', '3cecf96f-0c50-480e-a083-233eb63cea10',
        'b4cd58af-f962-48af-9ac7-12bf7ad6f29f', '4b7d8d79-e6a4-4ee7-b28e-06711b2733bf', '158c8a14-e357-4dc8-9361-24f06e1689e6',
        '6e9eb128-3037-4ed4-aeb1-b5e53fb17562', '776881d3-b2cb-4c80-a433-dfad38bad18d', 'ed297c65-dd3f-4189-a108-a5c4ddb96336',
        'becfaa8f-9270-4677-8ff7-5a7075b92d36', '4d8c9aa6-f64a-46f0-b010-6266038659a6', '32f82f2b-c9db-407f-b7b5-18c55afe3fb4',
        '58661e7f-7deb-4adc-b5cb-1f017af6e6c1', '720e1508-96a9-41cb-8fef-de9c7c46b845', 'f087d427-f91c-4049-a505-4323b73b8711',
        '9a6390ce-55de-41fc-8a2d-0b034ab0b174', 'debf3087-c21f-4498-a209-ea0ae25f1558', '1eb283ce-5988-4c8f-9cb9-4cbc8e2de575',
        '17cf823e-877b-4855-81ee-e14058d7bd15', '259123df-8ea7-455b-bbbc-26ca33f075fa', '687b88c1-94cc-429c-92de-23c7450dd1db',
        'dd18d988-3fb4-4bf9-9875-d852d9b2f89d', '1ee3d5a0-4f6e-4dc7-bf6b-02595092af93', '5bd706d2-fb06-4a90-bfe7-9970cce6043f',
        'd4f44460-f40e-48bd-8aae-9d17a7887e4b', '46933250-89a8-4d15-b736-15ac6b66a739', 'a3f3f907-b3f1-46d2-b493-7eeff3e1c350',
        '5e44c902-2478-44b6-a2b1-7ff8a5ba6df2', '6096344f-7492-4f54-b40e-4743902a599a', '2409abb1-bf67-4ab4-b05f-51947511cbdf',
        'af821f54-645e-4546-9d67-747c03b71c48', 'b9c83950-db85-47c5-80e3-951b9f3a588f', '62fa5f87-bd9a-4bf5-816a-04f557af98c3',
        'aac54887-f9d9-4e02-9739-65a9d070f4cc', '5271462b-1d38-4a18-9d76-fbc06247c6f0', '8c44b857-527d-41e2-a128-fc042b41736f',
    ];

    /**
     * @param \App\Model\Product\ProductFacade $productFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade $pricingGroupFacade
     * @param \App\Model\Product\ProductDataFactory $productDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueDataFactory $productParameterValueDataFactory
     * @param \App\Model\Product\Parameter\ParameterValueDataFactory $parameterValueDataFactory
     * @param \App\Model\Product\Parameter\ParameterFacade $parameterFacade
     * @param \App\Model\Product\Parameter\ParameterDataFactory $parameterDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PriceConverter $priceConverter
     * @param \App\Model\Product\Parameter\ParameterGroupDataFactory $parameterGroupDataFactory
     * @param \Faker\Generator $generator
     * @param \App\Model\Product\Parameter\ParameterGroupFacade $parameterGroupFacade
     * @param \App\Model\Stock\StockRepository $stockRepository
     * @param \App\Model\Stock\ProductStockDataFactory $productStockDataFactory
     * @param \App\Model\Store\ProductStoreDataFactory $productStoreDataFactory
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        ProductFacade $productFacade,
        Domain $domain,
        PricingGroupFacade $pricingGroupFacade,
        ProductDataFactoryInterface $productDataFactory,
        ProductParameterValueDataFactory $productParameterValueDataFactory,
        ParameterValueDataFactoryInterface $parameterValueDataFactory,
        ParameterFacade $parameterFacade,
        ParameterDataFactory $parameterDataFactory,
        PriceConverter $priceConverter,
        ParameterGroupDataFactory $parameterGroupDataFactory,
        Generator $generator,
        ParameterGroupFacade $parameterGroupFacade,
        StockRepository $stockRepository,
        ProductStockDataFactory $productStockDataFactory,
        ProductStoreDataFactory $productStoreDataFactory,
        EntityManagerInterface $em
    ) {
        $this->productFacade = $productFacade;
        $this->domain = $domain;
        $this->pricingGroupFacade = $pricingGroupFacade;
        $this->productDataFactory = $productDataFactory;
        $this->productParameterValueDataFactory = $productParameterValueDataFactory;
        $this->parameterValueDataFactory = $parameterValueDataFactory;
        $this->parameterFacade = $parameterFacade;
        $this->parameterDataFactory = $parameterDataFactory;
        $this->priceConverter = $priceConverter;
        $this->parameterGroupDataFactory = $parameterGroupDataFactory;
        $this->generator = $generator;
        $this->parameterGroupFacade = $parameterGroupFacade;
        $this->stockRepository = $stockRepository;
        $this->productStockDataFactory = $productStockDataFactory;
        $this->em = $em;
        $this->productStoreDataFactory = $productStoreDataFactory;
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $this->domain->switchDomainById(Domain::FIRST_DOMAIN_ID);

        /** @var \App\Model\Product\ProductData $productData */
        $productData = $this->productDataFactory->create();

        $productData->catnum = '9177759';
        $productData->partno = 'SLE 22F46DM4';
        $productData->ean = '8845781245930';
        $productData->orderingPriority = 1;
        $productData->weight = 3000;

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->namePrefix[$locale] = t('Televize', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->name[$locale] = t('22" Sencor SLE 22F46DM4 HELLO KITTY', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->nameSufix[$locale] = t('plazmová', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Television LED, 55 cm diagonal, 1920x1080 Full HD, DVB-T MPEG4 tuner with USB recording and playback (DivX, XviD, MP3, WMA, JPEG), HDMI, SCART, VGA, pink execution, energ. Class B', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Television LED, 55 cm diagonal, 1920x1080 Full HD, DVB-T MPEG4 tuner with USB recording and playback', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $productData->seoH1s[$domain->getId()] = t('Hello Kitty Television', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->seoTitles[$domain->getId()] = t('Hello Kitty TV', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->seoMetaDescriptions[$domain->getId()] = t('Hello Kitty TV, LED, 55 cm diagonal, 1920x1080 Full HD.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Screen size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('27"', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i, t('Hlavní údaje', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), UnitDataFixture::UNIT_INCH);
            $this->addParameterTranslations($parameterTranslations, t('Technology', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('LED', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i, t('Hlavní údaje', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale));
            $this->addParameterTranslations($parameterTranslations, t('Resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('1920×1080 (Full HD)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i, t('Hlavní údaje', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale));
            $this->addParameterTranslations($parameterTranslations, t('USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i, t('Způsob připojení', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale));
            $this->addParameterTranslations($parameterTranslations, t('HDMI', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i, t('Způsob připojení', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale));
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '2891.7');

        $this->setSellingFrom($productData, '16.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 300;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 300);
        $productData->productStoreData = $this->getProductStoreDataWithExposedOnStores(['store_1', 'store_3']);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_ELECTRONICS, CategoryDataFixture::CATEGORY_TV, CategoryDataFixture::CATEGORY_PC]);
        $productData->categoriesByDomainId[Domain::SECOND_DOMAIN_ID] = [];
        $productData->categoriesByDomainId[Domain::SECOND_DOMAIN_ID][] = $this->persistentReferenceFacade->getReference(CategoryDataFixture::CATEGORY_ELECTRONICS);
        $productData->categoriesByDomainId[Domain::SECOND_DOMAIN_ID][] = $this->persistentReferenceFacade->getReference(CategoryDataFixture::CATEGORY_TV);
        $productData->categoriesByDomainId[Domain::SECOND_DOMAIN_ID][] = $this->persistentReferenceFacade->getReference(CategoryDataFixture::CATEGORY_BOOKS);

        $this->setFlags($productData, [FlagDataFixture::FLAG_PRODUCT_ACTION]);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_SENCOR);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '9176508';
        $productData->partno = '32PFL4308H';
        $productData->ean = '8845781245929';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('32" Philips 32PFL4308', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('<p>Meet the latest generation of LED TVs Philips, which include the thin<strong>Smart TV running Android.</strong> Gets you a fine painting, innovative technologies, including image processing Ambilight and honest materials used in production. It features cutting-edge design structures with an eye for detail that fits into modern interiors. Like basis was inclined central pedestal that represents the geometric pun. On TV it will always look nice even when switched off. </p><p> <strong>The main advantages:</strong> </p><ul><li> Ambilight backlighting - projecting a glow on the wall </li><li> Sharp and smooth image </li><li> Android operating system with applications </li><li> Internet connection </li><li> Remote control with keyboard </li></ul><div data-act="block" contenteditable="false"><div data-act="edit" style="position: relative;" contenteditable="false"><h3> Superior picture </h3><p> <strong>Ambilight backlighting will forever change your perception of television.</strong> This is a unique technology that expands the display area outside the boundaries of the panel itself. 2 Sided glow lit from two sides of the screen to the surrounding walls and creates a breathtaking effect. Natural Motion technology ensures smooth and crisp moving images without blur by increasing the number of frames per second to double. It\'s handy for example when movie action scenes or sports broadcasts. </p><p> <strong>Micro Dimming Pro conveys a realistic visual experience day and night.</strong> It uses a light sensor and a special software to optimize the contrast of the screen on the basis of light conditions in the environment. The image is analyzed 6400 in different zones, thereby providing authentic and undisturbed experience. Sharp moving images with breathtaking contrast, detail and depth ensure Pixel Precise HD. </p></div></div><div data-act="block" contenteditable="false"><div data-act="edit" style="position: relative;" contenteditable="false"><h3> Android - richer television viewing experience </h3><p> Built-in WiFi module, and an Ethernet port expands the possibilities of television. <strong>Are you tired of watching okoukaných series?</strong> Then drop into online content. Waiting for you Archives TV stations, social networks, YouTube and other corners of the Internet via a web browser. Smart TV is based on Android OS 5.0 (Lollipop), has pre-installed applications and more can be found at Google Play. Use remote control with keyboard for easy typing text or even voice control. Advantage based on Android is also always up to date firmware. And all of you will be taken without waiting for a nimble run television is not only the old operating system, but also 2jádrový processor. </p></div></div><h3> Saving operation </h3><p> Manufacturer did not forget to decent connective equipment. It includes a quartet of HDMI connectors, one Scart and a trio of USB connectors for recording or playback of content by connecting USB disks. There is also support for HbbTV function or even suspend broadcasts EPG 8 days. Tuners are represented DVB-T / T2 / C and television unequivocal advantage of the economic operation. <strong>The TV comes to energy class A +,</strong> and for a very decent cuts only 45 kWh. </p><p><strong>Additional information:</strong> </p><p> <strong>Picture Enhancement:</strong> <br> Natural Motion <br> 500Hz Perfect Motion Rate <br> Pixel Precise HD <br> Micro Dimming Pro </p><p> <strong>Smart TV:</strong> <br> User interaction - multiroom Client and Server, SimplyShare, certified Wi-Fi Certified Miracast <br> Interactive television - HbbTV <br> Program - Suspension of television broadcasting, USB recording <br> Applications SmartTV - Online video store, open an Internet browser, Social TV service, Spotify, TV on Demand, YouTube <br> Easy Installation - Automatic detection equipment Philips Connection Wizard equipment, Network Setup Wizard, the Setup Wizard <br> Easy to use - Universal Smart Menu button, on-screen User Guide <br> Firmware upgradeable - Guide cars. update, Firmware upgradeable via USB, Firmware Update online <br> Screen Format Adjustments - Basic - Fill Screen, Fit to Screen, Advanced - Pan, Zoom, Stretch <br> Philips TV Remote - Applications Channels, Control, NowOnTV, TV guide, Video on demand <br> Remote control - Keyboard </p><p> <strong>Ambilight:</strong> <br> Versions of Ambilight - 2 pages <br> Ambilight - The adaptation of the color of walls, Lounge Mode, Game Mode, Ambilight + hue </p><p> <strong>Multimedia applications:</strong> <br> Video playback formats - Containers: AVI, MKV, H264 / MPEG-4 AVC, MPEG-1, MPEG-2, MPEG-4, WMV9 / VC1, HEVC <br> Format Support subtitles - .SMI, .SRT, .ssa, .SUB, .TXT, .ass <br> Play music formats - AAC, MP3, WAV, WMA (v2 to v9.2), PRO-WMA (v9 and v10) <br> Picture Playback Formats - JPEG, BMP, GIF, JPS, PNG, PNS, BMS, MIT </p><p> <strong style="font-family: Verdana, sans-serif, Arial; font-size: 13px;">Connectivity:</strong><span style="font-family: Verdana, sans-serif, Arial; font-size: 13px;">4 x HDMI</span> <span style="font-family: Verdana, sans-serif, Arial; font-size: 13px;">1 x Scart (RGB / CVBS)</span> <span style="font-family: Verdana, sans-serif, Arial; font-size: 13px;">3 x USB</span><span style="font-family: Verdana, sans-serif, Arial; font-size: 13px;">Wireless Dual Band Wi-Fi Direct, Integrated Wi-Fi 11n 2 × 2</span><span style="font-family: Verdana, sans-serif, Arial; font-size: 13px;">Other connections: Antenna IEC75, Common Interface Plus (CI +), Ethernet-LAN ??RJ-45, Digital audio output (optical), Audio L / R, Audio Input (DVI), Headphone out, Service connector, the connector Satellite</span><span style="font-family: Verdana, sans-serif, Arial; font-size: 13px;">HDMI features - Audio Return Channel</span><span style="font-family: Verdana, sans-serif, Arial; font-size: 13px;">EasyLink (HDMI-CEC) - Pass the remote control signal, System audio control, System standby, One touch play</span> </p><p> <strong>Supplied</strong> <br> Remote control <br> 2 x AAA Batteries <br> Table top stand <br> Power cable <br> Quick start guide use <br> Brochure Legal and safety information </p>', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Meet the latest generation of LED TVs Philips, which include the thin Smart TV running Android.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Screen size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('30"', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Technology', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('LED', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('1920×1080 (Full HD)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('HDMI', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('No', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '8173.5');

        $this->setSellingFrom($productData, '15.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 200;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 200);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_ELECTRONICS, CategoryDataFixture::CATEGORY_TV, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, [FlagDataFixture::FLAG_PRODUCT_ACTION]);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_PHILIPS);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '5965879P';
        $productData->partno = '47LA790V';
        $productData->ean = '8845781245928';
        $productData->weight = 4000;

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('47" LG 47LA790V (FHD)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('<h2>47 "LG 47LA790V</h2>> Luxury TV from the South Korean company LG bears <strong> 47LA790S </strong>. At first glance its <strong> beautiful design </strong> That pleases the eye of every lover of pure and precise shapes. Inside the TV is hidden except <strong> M13 dual-core processor </strong> number of extra features. I so belong to the energy class <strong> A + </strong> . Micro Pixel Control function is performed local dimming backlight and thus significantly <strong> reduces </strong> up to 64 W. This is so gigantic screen with really great. <br> <br> This TV is considerate to your eyes because of the image <strong> eliminates the annoying flicker </strong> caused by conventional 3D glasses, and you so you can enjoy long evenings of film <strong> without fatigue and eye pain </strong> . User-friendly environment allows <strong> adjust the 3D depth effect </strong> and yourself, you can choose whether you prefer comfort or deeper experience when watching 3D content. You could very well happen that while watching a football match <strong> flips the ball right into your living room </strong> Because special functions for image conversion<strong> improve any broadcasting the third dimension </strong> . <br> <br> Avid gamers will appreciate the <strong> Dual Play </strong> When television broadcasts two separate 2D images and each player using glasses to see their part to the full screen area. No more worry when a split screen. <strong> Intel WiDi </strong> (Wireless Display), respectively. wireless transmission of video and audio, allows quick and easy connection of TVs and laptops without cables or an Internet connection. It is very convenient and fast you\'ll love this feature.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('47 "LG 47LA790V Luxury TV from the South Korean company LG bears 47LA790S. At first glance its beautiful design that pleases the eye of every lover of pure and precise shapes.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Screen size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('47"', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Technology', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('LED', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('1920×1080 (Full HD)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('HDMI', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('No', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '17843');

        $this->setSellingFrom($productData, '14.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 800;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 800);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_ELECTRONICS, CategoryDataFixture::CATEGORY_TV, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_LG);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '5960453';
        $productData->partno = 'X-710BK';
        $productData->ean = '8845781245923';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->descriptions[$domain->getId()] = t('<h2>A4tech X710BK</h2>Playing computer mouse has five buttons and scrolovacím button for easier browsing documents or Internet pages. News is a key 3XFire that in one pressing regulating a triple clicks. The mouse has five options to set up the sensor sensitivity and in steps 400, 600, 1200, 1600 and 2000 DPI. The mouse has an ergonomic design, it connects via high-speed USB interface and is equipped with 16 Kb internal memory that is possible with the Oscar Mouse Editor to record scripts to control your favorite games. It is fully compatible with all modern operating systems including Windows Vista. <br><br><strong> Specifications: </strong><br><br><strong> Interface: </strong><br> USB 2.0 <br> Reduction in PS/2 <br><br><strong> OS Compatibility: </strong><br> Microsoft Windows 2000/XP/2003/Vista', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('A4tech X710BK Playing computer mouse has five buttons and scrolovacím button for easier browsing documents or Internet pages.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->name[$locale] = t('A4tech mouse X-710BK, OSCAR Game, 2000DPI, black,', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Gaming mouse', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i, t('Hlavní údaje myš', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale));
            $this->addParameterTranslations($parameterTranslations, t('Ergonomics', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Right-handed', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Supported OS', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Windows 2000/XP/Vista/7', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Number of buttons', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('5', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '263.6');

        $this->setSellingFrom($productData, '9.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = false;
        $productData->stockQuantity = 10;
        $this->setStocksQuantity($productData, 10);
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_ELECTRONICS, CategoryDataFixture::CATEGORY_PC, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_A4TECH);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '9510261';
        $productData->partno = 'ME440CS';
        $productData->ean = '8845781245956';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Apple iPhone 5S 64GB, gold', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Mobile phone - Apple A7 with 64-bit architecture, 4" Retina Touch display 1136x640, 32GB internal memory, WiFi 802.11a/b/g/n, Bluetooth 4.0, 8 Mpx camera with LED flash, GPS, Fingerprint sensor, iOS 7', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Mobile phone - Apple A7 with 64-bit architecture, 4" Retina Touch display 1136x640, 32GB internal memory, WiFi 802.11a/b/g/n, Bluetooth 4.0, 8 Mpx camera with LED flash, GPS, Fingerprint sensor, iOS 7', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Weight (kg)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), '0.12', $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Dimensions', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('123.8x58.6 mm', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Memory card support', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('No', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('RAM', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('1024 MB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Number of colors', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('16mil.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Processor frequency (GHz)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), '1.7', $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Number of processor cores', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('2', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Bluetooth', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('NFC', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('No', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('GPS module', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('No', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '19000');

        $this->setSellingFrom($productData, '11.2.2014');
        $this->setSellingTo($productData, null);
        $productData->usingStock = false;
        $productData->stockQuantity = 10;
        $this->setStocksQuantity($productData, 10);
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_PHONES, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, [FlagDataFixture::FLAG_PRODUCT_ACTION]);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_APPLE);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '5966179';
        $productData->partno = 'PTH300';
        $productData->ean = '8845781245939';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('BROTHER PT-H300', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('<p> Hand printing adhesive labels Brother especially suitable for easy marking in warehouses, offices and wherever it is required records or quickly usable labels. The printer boasts a very easy to use, ensuring a perfect look label width from 3.5 mm to 18 mm. You can create labels, barcodes, labels for folders, address and shipping labels, or labels on CD and DVD media. Everything can be created by using the keyboard directly to the printer manual. <br><br> The printer is in addition to ease of use and versatility also very fast. It can print up to 2 cm plate for one second. Very please direct thermal printing technology, which reduces the cost of toner and ink. The printer can be powered by AC adapter, rechargeable battery or alkaline batteries 6 x AA. <br><br><strong> Additional information: </strong><br><br><strong> Print technology: </strong><br> Thermal </p><p><strong> Width TZ tapes: </strong> 3.5/6/9/12/18 (mm) <br><strong> Display: </strong><br> 16 characters x 2 lines <br><strong> Print speed: </strong><br> 20 mm/s <br><strong> Odsřih: </strong><br> handmade <br><strong> Font style: </strong><br> 14 <br><br><strong> Symbols: </strong><br> 617<br><br><strong> Characters in mind: </strong><br> 2800 <br><br><strong> Other features: </strong><br> vertical printing, barcode printing <br><br><strong> Possible sources of supply: </strong><br> AC adapter AD-E001 - (not included) <br> rechargeable battery BA-E001 - (not included) <br> Alkaline Batteries 6 x AA - (not included) <br><strong> Package Contents: </strong><br> Printer PT-H300 <br> starting tape TZe-241 (18 mm black on white, length 4 m) <br> documentation </p>', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Hand printing adhesive labels Brother especially suitable for easy marking in warehouses, offices and wherever it is required records or quickly usable labels. The printer boasts a very easy to us', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Display', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('LCD', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Parallel port', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('No', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '1295');

        $this->setSellingFrom($productData, '25.1.2014');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 30;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 10);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_ELECTRONICS, CategoryDataFixture::CATEGORY_PC]);

        $productData->sellingDenied = true;
        $this->setBrand($productData, BrandDataFixture::BRAND_BROTHER);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '532564';
        $productData->partno = '6758B001';
        $productData->ean = '8845781245914';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Canon EH-22L', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('<h2>Canon EH-22L</h2>High quality, elegant, soft, and yet reliably protecting brand case for your Canon digital camera CANON EOS 650D or 700D. Provides protection while traveling from dust, scratches and other negative influences. You also have the camera ready at hand. It fits into the unit with lens 18-55 mm.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Canon EH-22L. High quality, elegant, soft, and yet reliably protecting brand case for your Canon digital camera CANON EOS 650D or 700D. ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Color', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('black', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '1110.54896');

        $this->setSellingFrom($productData, '3.8.1999');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 100;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 10);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_PHOTO, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_CANON);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '5964034';
        $productData->partno = '8596B047';
        $productData->ean = '8845781245912';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Canon EOS 700D', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Canon EOS 700D + EF-S 18-55 mm + 75-300 mm DC III DC III Quality digital camera with CMOS sensor with a resolution of 18 megapixels, which is to take the top photo in a professional style. Innovative DIGIC 5 image processing delivers powerful in any situation. A high sensitivity range up to ISO 12800 lets you capture great images even in dim light, even without a flash. Automatic smart scene mode analyzes the scene and automatically selects the best settings for the camera. View images, focus, or even pictures can be taken using a 3 "touch screen TFT Clear View II with a whopping resolution of 1.04 million pixels and a vari-angle. With this camera out of you in a moment become a professional cameraman. It makes it possible to shoot stunning movies in Full HD 1080p. Hybrid AF technology enables continuous focus during movie shooting and using the built-in microphone ozvučíme your images are high quality stereo sound. Autofocus system comprising nine cross-type AF points to capture fast-moving objects without any blurring. Thanks to continuous shooting at up to 5 frames per second, you\'ll never miss a crucial moment for getting the best picture possible.The HDR Backlight Control mode, the camera takes three different exposures and combines them into one, in order to preserve details in shadows and areas with high brightness. In Night Scene mode, the camera takes the hand of multiple images at high shutter speeds and combining them together and thus prevents blurring. Captured images and videos simply adding it to an SD Memory Card, SDHC and SDXC, thanks to the integrated connector is miniHDMI you can conveniently viewed on your LCD or plasma TV. The camera can buy a wide range of lenses, flashes and accessories that are compatible with the EOS system.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Canon EOS 700D + EF-S 18-55 mm + 75-300 mm DC III DC III Quality digital camera with CMOS sensor with a resolution of 18 megapixels, which is to take the top photo in a professional style. Innovative DIGIC 5 image processing delivers powerful in any situation. A high sensitivity range up to ISO 12800 lets you capture great images even in dim light', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Camera type', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('SLR', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('9 Mpix', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Power supply', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('battery', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Viewfinder type', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('optical', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Sensitivity (ISO)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('1800', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Display size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('3“', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Weight', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('580 g', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_ZERO);
        $this->setPriceForAllPricingGroups($productData, '24990');

        $this->setSellingFrom($productData, '3.2.2013');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 100;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 100);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_PHOTO, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_CANON);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '9184535';
        $productData->partno = '8331B006';
        $productData->ean = '8845781245938';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Canon MG3550', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('<h2>Canon PIXMA MG3350 black</h2><p>Features of modern and elegantly prepared MFPs<strong> s new wireless capabilities</strong>. Function <strong>automatic two-sided printing</strong> printing on both sides, which saves paper while producing professional looking documents. The printer uses<strong> ChromaLife100 ink system </strong>with four colors of ink hidden <strong>two print cartridges</strong>That provide easy user service and stable print quality throughout the life. You reach for XL FINE cartridges provide printing multiple pages significantly between individual ink replacement. This is ideal if you often print.<br><br>Do smart device application download <strong>Canon PIXMA Printing Solutions</strong> a straight print or scan. In addition, you can check the printer status and ink levels. They also supported services <strong>Apple AirPrint</strong> and access to the Internet and <strong>Google Cloud Print</strong>. Software <strong>My Image Garden</strong> has a solution for organizing and printing photos, scanning, and access to online services. Due to advanced features such as face detection, you will always find exactly what you\'re looking for.<br><br><strong>Additional information:</strong><br><br><strong>Print:</strong><br>Technology: 4-ink (in 2 packs) ChromaLife100 system, the head of FINE (2 pl)<br>Borderless printing: A4, Letter, 20 x 25 cm, 13 x 18 cm, 10 x 15 cm<br>Automatic two-sided printing: A4, A5, B5, Letter<br>Printing from Application PIXMA Printing Solutions, Google Cloud Print, Apple AirPrint</p>', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Features of modern and elegantly prepared MFPs with new wireless capabilities. Function automatic two-sided printing', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Print technology', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('inkjet', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Maximum size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('A3', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('LCD', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Print resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('4800x1200', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Color printing', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('WiFi', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Dimensions', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('449x304x152 mm', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Weight', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('5.4 kg', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '1314.1');

        $this->setSellingFrom($productData, '24.1.2014');
        $this->setSellingTo($productData, null);
        $productData->usingStock = false;
        $productData->stockQuantity = 10;
        $this->setStocksQuantity($productData, 10);
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_PRINTERS, CategoryDataFixture::CATEGORY_PC]);
        $productData->categoriesByDomainId[Domain::SECOND_DOMAIN_ID] = [];
        $productData->categoriesByDomainId[Domain::SECOND_DOMAIN_ID][] = $this->persistentReferenceFacade->getReference(CategoryDataFixture::CATEGORY_PHOTO);

        $this->setFlags($productData, [FlagDataFixture::FLAG_PRODUCT_NEW, FlagDataFixture::FLAG_PRODUCT_ACTION]);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_CANON);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '9184449';
        $productData->partno = '8328B006';
        $productData->ean = '8845781245936';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Canon PIXMA MG2450', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('<h2>Canon PIXMA MG2450</h2><p>Stylish and affordable, accessible multifunction devices for the home. Easy <strong> printing, scanning and copying </strong> in one device will take much less space and you\'ll save money than buying individual components. The printing machine uses an innovative print <strong> FINE technology </strong> Which guarantee excellent print quality. The printer has a system of four ink colors hidden in two ink cartridges, which provide easy user service and stable print quality throughout the life. <strong> You can reach the XL cartridges </strong> FINE, which provide significantly greater number of print pages between ink replacement. This is ideal if you are printing large volumes.<br><br>Software <strong> My Image Garden </strong> will reveal the full range of functions PIXMA printers. It offers solutions for the layout and printing photos, scanning, and access to online services. Due to advanced features such as face detection, it will scan all the pictures on your computer (even those long forgotten), and compile them into great designs to print. Service <strong> CREATIVE PARK PREMIUM</strong> you can download and print photos, images and artwork from internationally recognized photographers and artists. Create greeting cards, calendars or stunning 3D paper products, such as the space shuttle Endeavour.</p>', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t(' Easy printing, scanning and copying in one device will take much less space and you\'ll save money than buying individual components. The printing machine uses an innovative print FINE technology  Which guarantee excellent print quality.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Print technology', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('inkjet', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Maximum size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('A3', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('LCD', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Print resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('4800x1200', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Color printing', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('WiFi', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Weight', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('3.5 kg', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Dimensions', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('449x304x152 mm', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '818');

        $this->setSellingFrom($productData, '22.1.2014');
        $this->setSellingTo($productData, null);
        $productData->usingStock = false;
        $productData->stockQuantity = 0;
        $this->setStocksQuantity($productData, 0);
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_PRINTERS, CategoryDataFixture::CATEGORY_PC]);
        $productData->categoriesByDomainId[Domain::SECOND_DOMAIN_ID] = [];
        $productData->categoriesByDomainId[Domain::SECOND_DOMAIN_ID][] = $this->persistentReferenceFacade->getReference(CategoryDataFixture::CATEGORY_PHOTO);

        $this->setFlags($productData, [FlagDataFixture::FLAG_PRODUCT_NEW]);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_CANON);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '5960105';
        $productData->partno = '43266';
        $productData->ean = '8845781245920';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('CD-R VERBATIM 210MB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('media 210MB, 24min., writing speed 24x, slim box', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('media 210MB, 24min., writing speed 24x, slim box', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Media type', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Mini CD-R 8 cm', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Color', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('white', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Capacity', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('210 MB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('WiFi', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '5');

        $this->setSellingFrom($productData, '6.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 100;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 10);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_VERBATIM);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '176948';
        $productData->partno = 'DNS-327L';
        $productData->ean = '8845781245918';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('D-Link', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('<h2>D-Link DGS-105/E</h2>The switch provides a cost-effective way to create a small network or extend existing ones. Connect not only computers, but also a number of network devices such as IP cameras, network printers and more. The switch is equipped with five Gigabit Ethernet ports with auto-sensing speeds, so you shall always have the best performance. To communicate without delay and smooth video streaming has integrated <strong> QoS optimization </strong> . <br><br> Switch functions quickly and easily without complicated setting. But <strong> consistently low power </strong> detect used ports, and if stopping traffic enters Sleep mode. <strong> Rugged metal body is sufficient for cooling without fan </strong> . Together with its small size you can find a place almost anywhere. Also available is a set of <strong> wall mounting </strong> and it can also protect against theft Kensington.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('The switch provides a cost-effective way to create a small network or extend existing ones. Connect not only computers, but also a number of network devices such as IP cameras, network printers and more.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '2891.7');

        $this->setSellingFrom($productData, '4.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = false;
        $productData->stockQuantity = 0;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_DLINK);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '5990008';
        $productData->partno = '65480';
        $productData->ean = '8845781245945';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Defender 2.0 SPK-480', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('<h2>Defender SPK-480</h2><strong>Defender SPK 480</strong>, two portable and practical&nbsp;<strong>2" broadband&nbsp;</strong>speakers. They have&nbsp;<strong>4 watt output&nbsp;</strong>and are powered by USB. They\'re made of durable plastic and their&nbsp;compact size is&nbsp;<strong>portable&nbsp;</strong>and easy to fit into any bag or backpack. The speakers connect to notebooks or portable music players via the built-in 3.5 mm jack. Each speaker features its own volume control, and they can be placed near monitors without disturbing image quality due to their magnetic shielding. The silver and black&nbsp;design is simple and modern.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Defender SPK-480, easy to fit into any bag or backpack', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Dimensions', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('80x70x70 mm', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Overall performance', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('4 W', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '98.3');

        $this->setSellingFrom($productData, '31.1.2014');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 220;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 10);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_TV, CategoryDataFixture::CATEGORY_PC]);
        $productData->categoriesByDomainId[Domain::SECOND_DOMAIN_ID] = [];
        $productData->categoriesByDomainId[Domain::SECOND_DOMAIN_ID][] = $this->persistentReferenceFacade->getReference(CategoryDataFixture::CATEGORY_TV);

        $this->setFlags($productData, [FlagDataFixture::FLAG_PRODUCT_NEW]);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_DEFENDER);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '9771339';
        $productData->partno = 'ECAM 44.660 B';
        $productData->ean = '8845781245934';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('DeLonghi ECAM 44.660 B Eletta Plus', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('<h2>DéLonghi ECAM 44.660.B</h2><p>Start the morning with your favorite drink with the DeLonghi ECAM 44.660.B Eletta automatic coffee maker.&nbsp; A built in special hot steam/water jet automatically prepares a thick milk froth for cappuccino and tea. Drip coffee lovers can also press their own coffee.</p><h3>Magic Milk Menu button</h3><p><br>A special function is the Milk Menu button, which gives you a choice of milk-based beverages. In addition to the classic cappuccino, latte macchiato, or Latte, you can prepare rare drinks such as Espresso macchiato and a flat white. For a quick and easy treat, you can prepare hot cocoa or a cup of frothed milk, and the size of the beverage can be adjusted based on the cup.&nbsp;&nbsp;</p>', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('DéLonghi ECAM 44.660.B. Start the morning with your favorite drink with the DeLonghi ECAM 44.660.B Eletta automatic coffee maker', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Pressure', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('15 bar', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Water reservoir capacity', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('2 l', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Milk reservoir capacity', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('600 ml', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Magazine capacity for beans', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('400 g', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '19743.6');

        $this->setSellingFrom($productData, '20.1.2014');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 140;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 10);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_COFFEE, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, [FlagDataFixture::FLAG_PRODUCT_NEW]);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_DELONGHI);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '5962199';
        $productData->partno = '';
        $productData->ean = '8845781245958';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Pot holder, black', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('This pot holder is used to hold pots. No more burnt kitchen tables!', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('This pot holder is used to hold pots. No more burnt kitchen tables!', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '3');

        $this->setSellingFrom($productData, '13.2.2014');
        $this->setSellingTo($productData, null);
        $productData->usingStock = false;
        $productData->stockQuantity = 0;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_BOOKS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, [FlagDataFixture::FLAG_PRODUCT_NEW]);

        $productData->sellingDenied = true;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '5960139';
        $productData->partno = '31011039100';
        $productData->ean = '8845781245924';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Genius NetScroll 310 silver', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Simple Genius Mouse NetScroll 310 offers the most widely used functional elements of a basic ergonomics. It has an optical sensor with a resolution of 800 DPI, which can handle work on a large number of standard surfaces. There are two classic buttons and the third with the paging function wheel. Genius NetScroll 310 is equipped with modern USB interface and finds use in conserving ambidextrous or in an office environment. <br><strong> Specifications: </strong><br><strong> Supported OS: </strong><br> Microsoft Windows 7, Vista, XP, 2003, 2000, 98SE <br> Mac OS 8.6 or later ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Simple Genius Mouse NetScroll 310 offers the most widely used functional elements of a basic ergonomics. It has an optical sensor with a resolution of 800 DPI, which can handle work on a large number of standard surfaces. ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Technology', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Optical', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Interface', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Wired', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '90.1');

        $this->setSellingFrom($productData, '10.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 300;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 10);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, [FlagDataFixture::FLAG_PRODUCT_MADEIN_CZ]);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_GENIUS);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '5960550';
        $productData->partno = '31730946108';
        $productData->ean = '8845781245946';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Genius repro SP-M120 black', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('<h2>GENIUS SP-M120 black</h2><p align="justify"> Sleek and compact stereo speakers in combination of black and metallic surface. Speakers provide basic computer sound system with an output of 2 W RMS. On the front is virtually placed in a large volume control, but not forgotten even the popular headphone jack. <br><strong> Specifications: <br> Performance: <br></strong> 2 x 1 W RMS <br><strong> Frequency Range: </strong> 100 Hz - 20KHz <br><strong> Signal/noise ratio: </strong> 75 db <br><strong> Dimensions: </strong> 50 x 90 mm </p>', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Sleek and compact stereo speakers in combination of black and metallic surface.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('System type', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('2.0', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Active/Passive', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Active', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Overall performance', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('2W', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '164.5');

        $this->setSellingFrom($productData, '1.2.2014');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 220;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 10);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_TV, CategoryDataFixture::CATEGORY_PC]);
        $productData->categoriesByDomainId[Domain::SECOND_DOMAIN_ID] = [];
        $productData->categoriesByDomainId[Domain::SECOND_DOMAIN_ID][] = $this->persistentReferenceFacade->getReference(CategoryDataFixture::CATEGORY_TV);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_GENIUS);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '5960549';
        $productData->partno = '31340021118';
        $productData->ean = '8845781245925';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Genius SlimStar i820', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('<h2>Genius SlimStar i222 CZ+SK Black</h2>GENIUS LuxeMate I222 is a stylish black keyboard, which at first glance invites you to use your Apple design. It will decorate your desk at home or in the office. In addition to the standard layout will delight and a large variety of function keys that enhance control of the internet, email and also a multimedia player. Low profile you use for long periods still feel comfortable. The computer keyboard GENIUS LuxeMate I222 connects via USB interface.<br><br><strong>Specifications:<br><br>Type: </strong>conduction<br><strong>Layout: </strong>Czech<br><strong>Interface: </strong><br>USB<br><strong>Design:</strong><br>Apple design', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('GENIUS LuxeMate I222 is a stylish black keyboard, which at first glance invites you to use your Apple design.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Localization', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Czech', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Element arrangement', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('classic', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Enter', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('one-slotted', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '437.2');

        $this->setSellingFrom($productData, '11.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 100;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 10);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_GENIUS);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '8456655';
        $productData->partno = '31730992101';
        $productData->ean = '8845781245947';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Genius SP-U150X black-green', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('<h2>Genius SP-HF150 Black</h2>Compact Speakers Genius SP-HF150 are the perfect accessory for portable computers. It is powered only by the USB port. Yet they can generate high power 4 W RMS. The sound of the smaller 2.5 "drives is airy, yet does not lack sufficient bass fundament. All of the elegant veneer finish imitating a hidden volume control. <br><strong><br> Specifications: </strong><br><br><strong> Sound characteristics: </strong><br> Power: 2 x 2 W RMS <br> Drivers: 2.5 "(širokpásmové) <br> Frequency range: 200 to 20 000 Hz <br> Signal-to-Noise Ratio: 70 db <br><br><strong> Connectors: </strong><br> 3.5 mm audio <br> USB for power', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Compact Speakers Genius SP-HF150 are the perfect accessory for portable computers.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('System type', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('2.0', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Active/Passive', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Active', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Overall performance', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('2W', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '180');

        $this->setSellingFrom($productData, '2.2.2014');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 220;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 10);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_TV, CategoryDataFixture::CATEGORY_PC]);
        $productData->categoriesByDomainId[Domain::SECOND_DOMAIN_ID] = [];
        $productData->categoriesByDomainId[Domain::SECOND_DOMAIN_ID][] = $this->persistentReferenceFacade->getReference(CategoryDataFixture::CATEGORY_TV);

        $this->setFlags($productData, [FlagDataFixture::FLAG_PRODUCT_NEW, FlagDataFixture::FLAG_PRODUCT_MADEIN_CZ]);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_GENIUS);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '5960598';
        $productData->partno = 'GK-KM7580';
        $productData->ean = '8845781245926';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('GIGABYTE KM7580 CZ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('<h2>GIGABYTE GK-KM7580 Black</h2>Quality set keyboard and optical mouse from Gigabyte. Both components are color-matched to the sleek glossy black color. The keyboard has 15 multimedia keys for quick access to the Internet, email and other applications. The multimedia keys also used for various tasks (volume control, ...). Optical mouse has a sensor with a resolution of 500 to 1000 DPI resolution can be switched. Mouse fits both the left and the right hand. Wireless communication provides a miniature USB receiver for keyboard and mouse. This set will contrast nicely on any desk. <br><br><strong> Specifications: </strong> <br><br><strong> Multimedia keys: </strong><br> 15 hotkeys <br><br><strong> Interface: </strong><br> USB receiver <br><br><strong> Communication: </strong><br> wireless, 2.4 GHz band <br><br><strong> Power: </strong><br> Keyboard: 2x AAA <br> Mouse: 2x AAA', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Quality set keyboard and optical mouse from Gigabyte. Both components are color-matched to the sleek glossy black color. ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Localization', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Czech', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Element arrangement', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('classic', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Enter', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('one-slotted', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '429.8');

        $this->setSellingFrom($productData, '12.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 80;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 10);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = true;
        $this->setBrand($productData, BrandDataFixture::BRAND_GIGABYTE);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '718253';
        $productData->partno = 'B2L57C';
        $productData->ean = '8845781245937';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('HP Deskjet Ink Advantage 1515 (B2L57C)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('<p>Hewlett-Packard was founded in the difficult times of the Great Depression. The founders were a pair of friends whose name the company still proudly bears. They started their business in an unobtrusive garage near the city of Palo Alto. It is now a national monument. HP’s success lay not in copying existing products, but in the ability and courage to come up with something new.</p><p>The first commercial triumph was an oscillator that surpassed all competition in quality, yet sold at a quarter of the price. In 1968, HP released their first desktop computer - a desktop calculator. The company currently manufactures products primarily related to computer technology - computers and laptops, printers, scanners, digital cameras, servers, and last but not least, calculators.</p><p>Unless otherwise indicated in the product description, packaging does not contain a USB interface cable.</p>', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Hewlett-Packard was founded in the difficult times of the Great Depression. The founders were a pair of friends whose name the company still proudly bears. ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Print technology', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('inkjet', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Maximum size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('A3', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('LCD', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('No', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Print resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('2400x600', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Color printing', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('WiFi', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Weight', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('3.5 kg', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Dimensions', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('449x304x152 mm', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '1238');

        $this->setSellingFrom($productData, '23.1.2014');
        $this->setSellingTo($productData, null);
        $productData->usingStock = false;
        $productData->stockQuantity = 0;
        $this->setStocksQuantity($productData, 0);
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_PRINTERS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, [FlagDataFixture::FLAG_PRODUCT_NEW]);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_HP);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '9511043';
        $productData->partno = '99HZS017';
        $productData->ean = '8845781245955';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('HTC Desire 816 White', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Photographer\'s dream. Main camera with 13-megapixel front camera and a 5 megapixel guarantee photos in high definition on each side. Thanks to the built-in tools for editing and sharing can capture moving images in HD and combine the results to show from multiple angles. Incredible design, giant 5,5palcový display with HD resolution, two front stereo speakers, customized channels with real-time information and a quad-core processor - all combine to form one of the best smartphones in the world.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Photographer\'s dream. Main camera with 13-megapixel front camera and a 5 megapixel guarantee photos in high definition on each side. Thanks to the built-in tools for editing and sharing can capture moving images in HD and combine the results to show from multiple angles.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Display Size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('5.5"', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Display type', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Super LCD', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Resolution of rear camera', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('13 Mpx', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '8421.5');

        $this->setSellingFrom($productData, '10.2.2014');
        $this->setSellingTo($productData, null);
        $productData->usingStock = false;
        $productData->stockQuantity = 0;
        $this->setStocksQuantity($productData, 0);
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_PHONES, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, [FlagDataFixture::FLAG_PRODUCT_NEW]);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_HTC);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '9772572';
        $productData->partno = 'JURA Impressa J9 TFT Carbon';
        $productData->ean = '8845781245933';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('JURA Impressa J9 TFT Carbon', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('<h2>JURA IMPRESSA Z9 One Touch TFT Pianoblack</h2><p> Luxury automatic coffee machine Jura Impressa Z9 One Touch packed with Swiss precision and elegance, which sets new standards. This machine brings you jeich on several levels, such as coffee quality, easy to use and attractive design. The purpose is always the most advanced technology to achieve real, authentic results. The powerful device has intuitive controls and except large 3.5 "TFT color display. Together with rotary dial allows you to choose between 11 specialties and goes to prepare any coffee you can think of. With the coffee a la carte, allowing you to quickly change settings according to your needs, your options are almost limitless. </p><p> For an authentic experience and to ensure optimal conditions for coffee are two Thermoblocks. The first coffee and hot water, the second is intended to couple. With every cup of coffee is also possible to set the correct brewing temperature, amount of water and grind. Mill technology + Aroma is quiet and up to twice faster than previously used. Grains of coffee while grinding so much heat and so there is no negative influence of taste of coffee. </p><p> Foam crown in drinks Latte macchiato, cappuccino and other specialty are the world extremely popular. Automatic coffee machine in the model IMPRESSA Z9 One Touch is a distinctive design with a combination of pure luxury and sophistication. But technology also has a soft foam that each of trendy coffee specialties will adjust fluffy crown. S cappuccino outlets, which can be adjusted up to 153 mm, there is no problem to use a glass or a latte macchiato. Formed with Swiss precision and elegance with which you prepare the perfect espresso, cappuccino or caffe latte. </p><p> You will enjoy a very low power consumption. If the espresso is separated from the mains power switch, patented innovation JURY Zero Energy Switch (zero energy consumption) will remove any energy at all. Before turning off the espresso is automatically purged and cleansed, which provides him with long life. The unique geometry with finer teeth grinding and new technology supports the lower noise machine when grinding coffee. </p>', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Luxury automatic coffee machine Jura Impressa Z9 One Touch packed with Swiss precision and elegance, which sets new standards. This machine brings you jeich on several levels, such as coffee quality, easy to use and attractive design.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Pressure', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('15 bar', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Water reservoir capacity', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('2 l', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Milk reservoir capacity', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('600 ml', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Magazine capacity for beans', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('400 g', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '49587.5');

        $this->setSellingFrom($productData, '19.1.2014');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 111;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 10);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_COFFEE, CategoryDataFixture::CATEGORY_PC]);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_JURA);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '2565636';
        $productData->partno = '0';
        $productData->ean = '8845781245919';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Kabel HDMI A - HDMI A M/M 2m gold-plated connectors High Speed HD', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('A cable HDMI - HDMI AM / M 2 m gold-plated connector High Speed HDMI Cable with Ethernet 1.4 support 1080p FULL HD', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('A cable HDMI - HDMI AM / M 2 m gold-plated connector High Speed HDMI Cable with Ethernet 1.4 support 1080p FULL HD', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '98');

        $this->setSellingFrom($productData, '5.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 150;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 10);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '9890478';
        $productData->partno = '9788025117125';
        $productData->ean = '8845781245941';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Book 55 best programs for burning CDs and DVDs', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('<p><strong>CD/DVD/Blu-ray/HD-DVD</strong>is an affordable program for burning CD/DVD/Blu-ray/HD-DVD media. Intuitive operation with its easy to handle even a novice in this field. Another big advantage is the use of technology burning "on the fly", which is spared not only your time but also the capacity of the disk, because the process does not create any storage (temporary) files on your hard disk.<br><br>Worth mentioning is also the fact that<strong> for its operation baking does not use any components of Windows</strong> or other manufacturers. Its functionality is not needed any external libraries or DLL. This makes the software provides high stability, even if your system is corrupted Windows. <strong>This gives you the ability to backup before reinstalling Broken System</strong>. The program is completely in the Czech language.</p>', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Intuitive operation with its easy to handle even a novice in this field. Another big advantage is the use of technology burning "on the fly", which is spared not only your time but also the capacity of the disk, because the process does not create any storage (temporary) files on your hard disk.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Pages count', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('55', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Weight', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('50 g', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Cover', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('hardcover', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '37');

        $this->setSellingFrom($productData, '27.1.2014');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 500;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 10);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_BOOKS, CategoryDataFixture::CATEGORY_PC]);
        $productData->categoriesByDomainId[Domain::SECOND_DOMAIN_ID] = [];
        $productData->categoriesByDomainId[Domain::SECOND_DOMAIN_ID][] = $this->persistentReferenceFacade->getReference(CategoryDataFixture::CATEGORY_PC);
        $productData->categoriesByDomainId[Domain::SECOND_DOMAIN_ID][] = $this->persistentReferenceFacade->getReference(CategoryDataFixture::CATEGORY_BOOKS);

        $this->setFlags($productData, [FlagDataFixture::FLAG_PRODUCT_NEW, FlagDataFixture::FLAG_PRODUCT_MADEIN_CZ]);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '9831504';
        $productData->partno = '9788072267361';
        $productData->ean = '8845781245942';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Book scoring system and traffic regulations', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('New driving rules and tips just for you!', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('New driving rules and tips just for you!', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Pages count', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('50', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Weight', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('150 g', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Cover', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('paper', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '37');

        $this->setSellingFrom($productData, '28.1.2014');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 100;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 10);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_BOOKS, CategoryDataFixture::CATEGORY_PC]);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '9890274';
        $productData->partno = '9788025107805';
        $productData->ean = '8845781245943';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Book Computer for Dummies Digital Photography II', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Discover the secret of people with us.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Discover the secret of people with us.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Pages count', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('250', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Weight', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('250 g', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Cover', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('paper', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '44');

        $this->setSellingFrom($productData, '29.1.2014');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 450;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 10);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_BOOKS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, [FlagDataFixture::FLAG_PRODUCT_NEW]);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '9197872';
        $productData->partno = '9788000026336';
        $productData->ean = '8845781245944';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Book of traditional Czech fairy tales', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Collection of classical Czech fairy tales.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Collection of classical Czech fairy tales.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Pages count', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('48', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Weight', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('50 g', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Cover', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('paper', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '56');

        $this->setSellingFrom($productData, '30.1.2014');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 650;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 10);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_BOOKS, CategoryDataFixture::CATEGORY_PC]);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '9198277';
        $productData->partno = '9788025133484';
        $productData->ean = '8845781245940';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Book of procedures for dealing with traffic accidents', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Have you ever experienced an accident and didn\'t know how to react? Or are you going to? This book is just for you!', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Have you ever experienced an accident and didn\'t know how to react? Or are you going to? This book is just for you!', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '28');

        $this->setSellingFrom($productData, '26.1.2014');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 200;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 10);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_BOOKS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, [FlagDataFixture::FLAG_PRODUCT_NEW]);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '8981018';
        $productData->partno = '8808992086758';
        $productData->ean = '8845781245951';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('LG E410 Optimus L1 II White', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Mobile phone 4.7" 720x1280, procesor 1,5GHz, internal memory 16GB, camera 8mpx, GPS, WiFi, Bluetooth, 3G, FM, microSD, micro USB, Android 4.0', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Mobile phone 4.7" 720x1280, procesor 1,5GHz, internal memory 16GB, camera 8mpx, GPS, WiFi, Bluetooth, 3G, FM, microSD, micro USB, Android 4.0', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Weight (kg)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), '0.15', $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Dimensions', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('123.8x58.6 mm', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Memory card support', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('No', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('RAM', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('1024 MB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Number of colors', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('16mil.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Processor frequency (GHz)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), '1.8', $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Number of processor cores', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('2', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Bluetooth', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('NFC', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('No', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('GPS module', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('No', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '1644');

        $this->setSellingFrom($productData, '6.2.2014');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 440;
        $this->setStocksQuantity($productData, 440);
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_PHONES, CategoryDataFixture::CATEGORY_PC]);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_LG);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '9680315';
        $productData->partno = '980-000010';
        $productData->ean = '8845781245949';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Logitech S120 black', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('<h2>Logitech S-120 Speaker System</h2> Modern stereo speakers in a stylish black design, with an output of 2.3 W, suitable only for desktops and laptops, but also for CD or MP3 players, and other devices. They are connected via standard stereo jack 3,5 mm. The speakers have a frequency response of 50 Hz to 20 kHz. Easily accessible controls are located on the side of the right speaker with headphone outlet. <br><br><b> Specifications: </b><br><strong><br> Performance: <br></strong> 2.3 W (2 x 1.15 W RMS) <br><br><strong> Frequency response: <br></strong> 50 Hz to 20 kHz <br>', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Modern stereo speakers in a stylish black design, with an output of 2.3 W, suitable only for desktops and laptops, but also for CD or MP3 players.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('System type', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('2.0', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Active/Passive', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Active', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Overall performance', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('2W', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '263.6');

        $this->setSellingFrom($productData, '4.2.2014');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 200;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 10);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_TV, CategoryDataFixture::CATEGORY_PC]);
        $productData->categoriesByDomainId[Domain::SECOND_DOMAIN_ID] = [];
        $productData->categoriesByDomainId[Domain::SECOND_DOMAIN_ID][] = $this->persistentReferenceFacade->getReference(CategoryDataFixture::CATEGORY_TV);

        $this->setFlags($productData, [FlagDataFixture::FLAG_PRODUCT_NEW]);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_LOGITECH);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '5960072';
        $productData->partno = 'P58-00059';
        $productData->ean = '8845781245922';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Microsoft Basic Optical Mouse, black', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Practical and stylish optical mouse ideal for everyday office use.With its ergonomic shape fits perfect and is suitable for left and for right-handers.Using an optical sensor with high sensitivity is move the cursor on the screen fast and smooth.Benefits are also programmable buttons to help with easy access to the programs and documents you use most often.The computer mouse simply connect using a cable with a USB connector and the support Plug and Play, no need to install drivers and you can immediately get to work. ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Practical and stylish optical mouse ideal for everyday office use.With its ergonomic shape fits perfect and is suitable for left and for right-handers.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Gaming mouse', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Ergonomics', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Right-handed', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Supported OS', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Windows 2000/XP/Vista/7', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Number of buttons', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('5', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '231.5');

        $this->setSellingFrom($productData, '8.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 400;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 10);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = true;
        $this->setBrand($productData, BrandDataFixture::BRAND_MICROSOFT);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '5961383';
        $productData->partno = '6932011296018';
        $productData->ean = '8845781245959';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Million-euro toilet paper', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Toilet paper with Euro pictures. Even you can feel rich now!', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Toilet paper with Euro pictures. Even you can feel rich now!', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '10');

        $this->setSellingFrom($productData, '14.2.2014');
        $this->setSellingTo($productData, null);
        $productData->usingStock = false;
        $productData->stockQuantity = 0;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 1);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_BOOKS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, [FlagDataFixture::FLAG_PRODUCT_ACTION, FlagDataFixture::FLAG_PRODUCT_SALE]);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '5961384';
        $productData->partno = '';
        $productData->ean = '8845781245916';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('MIO Cyclo 100, bicycle computer, 1,8"', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Cyklocomputer – cyklonavigation with preset maps, color display 3", training programmes, WiFi', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Cyklocomputer – cyklonavigation with preset maps, color display 3", training programmes, WiFi', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '0');

        $this->setSellingFrom($productData, '2.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 200;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 10);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '8980681';
        $productData->partno = '1318206';
        $productData->ean = '8845781245935';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('OKI MC861cdxn+ (01318206)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Toner for MC861/ 851, 7000 pages', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Toner for MC861/ 851, 7000 pages', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Print technology', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('inkjet', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Maximum size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('A3', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('LCD', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Print resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('4800x1200', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Color printing', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('WiFi', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('No', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Dimensions', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('426x306x145 mm', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Weight', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('3.5 kg', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '67771.9');

        $this->setSellingFrom($productData, '21.1.2014');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 200;
        $this->setStocksQuantity($productData, 200);
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_PRINTERS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, [FlagDataFixture::FLAG_PRODUCT_NEW, FlagDataFixture::FLAG_PRODUCT_ACTION]);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '539888';
        $productData->partno = 'V4571510E000';
        $productData->ean = '8845781245915';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('OLYMPUS ME-34 Compact directional microphone suitable for recording lectures', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Digital Voice Reco.rder Olympus VN-733PC is profiled primarily intuitive operation and long battery life . Its user offers 4 gigabytes internal memory and a new slot for microSD cards. O capacity certainly will not be a shortage in power saving mode, LP handle record up to 1600 hours of recording', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Digital Voice Reco.rder Olympus VN-733PC is profiled primarily intuitive operation and long battery lif.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '1268.7');

        $this->setSellingFrom($productData, '1.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 800;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 10);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_TV, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = true;
        $this->setBrand($productData, BrandDataFixture::BRAND_OLYMPUS);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '5402881';
        $productData->partno = 'V108060WE000';
        $productData->ean = '8845781245910';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('OLYMPUS VH-520', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Digital Camera CMOS 16 megapixel, 24x zoom, 3.0 "LCD, Li-Ion, FullHD video, histogram, HDR, optical stabilizer, SD/SDHC/SDXC, face detection, USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Digital Camera CMOS 16 megapixel, 24x zoom, 3.0 "LCD, Li-Ion, FullHD video, histogram, HDR, optical stabilizer, SD/SDHC/SDXC, face detection, USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Camera type', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('SLR', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('18 Mpix', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Power supply', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('battery', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Viewfinder type', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('optical', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Sensitivity (ISO)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('12800', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Display size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('5“', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Weight', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('580 kg', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '2783');

        $this->setSellingFrom($productData, '1.1.2013');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 200;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 10);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_PHOTO, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_OLYMPUS);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '5965907';
        $productData->partno = 'DMC FT5EP-K';
        $productData->ean = '8845781245911';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('PANASONIC DMC FT5EP', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Digital Camera - Waterproof, shockproof, freezeproof, 16.1 Mpx CCD, 4x zoom (29-108 mm), 2.7" LCD display, Li-Ion, HD video, SD/SDHC/SDXC, time lapse recording, stabilizer', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Digital Camera - Waterproof, shockproof, freezeproof, 16.1 Mpx CCD, 4x zoom (29-108 mm), 2.7" LCD display, Li-Ion, HD video, SD/SDHC/SDXC, time lapse recording, stabilizer', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Camera type', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('SLR', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('12 Mpix', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Power supply', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('battery', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Viewfinder type', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('optical', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Sensitivity (ISO)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('12800', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Display size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('4“', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Weight', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('250 g', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_LOW);
        $this->setPriceForAllPricingGroups($productData, '8385');

        $this->setSellingFrom($productData, '1.2.2013');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 500;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 10);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_PHOTO, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '5960585';
        $productData->partno = 'PC-AD23DGLASS';
        $productData->ean = '8845781245927';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('PRIMECOOLER PC-AD2 3D glasses', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Red and green paper glasses for watching ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Red and green paper glasses for watching ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '15.7');

        $this->setSellingFrom($productData, '13.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 600;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 10);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_BOOKS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, [FlagDataFixture::FLAG_PRODUCT_MADEIN_CZ]);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '9624190';
        $productData->partno = '8595159809694';
        $productData->ean = '8845781245957';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Reflective tape for safe movement on the road', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('This luminiscent tape might prevent you from dying.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('This luminiscent tape might prevent you from dying.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '2');

        $this->setSellingFrom($productData, '12.2.2014');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 10;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 10);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_BOOKS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, [FlagDataFixture::FLAG_PRODUCT_ACTION]);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '4125523';
        $productData->partno = 'ROC-11-710';
        $productData->ean = '8845781245921';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('ROCCAT Kone Pure Optical Gaming Mouse', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Mouse game, 8200dpi laser sensor, 7 programmable buttons + 2D wheel 12000fps, 1ms response, EasyShift, USB, LED backlight, Turbo Core V2, Black ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Mouse game, 8200dpi laser sensor, 7 programmable buttons + 2D wheel 12000fps, 1ms response, EasyShift, USB, LED backlight, Turbo Core V2, Black ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Gaming mouse', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Ergonomics', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Right-handed', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Supported OS', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Windows 2000/XP/Vista/7', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Number of buttons', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('5', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '1562');

        $this->setSellingFrom($productData, '7.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = false;
        $productData->stockQuantity = 0;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '8981612';
        $productData->partno = 'SM-G355HZKNETL';
        $productData->ean = '8845781245953';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Samsung Galaxy Core 2 (SM-G355) - black', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Mobile phone 4.5 "800x480, processor, Quad-Core 1.2GHz, RAM 768 megabytes internal memory of 4GB, microSD up to 64GB, 5 megapixel camera, GPS, WiFi, Bluetooth 4.0, NFC, 3G, microUSB, Li-Ion 2000 mAh, Android 4.4', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Mobile phone 4.5 "800x480, processor, Quad-Core 1.2GHz, RAM 768 megabytes internal memory of 4GB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Display Size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('4.5"', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('800 × 480 px', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Annual energy consumption ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('250 kWh/year', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Display type', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('TFT', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Resolution of rear camera', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('5 Mpx', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '4124');

        $this->setSellingFrom($productData, '8.2.2014');
        $this->setSellingTo($productData, null);
        $productData->usingStock = false;
        $productData->stockQuantity = 0;
        $this->setStocksQuantity($productData, 0);
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_PHONES, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, [FlagDataFixture::FLAG_PRODUCT_NEW]);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_SAMSUNG);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '8981055';
        $productData->partno = 'G3500ZWAETL';
        $productData->ean = '8845781245952';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Samsung Galaxy Core Plus (SM-G350) - white', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Mobile phone 4.5 "800x480, processor, Quad-Core 1.2GHz, RAM 768 megabytes internal memory of 4GB, microSD up to 64GB, 5 megapixel camera, GPS, WiFi, Bluetooth 4.0, NFC, 3G, microUSB, Li-Ion 2000 mAh, Android 4.4 ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Mobile phone 4.5 "800x480, processor, Quad-Core 1.2GHz, RAM 768 megabytes internal memory of 4GB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Display Size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('4.5"', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('800 × 480 px', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Display type', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('TFT', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Annual energy consumption ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('275 kWh/year', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Resolution of rear camera', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('5 Mpx', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '3876');

        $this->setSellingFrom($productData, '7.2.2014');
        $this->setSellingTo($productData, null);
        $productData->usingStock = false;
        $productData->stockQuantity = 0;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_PHONES, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, [FlagDataFixture::FLAG_PRODUCT_MADEIN_CZ]);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_SAMSUNG);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '9773676';
        $productData->partno = 'UE75HU7500';
        $productData->ean = '8845781245932';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Samsung UE75HU7500 (UHD)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Television SMART 3D LED, 189 cm diagonal, CMR 1000 4K Ultra HD 3840x2160, DVB-S2 / T2 / C, 4x HDMI, 3x USB, CI +, LAN, WiFi, DLNA, MHL, HbbTV, Tizen OS, energ. Class A ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Television SMART 3D LED, 189 cm diagonal.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Energy efficiency class', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('A', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Annual energy consumption ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('275 kWh/year', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Screen size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('75"', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '140486.8');

        $this->setSellingFrom($productData, '18.1.2014');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 550;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 0);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_TV, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, [FlagDataFixture::FLAG_PRODUCT_NEW]);

        $productData->sellingDenied = false;
        $productData->preorder = true;
        $this->setBrand($productData, BrandDataFixture::BRAND_SAMSUNG);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '9178302';
        $productData->partno = '4002M4';
        $productData->ean = '8845781245931';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Sencor SDB 4002M4', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('DVB-T Receiver HD - MPEG4, MPEG2, USB recording, timeshift, EPG, HDMI 1.3, USB, SCART, coaxial output, power antenna 5V/50mA ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('DVB-T Receiver HD - MPEG4, MPEG2, USB recording, timeshift', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Tuner', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('DVB-T', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Recording on', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('flash disk', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Multimedia', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Video', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Annual energy consumption ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('250 kWh/year', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '577.7');

        $this->setSellingFrom($productData, '17.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 400;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 10);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_TV, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, [FlagDataFixture::FLAG_PRODUCT_ACTION]);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_SENCOR);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '5963470';
        $productData->partno = '';
        $productData->ean = '8845781245950';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Sennheiser HD 700', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('HQ speakers from well known brand.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('HQ speakers from well known brand.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Ear Coupling', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Circumaural', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Construction', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Open', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Fold-up', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Other', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Determination', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Home Listening', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '14537');

        $this->setSellingFrom($productData, '5.2.2014');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 300;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 10);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_TV, CategoryDataFixture::CATEGORY_PC]);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '9142035';
        $productData->partno = '561392';
        $productData->ean = '8845781245961';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Cap with solar-powered fan, white', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Cap with air conditioning, convenient for hot days.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Cap with air conditioning, convenient for hot days.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '136.9');

        $this->setSellingFrom($productData, '16.2.2014');
        $this->setSellingTo($productData, null);
        $productData->usingStock = false;
        $productData->stockQuantity = 0;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_BOOKS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, [FlagDataFixture::FLAG_PRODUCT_NEW]);

        $productData->sellingDenied = true;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '5964356';
        $productData->partno = 'DSCRX100.CEE8';
        $productData->ean = '8845781245913';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('SONY DSCRX100', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Digital Camera CMOS Exmor R1 20.2 megapixel, 3.6x zoom (28-100 mm F1.8), Full HD video (1920 x 1080) 50p, 3 "LCD, Li-Ion, MS DUO + \u200b\u200bSD/SDHC/SDXC, Face Detection, Intelligent stabilizer ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Digital Camera CMOS Exmor R1 20.2 megapixel, 3.6x zoom', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '12989');

        $this->setSellingFrom($productData, '2.6.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 700;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 10);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_PHOTO, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_SONY);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '9882324P';
        $productData->partno = '1272-2537';
        $productData->ean = '8845781245954';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('SONY Xperia SP C5303', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Mobile phone 4.6 "1280x720, Qualcomm MSM8960Pro 1.7 GHz, 1GB RAM, 8GB 8MPx camera, GPS, WiFi, Bluetooth, FM, micro USB, Android 4.1', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Mobile phone 4.6 "1280x720, Qualcomm MSM8960Pro 1.7 GHz', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Weight (kg)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), '0.54', $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Processor frequency (GHz)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), '2.4', $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '4371.9');

        $this->setSellingFrom($productData, '9.2.2014');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 200;
        $this->setStocksQuantity($productData, 200);
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_PHONES, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, [FlagDataFixture::FLAG_PRODUCT_MADEIN_CZ]);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_SONY);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '5961201';
        $productData->partno = '';
        $productData->ean = '8845781245960';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Fluorescent laces, green', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Fluorescent green laces. Visible at any condition.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Fluorescent green laces. Visible at any condition.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '15');

        $this->setSellingFrom($productData, '15.2.2014');
        $this->setSellingTo($productData, null);
        $productData->usingStock = false;
        $productData->stockQuantity = 0;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_BOOKS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, [FlagDataFixture::FLAG_PRODUCT_NEW, FlagDataFixture::FLAG_PRODUCT_ACTION]);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '4122531';
        $productData->partno = '53082';
        $productData->ean = '8845781245917';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('VERBATIM 1TB external HDD 2,5" USB 3.0 GT SuperSpeed red', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('<p>This product is not an independently functional unit and may require professional installation.</p>', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('This product is not an independently functional unit and may require professional installation.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '1268.7');

        $this->setSellingFrom($productData, '3.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 140;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 140);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, [FlagDataFixture::FLAG_PRODUCT_ACTION]);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_VERBATIM);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '9771195';
        $productData->partno = '1005WH';
        $productData->ean = '8845781245948';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('YENKEE YSP 1005WH white', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Speakers 2 x 3W RMS, 2.0 stereo, portable, volume control, frequency range of 150Hz-20kHz, sensitivity 80dB, 4Ohm impedance, power supply via USB, 3.5 mm audio jack, dimensions 85x85x107mm, weight 550 g', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Speakers 2 x 3W RMS, 2.0 stereo, portable, volume control, frequency range of 150Hz-20kHz, sensitivity 80dB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '189.3');

        $this->setSellingFrom($productData, '3.2.2014');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 100;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 100);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_TV, CategoryDataFixture::CATEGORY_PC]);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '9176544';
        $productData->partno = '32PFL4308I';
        $productData->ean = '8845781243205';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('36" Philips 32PFL4308', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->namePrefix[$locale] = t('Defaultní varianta - prefix', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->nameSufix[$locale] = t('Defaultní varianta - suffix', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('TV LED, 100Hz, diagonal 80cm 100Hz, Full HD 1920 x 1080, DVB-T / C, 2x HDMI, USB, CI +, VGA, SCART, speakers 16W, energy. Class A + ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('TV LED, 100Hz, diagonal 80cm 100Hz, Full HD 1920 x 1080, DVB-T / C, 2x HDMI, USB, CI +, VGA, SCART, speakers 16W, energy. Class A + ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Screen size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('36"', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Color', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('black', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Technology', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('LED', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('1366×768 (HD Ready)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('HDMI', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('No', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '9173.5');

        $this->setSellingFrom($productData, '15.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 200;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 200);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_TV, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, [FlagDataFixture::FLAG_PRODUCT_SALE]);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_PHILIPS);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '9176588';
        $productData->partno = '32PFL4308J';
        $productData->ean = '8845781243205';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('54" Philips CRT 32PFL4308', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('TV CRT, 100Hz, diagonal 80cm 100Hz, Full HD 1920 x 1080, DVB-T / C, 2x HDMI, USB, CI +, VGA, SCART, speakers 16W, energy. Class A + ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('TV CRT, 100Hz, diagonal 80cm 100Hz, Full HD 1920 x 1080, DVB-T / C, 2x HDMI, USB, CI +, VGA, SCART, speakers 16W, energy. Class A + ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Screen size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('54"', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Color', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('white', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Technology', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('CRT', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('1366×768 (HD Ready)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('HDMI', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('No', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '10173.5');

        $this->setSellingFrom($productData, '15.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 200;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 200);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_TV, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, [FlagDataFixture::FLAG_PRODUCT_MADEIN_CZ]);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_PHILIPS);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '5965879B';
        $productData->partno = '47LA790W';
        $productData->ean = '8845781245928';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('LG 47LA790W (FHD)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('TV Cinema 3D LED SMART diagonal 119 cm, 700 MCI, 1920x1080 Full HD, DVB-S / S2 / T / T2 / C, 3x HDMI, 3x USB, CI, SCART, MHL, LAN, HbbTV, WiFi, Miracast / WiDi, WebOS, Web browser, Dual Core, 2 pieces 3D glasses AG-F310, magical driver energ. Class A +', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('TV Cinema 3D LED SMART diagonal 119 cm, 700 MCI, 1920x1080 Full HD', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Screen size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('60"', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Technology', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('LED', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('1366×768 (HD Ready)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('HDMI', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('No', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '19843');

        $this->setSellingFrom($productData, '14.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 80;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 80);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_TV, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $productData->preorder = true;
        $this->setBrand($productData, BrandDataFixture::BRAND_LG);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '9774523';
        $productData->partno = 'LT-823 C82B';
        $productData->ean = '8845781245929';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Orava LT-823 C82B', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Television LED, diagonal 82 cm, 1366x768, DVB-T/C MPEG4 tuner, 2x HDMI, USB, SCART, VGA, headphone jack, USB/HDD recording, hotel mode, Energ. Class A ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Television LED, diagonal 82 cm, 1366x768, DVB-T/C MPEG4 tuner', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '6490');

        $this->setSellingFrom($productData, '14.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 100;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 100);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_TV, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = true;
        $this->setBrand($productData, BrandDataFixture::BRAND_ORAVA);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '7700667';
        $productData->partno = '22MT44D';
        $productData->ean = '8845781245930';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('LG 22MT44D 21,5"', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Television monitor LED 16: 9, 5M: 1, 250cd/m2, 9.5ms, 1366x768, DVB-T/C, HDMI, SCART, D-Sub, USB, speakers, Energ. Class A ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Television monitor LED 16: 9, 5M: 1, 250cd/m2, 9.5ms, 1366x768', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Screen size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('21"', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Technology', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('LED', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('1366×768 (HD Ready)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('HDMI', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('No', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '3999');

        $this->setSellingFrom($productData, '14.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 100;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 100);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_TV, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = true;
        $this->setBrand($productData, BrandDataFixture::BRAND_LG);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '7700668';
        $productData->partno = '22MT44E';
        $productData->ean = '8845781245931';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('LG 22MT44D 30"', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Television monitor LED 16: 9, 5M: 1, 250cd/m2, 9.5ms, 1366x768, DVB-T/C, HDMI, SCART, D-Sub, USB, speakers, Energ. Class A', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Television monitor LED 16: 9, 5M: 1, 250cd/m2, 9.5ms', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Screen size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('30"', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Technology', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('LED', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('1366×768 (HD Ready)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('HDMI', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '3999');

        $this->setSellingFrom($productData, '14.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 100;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 100);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_TV, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_LG);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '8981435';
        $productData->partno = 'LT27D590EW';
        $productData->ean = '8845781245932';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Samsung T27D590EW', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Watch broadcast in stunning Full HD, while at the same stylish devices work. TD590 Monitor allows you to quickly and easily switch between TV and computer monitor or test your multitasking skills and watch TV while working on a split screen. In addition, it offers flexible connectivity options to your entertainment and multimedia can be enjoyed to the fullest. This monitor is perfect for studio flats, rooms in dormitories and small living rooms. Why should you buy two devices when one can do it more?', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Watch broadcast in stunning Full HD, while at the same stylish devices work.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Screen size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('27"', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Technology', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('LED', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('1920×1080 (Full HD)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('HDMI', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '5199');

        $this->setSellingFrom($productData, '14.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 100;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 100);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_TV, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_SAMSUNG);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '8981438';
        $productData->partno = 'LT27D590EX';
        $productData->ean = '8845781245933';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Samsung T27D590EX', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('The TV monitor PLS LED, 1000:1, 5ms, 1920x1080, tuner DVB-T/C, PiP +, 2x HDMI, MHL, USB, CI, Scart, 2x 5W speakers, remote control', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('The TV monitor PLS LED, 1000:1, 5ms, 1920x1080, tuner DVB-T/C, PiP +, 2x HDMI, MHL, USB, CI, Scart, 2x 5W speakers, remote control', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Screen size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('27"', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Technology', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('LED', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('1920×1080 (Full HD)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('HDMI', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '5399');

        $this->setSellingFrom($productData, '14.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 100;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 100);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_TV, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_SAMSUNG);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '9510540';
        $productData->partno = 'UMNP000883';
        $productData->ean = '8845781245934';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Xtreamer SW4', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Xtreamer SW4 is all-encompassing amusement system, bringing fun to your TV. Games, movies and many more functions in HD quality', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Xtreamer SW4 is all-encompassing amusement system, bringing fun to your TV. Games, movies and many more functions', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '2390');

        $this->setSellingFrom($productData, '14.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 100;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 100);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_TV, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '5965879C';
        $productData->partno = '58LA790W';
        $productData->ean = '8845781245935';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('LG 58LA790W (FHD)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('TV SMART LED TV, 147 cm diagonal, 4K Ultra HD 3840x2160 4K upscaler, DVB-S2 / T2 / C, H.265, 3x HDMI, 3x USB, Scart, CI +, LAN, WiFi, Miracast, DLNA, MHL, HbbTV, Web browser, webOS 2.0, 2x10W speakers, magical MR15 driver, energ. Class A +', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('TV SMART LED TV, 147 cm diagonal, 4K Ultra HD 3840x2160 4K upscaler', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '20159');

        $this->setSellingFrom($productData, '14.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 80;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 80);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_TV, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = true;
        $this->setBrand($productData, BrandDataFixture::BRAND_LG);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '9774524';
        $productData->partno = 'LT-823 C82C';
        $productData->ean = '8845781245936';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Orava LT-823 C82C', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Television LED, diagonal 82 cm, 1366x768, DVB-T/C MPEG4 tuner, 2x HDMI, USB, SCART, VGA, headphone jack, USB/HDD recording, hotel mode, Energ. Class A ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Television LED, diagonal 82 cm, 1366x768, DVB-T/C MPEG4 tuner, 2x HDMI', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '7290');

        $this->setSellingFrom($productData, '14.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 100;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 100);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_TV, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, [FlagDataFixture::FLAG_PRODUCT_MADEIN_CZ]);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_ORAVA);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '7700677';
        $productData->partno = '22MT44A';
        $productData->ean = '8845781245937';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('LG 22MT44D 51,5"', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Television monitor IPS, 16: 9, 5M: 1, 200cd/m2, 5ms GTG, FullHD 1920x1080, DVB-S2/T2/C, 2x HDMI, USB, SCART, 2 x 5W speakers, energ. Class A', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Television monitor IPS, 16: 9, 5M: 1, 200cd/m2, 5ms GTG, FullHD 1920x1080', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Screen size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('60"', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Technology', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('LED', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('1920×1080 (Full HD)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('HDMI', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Annual energy consumption ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('275 kWh/year', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '4899');

        $this->setSellingFrom($productData, '14.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 100;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 100);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_TV, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, [FlagDataFixture::FLAG_PRODUCT_MADEIN_CZ]);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_LG);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '7700669';
        $productData->partno = '22MT44F';
        $productData->ean = '8845781245938';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('LG 22MT44D 60"', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Television monitor IPS, 16: 9, 5M: 1, 200cd/m2, 5ms GTG, FullHD 1920x1080, DVB-S2/T2/C, 2x HDMI, USB, SCART, 2 x 5W speakers, energ. Class A ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Television monitor IPS, 16: 9, 5M: 1, 200cd/m2, 5ms GTG, FullHD 1920x1080', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Screen size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('60"', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Technology', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('LED', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('1920×1080 (Full HD)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('HDMI', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Annual energy consumption ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('250 kWh/year', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '5999');

        $this->setSellingFrom($productData, '14.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 100;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 100);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_TV, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_LG);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '8981437';
        $productData->partno = 'LT27D590EY';
        $productData->ean = '8845781245939';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Samsung T27D590EY', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('The TV monitor PLS LED, 1000:1, 5ms, 1920x1080, tuner DVB-T/C, PiP +, 2x HDMI, MHL, USB, CI, Scart, 2x 5W speakers, remote control ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('The TV monitor PLS LED, 1000:1, 5ms, 1920x1080, tuner DVB-T/C, PiP +, 2x HDMI, MHL, USB, CI, Scart, 2x 5W speakers, remote control ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '6199');

        $this->setSellingFrom($productData, '14.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 100;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 100);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_TV, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_SAMSUNG);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '18981438';
        $productData->partno = 'LT27D590EZ';
        $productData->ean = '8845781245940';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Samsung T27D590EZ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('The TV monitor PLS LED, 1000:1, 5ms, 1920x1080, tuner DVB-T/C, PiP +, 2x HDMI, MHL, USB, CI, Scart, 2x 5W speakers, remote control ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('The TV monitor PLS LED, 1000:1, 5ms, 1920x1080, tuner DVB-T/C, PiP +, 2x HDMI, MHL, USB, CI, Scart, 2x 5W speakers, remote control ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '6399');

        $this->setSellingFrom($productData, '14.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 100;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 100);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_TV, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_SAMSUNG);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '9510541';
        $productData->partno = 'UMNP000884';
        $productData->ean = '8845781245941';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Xtreamer SW5', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Xtreamer SW5 is all-encompassing amusement system, bringing fun to your TV. Games, movies and many more functions in HD quality', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Xtreamer SW5 is all-encompassing amusement system, bringing fun to your TV. Games, movies and many more functions', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '2490');

        $this->setSellingFrom($productData, '14.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 100;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 100);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_TV, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, [FlagDataFixture::FLAG_PRODUCT_MADEIN_CZ]);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '9176544M';
        $productData->partno = '32PFL4308';
        $productData->ean = '8845781243205';

        $parameterTranslations = [];
        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Philips 32PFL4308', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('TV LED, 100Hz, diagonal 80cm 100Hz, Full HD 1920 x 1080, DVB-T / C, 2x HDMI, USB, CI +, VGA, SCART, speakers 16W, energy. Class A + ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('TV LED, 100Hz, diagonal 80cm 100Hz, Full HD 1920 x 1080, DVB-T / C, 2x HDMI, USB, CI +, VGA, SCART, speakers 16W, energy. Class A + ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
        }
        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '9173.5');

        $this->setSellingFrom($productData, '15.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = false;
        $productData->stockQuantity = 0;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_TV, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, [FlagDataFixture::FLAG_PRODUCT_ACTION]);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_PHILIPS);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '9176544MF';
        $productData->partno = 'FLO242-PRI';
        $productData->ean = '8845781243206';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Prime flour 1 kg', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Prime flour for creating your own cake. Now with special discount.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Prime flour for creating your own cake. Now with special discount.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Weight', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('1 kg', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_LOW);
        $this->setPriceForAllPricingGroups($productData, '8.3');

        $this->setSellingFrom($productData, '15.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 100000000;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 10000000);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_FOOD, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, [FlagDataFixture::FLAG_PRODUCT_ACTION, FlagDataFixture::FLAG_PRODUCT_MADEIN_CZ]);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '9176544MG';
        $productData->partno = 'FLO242-PRJ';
        $productData->ean = '8845781243277';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Aquila Aquagym non-carbonated spring water', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Aquila Aquagym non-carbonated spring water, description.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '12.5');

        $this->setSellingFrom($productData, '15.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 75;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 75);

        $this->setUnit($productData, UnitDataFixture::UNIT_CUBIC_METERS);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_FOOD, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '9176544MS';
        $productData->partno = 'TIC100';
        $productData->ean = '8845781243207';
        $productData->orderingPriority = 2;

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('100 Czech crowns ticket', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Coupon valued to 100 Czech crowns. You can cash it at any exchange office', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Coupon valued to 100 Czech crowns. You can cash it at any exchange office', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $productData->seoH1s[$domain->getId()] = t('Ticket for 100 Czech crowns', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->seoTitles[$domain->getId()] = t('Ticket for 100 CZK', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->seoMetaDescriptions[$domain->getId()] = t('Coupon valued to 100 Czech crowns.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '100');

        $this->setSellingFrom($productData, '15.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 1000000;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 100000);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_BOOKS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '9176544M3';
        $productData->partno = 'CAB-13';
        $productData->ean = '88457812432071';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('PremiumCord micro USB, A-B, 1m', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Well known USB cable with A and micro B connectors.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Well known USB cable with A and micro B connectors.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Weight', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('50 g', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Connectors', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('A and micro B', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_SECOND_LOW);
        $this->setPriceForAllPricingGroups($productData, '61.9');

        $this->setSellingFrom($productData, '15.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 100000;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 100000);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '9176554';
        $productData->partno = '32PFL4360';
        $productData->ean = '8845781243205';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('36" Hyundai 32PFL4308', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('TV LED, 100Hz, diagonal 80cm 100Hz, Full HD 1920 x 1080, DVB-T / C, 2x HDMI, USB, CI +, VGA, SCART, speakers 16W, energy. Class A + ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('TV LED, 100Hz, diagonal 80cm 100Hz, Full HD 1920 x 1080, DVB-T / C, 2x HDMI, USB, CI +, VGA, SCART, speakers 16W, energy. Class A + ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Screen size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('36"', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Technology', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('LED', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('1366×768 (HD Ready)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('HDMI', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '9173.5');

        $this->setSellingFrom($productData, '15.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 200;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 200);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_TV, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, [FlagDataFixture::FLAG_PRODUCT_ACTION, FlagDataFixture::FLAG_PRODUCT_MADEIN_CZ]);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_HYUNDAI);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '9176578';
        $productData->partno = 'T27D590EY';
        $productData->ean = '8845781243205';
        $productData->orderingPriority = 1;

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('27” Hyundai T27D590EY', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('TV LED, 100Hz, diagonal 80cm 100Hz, Full HD 1920 x 1080, DVB-T / C, 2x HDMI, USB, CI +, VGA, SCART, speakers 16W, energy. Class A + ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('TV LED, 100Hz, diagonal 80cm 100Hz, Full HD 1920 x 1080, DVB-T / C, 2x HDMI, USB, CI +, VGA, SCART, speakers 16W, energy. Class A + ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Screen size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('27"', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Technology', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('LED', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('1366×768 (HD Ready)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('HDMI', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '9173.5');

        $this->setSellingFrom($productData, '15.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 200;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 200);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_TV, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, [FlagDataFixture::FLAG_PRODUCT_ACTION]);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_HYUNDAI);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '7700768';
        $productData->partno = 'T27D590EY';
        $productData->ean = '8845781245930';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('21,5” Hyundai 22MT44', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Television monitor LED 16: 9, 5M: 1, 250cd/m2, 9.5ms, 1366x768, DVB-T/C, HDMI, SCART, D-Sub, USB, speakers, Energ. Class A ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Television monitor LED 16: 9, 5M: 1, 250cd/m2, 9.5ms, 1366x768', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Screen size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('21"', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Technology', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('LED', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('1920×1080 (Full HD)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('HDMI', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '3999');

        $this->setSellingFrom($productData, '14.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 100;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 100);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_TV, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = true;
        $this->setBrand($productData, BrandDataFixture::BRAND_HYUNDAI);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '7700769';
        $productData->partno = '22MT44D';
        $productData->ean = '8845781245931';
        $productData->orderingPriority = 1;

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('30” Hyundai 22MT44D', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Television monitor LED 16: 9, 5M: 1, 250cd/m2, 9.5ms, 1366x768, DVB-T/C, HDMI, SCART, D-Sub, USB, speakers, Energ. Class A', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Television monitor LED 16: 9, 5M: 1, 250cd/m2, 9.5ms, 1366x768', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Screen size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('30"', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Technology', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('LED', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('1920×1080 (Full HD)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('HDMI', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '3999');

        $this->setSellingFrom($productData, '14.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 100;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 100);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_TV, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_HYUNDAI);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '7700777';
        $productData->partno = '22HD44D';
        $productData->ean = '8845781245937';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('51,5” Hyundai 22HD44D', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Television monitor IPS, 16: 9, 5M: 1, 200cd/m2, 5ms GTG, FullHD 1920x1080, DVB-S2/T2/C, 2x HDMI, USB, SCART, 2 x 5W speakers, energ. Class A', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Television monitor IPS, 16: 9, 5M: 1, 200cd/m2, 5ms GTG, FullHD 1920x1080', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Screen size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('51,5"', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '4899');

        $this->setSellingFrom($productData, '14.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 100;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 100);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_TV, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = true;
        $this->setBrand($productData, BrandDataFixture::BRAND_HYUNDAI);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '7700769Z';
        $productData->partno = '22HD44D';
        $productData->ean = '8845781245938';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('60” Hyundai 22HD44D', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Television monitor IPS, 16: 9, 5M: 1, 200cd/m2, 5ms GTG, FullHD 1920x1080, DVB-S2/T2/C, 2x HDMI, USB, SCART, 2 x 5W speakers, energ. Class A ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Television monitor IPS, 16: 9, 5M: 1, 200cd/m2, 5ms GTG, FullHD 1920x1080', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Screen size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('60"', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '5999');

        $this->setSellingFrom($productData, '16.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 100;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 100);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_TV, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_HYUNDAI);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '8981537';
        $productData->partno = 'T27D590EY';
        $productData->ean = '8845781245939';
        $productData->orderingPriority = 1;

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('27” Hyundai T27D590EY', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('The TV monitor PLS LED, 1000:1, 5ms, 1920x1080, tuner DVB-T/C, PiP +, 2x HDMI, MHL, USB, CI, Scart, 2x 5W speakers, remote control ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('The TV monitor PLS LED, 1000:1, 5ms, 1920x1080, tuner DVB-T/C, PiP +, 2x HDMI, MHL, USB, CI, Scart, 2x 5W speakers, remote control ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Screen size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('27"', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Technology', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('LED', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('1920×1080 (Full HD)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('HDMI', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '6199');

        $this->setSellingFrom($productData, '15.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 100;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 100);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_TV, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_HYUNDAI);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '8981538';
        $productData->partno = 'T27D590EZ';
        $productData->ean = '8845781245940';
        $productData->orderingPriority = 1;

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('27” Hyundai T27D590EZ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('The TV monitor PLS LED, 1000:1, 5ms, 1920x1080, tuner DVB-T/C, PiP +, 2x HDMI, MHL, USB, CI, Scart, 2x 5W speakers, remote control ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('The TV monitor PLS LED, 1000:1, 5ms, 1920x1080, tuner DVB-T/C, PiP +, 2x HDMI, MHL, USB, CI, Scart, 2x 5W speakers, remote control ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Screen size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('27"', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Technology', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('LED', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('1920×1080 (Full HD)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('HDMI', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '6399');

        $this->setSellingFrom($productData, '14.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 100;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 100);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_TV, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_HYUNDAI);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '32PFL4400';
        $productData->partno = '32PFL4400';
        $productData->ean = '8845781243205';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('32” Hyundai 32PFL4400', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('TV LED, 100Hz, diagonal 32 inches 100Hz, Full HD 1920 x 1080, DVB-T / C, 2x HDMI, USB, CI +, VGA, SCART, speakers 16W, energy. Class A + ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('TV LED, 100Hz, diagonal 32 inches 100Hz, Full HD 1920 x 1080, DVB-T / C, 2x HDMI, USB, CI +, VGA, SCART, speakers 16W, energy. Class A + ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
        }
        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '9173.5');

        $this->setSellingFrom($productData, '9.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = false;
        $productData->stockQuantity = 0;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_TV, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, [FlagDataFixture::FLAG_PRODUCT_MADEIN_CZ]);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_HYUNDAI);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '7700769XCX';
        $productData->partno = '22HD44D';
        $productData->ean = '8845781245938';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Hyundai 22HD44D', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Television monitor IPS, 16: 9, 5M: 1, 200cd/m2, 5ms GTG, FullHD 1920x1080, DVB-S2/T2/C, 2x HDMI, USB, SCART, 2 x 5W speakers, energ. Class A ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Television monitor IPS, 16: 9, 5M: 1, 200cd/m2, 5ms GTG, FullHD 1920x1080, DVB-S2/T2/C, 2x HDMI, USB, SCART, 2 x 5W speakers, energ. Class A ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
        }
        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '5999');

        $this->setSellingFrom($productData, '16.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 100;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 100);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_TV, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_HYUNDAI);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '532565';
        $productData->partno = '6758B001';
        $productData->ean = '8845781245914';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Nikon ND-22L', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('<h2>Nikon EH-22L</h2> High quality, elegant, soft, and yet reliably protecting brand case for your Nikon digital camera CANON EOS 650D or 700D. Provides protection while traveling from dust, scratches and other negative influences. You also have the camera ready at hand. It fits into the unit with lens 18-55 mm.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('High quality, elegant, soft, and yet reliably protecting brand case for your Canon digital camera CANON EOS 650D or 700D.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '1110.54896');

        $this->setSellingFrom($productData, '11.2.2320');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 100;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 100);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_PHOTO, CategoryDataFixture::CATEGORY_PC]);
        $productData->categoriesByDomainId[Domain::SECOND_DOMAIN_ID] = [];
        $productData->categoriesByDomainId[Domain::SECOND_DOMAIN_ID][] = $this->persistentReferenceFacade->getReference(CategoryDataFixture::CATEGORY_PHOTO);
        $productData->categoriesByDomainId[Domain::SECOND_DOMAIN_ID][] = $this->persistentReferenceFacade->getReference(CategoryDataFixture::CATEGORY_BOOKS);

        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_NIKON);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '5964035';
        $productData->partno = '8596B047';
        $productData->ean = '8845781245912';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Nikon COS 700D', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Nikon EOS 700D + EF-S 18-55 mm + 75-300 mm DC III DC III Quality digital camera with CMOS sensor with a resolution of 18 megapixels, which is to take the top photo in a professional style. Innovative DIGIC 5 image processing delivers powerful in any situation. A high sensitivity range up to ISO 12800 lets you capture great images even in dim light, even without a flash. Automatic smart scene mode analyzes the scene and automatically selects the best settings for the camera. View images, focus, or even pictures can be taken using a 3 "touch screen TFT Clear View II with a whopping resolution of 1.04 million pixels and a vari-angle. With this camera out of you in a moment become a professional cameraman. It makes it possible to shoot stunning movies in Full HD 1080p. Hybrid AF technology enables continuous focus during movie shooting and using the built-in microphone ozvučíme your images are high quality stereo sound. Autofocus system comprising nine cross-type AF points to capture fast-moving objects without any blurring. Thanks to continuous shooting at up to 5 frames per second, you\'ll never miss a crucial moment for getting the best picture possible.The HDR Backlight Control mode, the camera takes three different exposures and combines them into one, in order to preserve details in shadows and areas with high brightness. In Night Scene mode, the camera takes the hand of multiple images at high shutter speeds and combining them together and thus prevents blurring. Captured images and videos simply adding it to an SD Memory Card, SDHC and SDXC, thanks to the integrated connector is miniHDMI you can conveniently viewed on your LCD or plasma TV. The camera can buy a wide range of lenses, flashes and accessories that are compatible with the EOS system.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Canon EOS 700D + EF-S 18-55 mm + 75-300 mm DC III DC III Quality digital camera with CMOS sensor with a resolution of 18 megapixels', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Camera type', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('SLR', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('14 Mpix', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Power supply', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('battery', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Viewfinder type', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('optical', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Sensitivity (ISO)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('6400', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Display size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('3“', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Weight', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('380 g', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_ZERO);
        $this->setPriceForAllPricingGroups($productData, '24990');

        $this->setSellingFrom($productData, '25.1.2014');
        $this->setSellingTo($productData, '25.1.2015');
        $productData->usingStock = true;
        $productData->stockQuantity = 100;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 100);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_PHOTO, CategoryDataFixture::CATEGORY_PC]);
        $productData->categoriesByDomainId[Domain::SECOND_DOMAIN_ID] = [];
        $productData->categoriesByDomainId[Domain::SECOND_DOMAIN_ID][] = $this->persistentReferenceFacade->getReference(CategoryDataFixture::CATEGORY_PHOTO);
        $productData->categoriesByDomainId[Domain::SECOND_DOMAIN_ID][] = $this->persistentReferenceFacade->getReference(CategoryDataFixture::CATEGORY_BOOKS);

        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_NIKON);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '5402880';
        $productData->partno = 'V108060WE000';
        $productData->ean = '8845781245910';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Nikon VH-520', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Digital Camera Nikon VH-520 16 megapixel, 24x zoom, 3.0 "LCD, Li-Ion, FullHD video, histogram, HDR, optical stabilizer, SD/SDHC/SDXC, face detection, USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Digital Camera Nikon VH-520 16 megapixel, 24x zoom, 3.0 "LCD, Li-Ion, FullHD video', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Camera type', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('SLR', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('12 Mpix', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Power supply', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('battery', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Viewfinder type', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('optical', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Sensitivity (ISO)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('6400', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Display size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('3“', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Weight', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('560 g', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '2783');

        $this->setSellingFrom($productData, '3.8.1999');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 200;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 200);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_PHOTO, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_NIKON);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '5965908';
        $productData->partno = 'DMC FT5EP-K';
        $productData->ean = '8845781245911';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Nikon DMC FT5EP', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Digital Camera - Waterproof, shockproof, freezeproof, 16.1 Mpx CCD, 4x zoom (29-108 mm), 2.7" LCD display, Li-Ion, HD video, SD/SDHC/SDXC, time lapse recording, stabilizer', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Digital Camera - Waterproof, shockproof, freezeproof, 16.1 Mpx CCD', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Camera type', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('SLR', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('12 Mpix', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Power supply', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('battery', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Viewfinder type', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('optical', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Sensitivity (ISO)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('12800', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Display size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('3“', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Weight', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('250 g', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_LOW);
        $this->setPriceForAllPricingGroups($productData, '2000');

        $this->setSellingFrom($productData, '3.2.2013');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 500;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 500);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_PHOTO, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_NIKON);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '1532564';
        $productData->partno = '6758B001';
        $productData->ean = '8845781245914';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Canon EH-22M', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('<h2>Canon EH-22L</h2> High quality, elegant, soft, and yet reliably protecting brand case for your Canon digital camera CANON EOS 650D or 700D. Provides protection while traveling from dust, scratches and other negative influences. You also have the camera ready at hand. It fits into the unit with lens 18-55 mm.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('High quality, elegant, soft, and yet reliably protecting brand case for your Canon digital camera CANON EOS 650D or 700D.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Color', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('black', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '1110.54896');

        $this->setSellingFrom($productData, '3.8.1999');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 100;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 100);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_PHOTO, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_CANON);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '15964035';
        $productData->partno = '8596B047';
        $productData->ean = '8845781245912';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Canon EOS 700E', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Canon EOS 700D + EF-S 18-55 mm + 75-300 mm DC III DC III Quality digital camera with CMOS sensor with a resolution of 18 megapixels, which is to take the top photo in a professional style. Innovative DIGIC 5 image processing delivers powerful in any situation. A high sensitivity range up to ISO 12800 lets you capture great images even in dim light, even without a flash. Automatic smart scene mode analyzes the scene and automatically selects the best settings for the camera. View images, focus, or even pictures can be taken using a 3 "touch screen TFT Clear View II with a whopping resolution of 1.04 million pixels and a vari-angle. With this camera out of you in a moment become a professional cameraman. It makes it possible to shoot stunning movies in Full HD 1080p. Hybrid AF technology enables continuous focus during movie shooting and using the built-in microphone ozvučíme your images are high quality stereo sound. Autofocus system comprising nine cross-type AF points to capture fast-moving objects without any blurring. Thanks to continuous shooting at up to 5 frames per second, you\'ll never miss a crucial moment for getting the best picture possible.The HDR Backlight Control mode, the camera takes three different exposures and combines them into one, in order to preserve details in shadows and areas with high brightness. In Night Scene mode, the camera takes the hand of multiple images at high shutter speeds and combining them together and thus prevents blurring. Captured images and videos simply adding it to an SD Memory Card, SDHC and SDXC, thanks to the integrated connector is miniHDMI you can conveniently viewed on your LCD or plasma TV. The camera can buy a wide range of lenses, flashes and accessories that are compatible with the EOS system.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Quality digital camera with CMOS sensor with a resolution of 18 megapixels', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Camera type', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('SLR', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('9 Mpix', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Power supply', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('battery', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Viewfinder type', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('optical', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Sensitivity (ISO)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('1800', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Display size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('3“', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Weight', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('580 g', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_ZERO);
        $this->setPriceForAllPricingGroups($productData, '24990');

        $this->setSellingFrom($productData, '3.2.2013');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 100;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 100);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_PHOTO, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_CANON);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '15402889';
        $productData->partno = 'V108060WE000';
        $productData->ean = '8845781245910';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('OLYMPUS VH-620', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Digital Camera CMOS 16 megapixel, 24x zoom, 3.0 "LCD, Li-Ion, FullHD video, histogram, HDR, optical stabilizer, SD/SDHC/SDXC, face detection, USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Digital Camera CMOS 16 megapixel, 24x zoom, 3.0 "LCD, Li-Ion, FullHD video, histogram, HDR', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Camera type', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('SLR', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('18 Mpix', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Power supply', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('battery', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Viewfinder type', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('optical', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Sensitivity (ISO)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('12800', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Display size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('5“', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Weight', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('580 g', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '2783');

        $this->setSellingFrom($productData, '1.1.2013');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 200;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 200);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_PHOTO, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, [FlagDataFixture::FLAG_PRODUCT_MADEIN_DE]);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_OLYMPUS);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '16402880';
        $productData->partno = 'V108060WE111';
        $productData->ean = '8845781245910';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Nikon TS-800', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Digital Camera Nikon VH-520 16 megapixel, 24x zoom, 3.0 "LCD, Li-Ion, FullHD video, histogram, HDR, optical stabilizer, SD/SDHC/SDXC, face detection, USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Digital Camera CMOS 16 megapixel, 24x zoom, 3.0 "LCD, Li-Ion, FullHD video, histogram, HDR', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Camera type', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('SLR', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('12 Mpix', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Power supply', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('battery', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Viewfinder type', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('optical', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Sensitivity (ISO)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('6400', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Display size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('3“', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Weight', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('580 g', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '2783');

        $this->setSellingFrom($productData, '3.8.1999');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 200;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 200);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_PHOTO, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_NIKON);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = 'tk9710';
        $productData->partno = '8594049730544';
        $productData->ean = '8845781245912';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('TK-9710 Turbo brush Prominent VP 971', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('<ul><li> Type nozzles: Universal </li><li> Diameter: 32 mm </li><li> Turbobrush Big </li></ul>', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Type nozzles: Universal. Diameter: 32 mm. Turbobrush: Big.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '449');

        $this->setSellingFrom($productData, '24.1.2014');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 5050;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 5050);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_GARDEN_TOOLS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = 'ns9020';
        $productData->partno = '8594049730575';
        $productData->ean = '8845781245913';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('NS-9020 Limpio/Clipper/Nino replacement paper bags for VP902/3/9010', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Spare paper bags for vacuum cleaners CONCEPT Limpio VP 9020/21, Clipper VP and VP 913_ 903_, Nino VP 9010 Packaging: 5 pieces of bags + input and output filter.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Spare paper bags for vacuum cleaners CONCEPT Limpio VP 9020/21', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '119');

        $this->setSellingFrom($productData, '22.1.2014');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 5335;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 5335);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_GARDEN_TOOLS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = 'ns9030';
        $productData->partno = '8594049730568';
        $productData->ean = '8845781245914';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('NS-9030 Clipper replacement paper bags for VP-903/913', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Spare paper bags for vacuum cleaners CONCEPT Clipper 9030/31/32/33 VP and VP 9130/31/32. Packaging: 5 pieces of bags + input and output filter.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Spare paper bags for vacuum cleaners CONCEPT Clipper 9030/31/32/33 VP and VP 9130/31/32', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '119');

        $this->setSellingFrom($productData, '6.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 878;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 878);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_GARDEN_TOOLS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = 'ns9040';
        $productData->partno = '8594049730551';
        $productData->ean = '8845781245915';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('NS-9040 Jumbo replacement paper bags for VP-9040', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Spare paper bags for vacuum cleaners CONCEPT Jumbo VP 9,041th Package: 5 pieces of bags + input and output filter.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Spare paper bags for vacuum cleaners CONCEPT Jumbo VP 9', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '149');

        $this->setSellingFrom($productData, '4.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 9877;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 9877);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_GARDEN_TOOLS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = 'ns9710';
        $productData->partno = '8594049730582';
        $productData->ean = '8845781245916';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('NS-9710 Prominent replacement paper bags for VP-9711/12/13', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Spare paper bags for vacuum cleaners CONCEPT Prominent VP 9711/12/13. Packaging: 5 pieces of bags + input and output filter.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Spare paper bags for vacuum cleaners CONCEPT Prominent VP 9711/12/13', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '159');

        $this->setSellingFrom($productData, '31.1.2014');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 65444;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 65444);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_GARDEN_TOOLS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '44290043';
        $productData->partno = '';
        $productData->ean = '8845781245917';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Winch throttle silver VP-9711/12', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '32');

        $this->setSellingFrom($productData, '20.1.2014');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 798;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 798);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_GARDEN_TOOLS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '44290047';
        $productData->partno = '';
        $productData->ean = '8845781245918';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Telescopic pipe VP918x VP802x', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Tube type: Universal. Diameter: 32mm.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Tube type: Universal. Diameter: 32mm.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '299');

        $this->setSellingFrom($productData, '13.2.2014');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 54;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 54);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_GARDEN_TOOLS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '44290050';
        $productData->partno = '8594049731732';
        $productData->ean = '8845781245919';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('HEPA filter VP-9711/12', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '269');

        $this->setSellingFrom($productData, '10.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 78;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 78);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_GARDEN_TOOLS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '44291501';
        $productData->partno = '';
        $productData->ean = '8845781246005';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Reducing the diameter of 35 mm to 32 mm', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '85');

        $this->setSellingFrom($productData, '23.1.2014');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 48;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 48);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_GARDEN_TOOLS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = 'opv3260';
        $productData->partno = '8594049735587';
        $productData->ean = '8845781246006';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('OPV-3260 Built-in retractable hood is 60 cm', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('The telescopic hood is very elegant and practical variant of the classical extractor with minimum space requirements, making it suitable for small kitchens. Stainless steel front bar with rocker switch for easy operation. <h2> Reasons to opt just for telescopic cooker hood Concept OPV-3260: </h2> <ol> <li> Performance is the most important parameter hoods and should be at least equal to ten times the volume of the room. Telescopic cooker hood Brand Concept OPV-3260 will surprise you <strong> output of 198 m3 per hour </strong>. </li> <li> Model AL-3260 is equipped with two mA practical metal grease filters, whose maintenance is very easy and unlimited lifespan. If you can not pay out of steam, you can choose instead of exhaust classic <strong> recirculation system </strong> using two carbon filters, which can be bought (kat.číslo: 61990005). </li> </ol> <h2> Specifications: </h2> <ul> <li> Height: 175 mm </li> <li> Width: 600 mm </li> <li> Depth: 310-470 mm </li> <li> Accessories: backflow preventer </li> <li> Stainless steel front bar </li> <li> Ability to upper exhaust or recirculation </li> <li> Rocker Switch </li> <li> 2 power levels </li> <li> Max. Performance 198 m3 / h. </li> <li> Max.hlučnost the highest level of 66 db (A) </li> <li> The bulb 40 W </li> <li> 2x the grease filter </li> <li> diameter: 120 mm </li> </ul> <h2> Detailed description: </h2> <ul> <li> Optional Accessories: 2x carbon filter 61990005 </li> <li> 1 motor / fan </li> <li> Minimum distance from electric hob 650 mm </li> <li> Minimum distance from gas hob: 750 mm </li> <li> Dimensions for installation (HxWxD): 133 x 560 x 272 mm </li> <li> Net weight: 5.9 kg </li> <li> Voltage: 230 V ~ 50Hz </li> <li> Power: 150 W </li> <li> Cord Length: 2.2 m </li> </ul>', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t(' The telescopic hood is very elegant and practical variant of the classical extractor with minimum space requirements', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '3290');

        $this->setSellingFrom($productData, '10.2.2014');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 48;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 48);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_GARDEN_TOOLS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = 'opp2060';
        $productData->partno = '8594049735594';
        $productData->ean = '8845781246007';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('OPP-2060 Hood sub-mounting 60 cm', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('<p> The minimum space requirements excels sub-mounting range hood OPP-2060th Suitable for very small kitchens. Metal buttons provide ease of use and precise adjustment of the corresponding stage performance. </p> <br /> <br /> <h2> Reasons to choose just the sub-mounting range hood Concept OPP-2060: </h2> <br /> <br /> <ol> <li> Performance is the most important parameter hoods and should be at least equal to ten times the volume of the room. The sub-mounting range hood brand Concept OPP-2060 will surprise performance <strong> 186 m3 per hour </strong>. </li><li> Very practical is the ability to select <strong> 4 Ways exhaust </strong>. </li></ol> Model OPP-2060 is equipped with a practical <strong> metal grease filter </strong>, its maintenance is very easy and unlimited lifespan. If you can not pay out of steam, you can choose instead of exhaust classic <strong> recirculation system </strong> using a carbon filter, which can be bought.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('The minimum space requirements excels sub-mounting range hood OPP-2060th', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '2990');

        $this->setSellingFrom($productData, '19.1.2014');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 48;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 48);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_GARDEN_TOOLS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '61990002';
        $productData->partno = '8594049737383';
        $productData->ean = '8845781246008';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('OPK44xx carbon filter / OPK-5690 / OPK5790 / OPO55xx', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('295 x 240 x 15 mm <br /> cartridge with active carbon', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('295 x 240 x 15 mm <br /> cartridge with active carbon', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '499');

        $this->setSellingFrom($productData, '5.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 489;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 489);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_GARDEN_TOOLS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = 'POScook_book_CZ';
        $productData->partno = 'POScook_book_CZ';
        $productData->ean = '8845781246009';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('POS cookbook_steam oven GB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('<h2> Cookbook </h2> <br /> <br /> <p> What can be cooked in a steam oven? <br /> Using a steamer Concept is surprisingly versatile. It can be used to prepare appetizers, soups, meat, fish, vegetables, vegetarian dishes, dumplings, rice, fruit and desserts. <br /> 75 pages of cookbooks Concept - cook healthy steamed - contains recipes with practical procedures. Each recipe includes: </p> <br /> <br /> <p> The list of ingredients </p> <br /> <p> Method </p> <br /> <p> How many people </p > <br /> <p> Preparation time </p> <br /> <p> The degree of difficulty </p> <br /> <p> + time + temperature </p> <br /> <br /> From under the lids give you some tips - Asparagus with tuna sauce, salmon in vermouth sauce, soup minestrone, chicken mint surprise Cabbage leaves stuffed with minced meat, grilled chop with vegetables, potatoes baked with blue cheese, pear with chocolate sauce, roasted mandelimi and ice cream, etc.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Using a steamer Concept is surprisingly versatile. It can be used to prepare appetizers, soups, meat, fish, vegetables, vegetarian dishes, dumplings, rice, fruit and desserts.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '499');

        $this->setSellingFrom($productData, '27.1.2014');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 48;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 48);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_GARDEN_TOOLS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '61990003';
        $productData->partno = '8594049737390';
        $productData->ean = '8845781246010';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('OPK5660 carbon filter / OPK5760 / OPK6690', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('240 x 205 x 15 mm', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('240 x 205 x 15 mm', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '499');

        $this->setSellingFrom($productData, '28.1.2014');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 4984;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 4894);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_GARDEN_TOOLS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '61990004';
        $productData->partno = '8594049737437';
        $productData->ean = '8845781246011';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Filter carbon OPK4290', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('310 x 285 x 15 mm', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('310 x 285 x 15 mm', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '349');

        $this->setSellingFrom($productData, '29.1.2014');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 878;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 878);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_GARDEN_TOOLS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '61990006';
        $productData->partno = '8594049737444';
        $productData->ean = '8845781246012';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Metal grease filter OPK-5660 / OPK-5760 / OPK-6690', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('220 x 250 x 9 mm', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('220 x 250 x 9 mm', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '599');

        $this->setSellingFrom($productData, '30.1.2014');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 9877;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 9877);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_GARDEN_TOOLS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '61990007';
        $productData->partno = '8594049737451';
        $productData->ean = '8845781246013';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Metal grease filter OPK-4290', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('320 x 300 x 10 mm', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('320 x 300 x 10 mm', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '399');

        $this->setSellingFrom($productData, '26.1.2014');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 65444;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 65444);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_GARDEN_TOOLS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '61990008';
        $productData->partno = '8594049737468';
        $productData->ean = '8845781246014';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Metal grease filter OPV-3260', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('495 x 200 x 8 mm', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('495 x 200 x 8 mm', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '499');

        $this->setSellingFrom($productData, '6.2.2014');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 798;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 798);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_GARDEN_TOOLS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '61990013';
        $productData->partno = '';
        $productData->ean = '8845781246015';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Reduction Avg. OPK OPO 150/120 mm', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '382');

        $this->setSellingFrom($productData, '4.2.2014');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 54;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 54);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_GARDEN_TOOLS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = 'sdv3460';
        $productData->partno = '8594049735754';
        $productData->ean = '8845781246016';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('SDV 3460-built ceramic plate 60 cm', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('<p> It is safe for you to your loved ones as important as us? In that case, you will appreciate with hob SDV-3460 feature residual heat indicators - H that you and your loved ones will protect against nasty burns. The residual heat indicator signals a residual temperature of the cooking zone even after power off. </p><br /> <h2> Reasons to opt just for built-in ceramic plate Concept SDV-3460: </h2><br /> <ol> <li> The built-in ceramic hob SDV-3460 you will be astonished <strong> handy touch controls </strong>. </li><li> Special radiators cooking zones <strong> HI-LIGHT </strong> are able to warm up to a maximum of a few seconds. </li><li> <strong> The residual heat indicator H </strong> - protects you against nasty burns. Indicates residual temperature of the cooking zone even after power off. </li><li> If you want to directly select the time that you want to cook, be sure to take the opportunity of the <strong> off-delay </strong>. </li></ol><br /> <h2> Specifications: </h2><br /> <ul> <li> Height: 60 mm </li> <li> Width: 590 mm </li><li> Depth: 520 mm </li><li> Glass ceramics </li><li> Accessories cleaning scraper </li><li> Touch control </li><li> The residual heat indicator - H </li><li> Without frame, angled edges Grounded </li><li> 4 cooking zones </li><li> Auto-off function - EXTRA SECURE </li><li> The off-delay </li><li> Beep </li><li> Control Panel front center </li><li> Child lock </li><li> The ON state </li></ul><br /> <h2> Details: </h2><br /> <ul> <li> Dimensions for installation (HxWxD): 50 x 560 x 490 mm </li><li> Main switch </li><li> <strong> Left Front plate: </strong> </li><li> The diameter of the front left plates: 165x265 mm </li><li> Input left front plate: 1100/2000 W </li><li> <strong> Rear Left plate: </strong> Circular HI-LIGHT </li><li> The diameter of the rear left of the plate 165 mm </li><li> Input left rear plate: 1200 W </li><li> <strong> The right rear plate: </strong> Circular HI-LIGHT </li><li> The diameter of the rear right plate: 200mm </li><li> wattage right rear plate: 1800 W </li><li> <strong> The front right plate: </strong> Circular HI-lihgt </li><li> The diameter of the front right plate: 165 mm </li><li> Input right front plate: 1200 W </li><li> Max.příkon-el .: 5700-6800 W </li><li> Weight: 10 kg </li><li> Voltage: 220-240 / 400 V 2N ~ 50/60 Hz </li> </ul>', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '6990');

        $this->setSellingFrom($productData, '8.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 78;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 78);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_GARDEN_TOOLS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '62790165';
        $productData->partno = '';
        $productData->ean = '8845781246017';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Side mount plates - few ETV-2860', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '199');

        $this->setSellingFrom($productData, '14.2.2014');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 8878;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 8878);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_GARDEN_TOOLS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '62790168';
        $productData->partno = '';
        $productData->ean = '8845781246018';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Deep baking sheet ETV-2560/2860/2960 / 3160bc', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('440 x 345 x 40 mm', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('440 x 345 x 40 mm', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '299');

        $this->setSellingFrom($productData, '2.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 54;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 54);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_GARDEN_TOOLS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '62790149';
        $productData->partno = '';
        $productData->ean = '8845781246019';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Slicer Pizza ETV-2860', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '49');

        $this->setSellingFrom($productData, '21.1.2014');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 648;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 648);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_GARDEN_TOOLS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '44290821';
        $productData->partno = '8594049736577';
        $productData->ean = '8845781246020';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('HEPA filter SF-9161 / SF-8210', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '199');

        $this->setSellingFrom($productData, '1.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 8744;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 8744);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_GARDEN_TOOLS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '44290820';
        $productData->partno = '';
        $productData->ean = '8845781246021';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Filter input VP-9161 / SF-9162 / SF-8210', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '29');

        $this->setSellingFrom($productData, '1.1.2013');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 648;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 648);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_GARDEN_TOOLS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '44291504';
        $productData->partno = '';
        $productData->ean = '8845781246022';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Floor nozzle metal yellow VP-9141ye', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '299');

        $this->setSellingFrom($productData, '1.2.2013');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 86;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 86);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_GARDEN_TOOLS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = 'sdv3360';
        $productData->partno = '8594049736201';
        $productData->ean = '8845781246023';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('SDV 3360-built ceramic plate 60 cm', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('<p> Enjoy quick cooking with special cooking HI-LIGHT zones which warms almost immediately. </p> Reasons to opt just for built-in ceramic plate Concept SDV-3360:<br /> <ol> <li> The built-in ceramic hob SDV-3360 you will be astonished <strong> handy touch controls </strong>. </li><li> Special radiators cooking zones <strong> HI-LIGHT </strong> are able to warm up to a maximum of a few seconds. </li><li> <strong> The residual heat indicator H </strong> - will protect you against nasty burns. Indicates residual temperature of the cooking zone even after power off. </li><li> If you want to directly select the time that you want to cook, be sure to take the opportunity of the <strong> off-delay </strong>. </li></ol><br /> <h2> Specifications: </h2><br /> <ul> <li> Height: 50mm </li><li> Width: 590 mm </li><li> Depth: 520 mm </li><li> Glass ceramics </li><li> Accessories cleaning scraper </li><li> Touch control </li><li> The residual heat indicator - H </li><li> Without frame, orthogonal edges Grounded </li><li> 4 cooking zones </li><li> Auto-off function - EXTRA SECURE </li><li> The off-delay </li><li> Beep </li><li> Control Panel front center </li><li> Child lock </li><li> The ON state </li></ul><br /> <h2> Details: </h2><br /> <ul> <li> Dimensions for installation (HxWxD): 46 x 560 x 490 mm </li><li> Main switch </li><li> <strong> Left Front plate: </strong> Circular HI-LIGHT </li><li> The diameter of the front left plates: 200 mm </li><li> Input left front plate: 1800 W </li><li> <strong> Rear left hotplate: </strong> Circular HI-LIGHT </li><li> The diameter of the rear left of the plate 165 mm </li><li> Input left rear plate: 1200 W </li><li> <strong> The right rear plate: </strong> Circular HI-LIGHT </li><li> The diameter of the rear right plate: 200mm </li><li> Input right rear plates : 1800 W </li><li> <strong> The front right plate: </strong> Circular HI-lihgt </li><li> The diameter of the front right plate 165 mm </li><li> Input right front plate: 1200 W </li><li> Max.příkon-el .: 5500-6600 W </li><li> Weight: 9 kg </li><li> Voltage: 220-240 / 400 V 2N ~ 50/60 Hz </li> </ul>', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Enjoy quick cooking with special cooking HI-LIGHT zones', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '5990');

        $this->setSellingFrom($productData, '13.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 78;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 78);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_GARDEN_TOOLS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = 'opp1060';
        $productData->partno = '8594049736270';
        $productData->ean = '8845781246024';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('OPP-1060 Hood sub-mounting 60 cm', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('<h2> Specifications: </h2> <ul> <li> Height: 140 mm </li> <li> Width: 600 mm </li> <li> Depth: 470 mm </li> <li> Accessories: backflow preventer </li> <li> White execution </li> <li> Top towing - the possibility of recirculation </li> <li> Controls - slider slider </li> <li> 3 levels of performance </li> <li> Max. Performance: 185 m3 / h. </li> <li> Max.hlučnost the highest level of 66 db (A) </li> <li> The bulb 40 W </li> <li> Textile grease filter </li> <li> diameter: 120 mm </li> </ul> <h2> Detailed description: </h2> <ul> <li> Optional Accessories: 1x textile filter 61990026, 1x carbon filter 61990028 </li> <li> 1 motor / fan </li> <li> Minimum distance from electric hob 650 mm </li> <li> Minimum distance from gas hob: 750 mm </li> <li> Net weight: 4 , 5 kg </li> <li> Voltage: 230 V ~ 50Hz </li> <li> Power: 150 W </li> <li> Cord Length: 1.5 m </li> </ul>', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '1290');

        $this->setSellingFrom($productData, '12.2.2014');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 878;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 878);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_GARDEN_TOOLS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '42390452';
        $productData->partno = '8594049736386';
        $productData->ean = '8845781246025';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Coarse grater blade RM-3240/3250', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '179');

        $this->setSellingFrom($productData, '7.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 787;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 787);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_GARDEN_TOOLS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '42390453';
        $productData->partno = '8594049736362';
        $productData->ean = '8845781246026';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Knife potato RM-3240/3250', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '259');

        $this->setSellingFrom($productData, '8.2.2014');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 77;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 77);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_GARDEN_TOOLS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '61990047';
        $productData->partno = '8594049737581';
        $productData->ean = '8845781246027';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('The filter carbon OPK-4360/4390', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('255 x 255 x 15', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '369');

        $this->setSellingFrom($productData, '7.2.2014');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 7;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 7);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_GARDEN_TOOLS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '44291567';
        $productData->partno = '';
        $productData->ean = '8845781246028';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Hose VP-9310', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '289');

        $this->setSellingFrom($productData, '18.1.2014');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 9;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 9);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_GARDEN_TOOLS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '44291569';
        $productData->partno = '';
        $productData->ean = '8845781246029';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Floor nozzle metal VP-9310', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '299');

        $this->setSellingFrom($productData, '17.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 78;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 78);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_GARDEN_TOOLS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '42390545';
        $productData->partno = '';
        $productData->ean = '8845781246030';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Extension rod plastic TM-4610', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '149');

        $this->setSellingFrom($productData, '5.2.2014');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 54;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 54);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_GARDEN_TOOLS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = 'zn8009';
        $productData->partno = '8594049735839';
        $productData->ean = '8845781246031';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('ZN-8009 steam iron Ferrato stainless steel 2200 Watt Blue', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('<h2>Steam iron Concept ZN8009 wattage 2200 Watt security system AUTO - SHUT OFF lets much easier and more convenient ironing. </h2> Its other advantage is easy operation and many other practical functions and features, such as 3 m long supply cable through which you will not have to move ironing. Specifications: <ul> <li> Stainless steel soleplate </li><li> Even steam dosage: 20 g / min </li><li> Auto-off function AUTO SHUT-OFF Audible : turn off after 30 seconds in horizontal position and after 8 minutes in the vertical position irons </li><li> The water tank: 300 ml </li><li> Airbrush </li><li> The anti-drip ANTI-DRIP </li><li> Self-cleaning function Self Clean </li><li> The descaling function ANTI-CALC </li><li> Vertical steam </li><li> Notification light </li><li> Thermostat </li><li> 3 m power cable (with swivel 360 °) </li><li> Color: blue + silver </li><li> Power consumption: 2200 W </li><li> Voltage: 230 V </li> </ul> <br/>Accessories: container to refill their water', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '999');

        $this->setSellingFrom($productData, '16.2.2014');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 12;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 12);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_GARDEN_TOOLS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '61990022';
        $productData->partno = '8594049737499';
        $productData->ean = '8845781246032';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('The filter carbon OPP-2060', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('480 x 310 x 10 mm <br /> <br /> The filter is a need to adjust the scissors to cover the entire surface of the grease filter.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('480 x 310 x 10 mm. The filter is a need to adjust the scissors to cover the entire surface of the grease filter.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '399');

        $this->setSellingFrom($productData, '2.6.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 351;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 351);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_GARDEN_TOOLS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = 'ts9080';
        $productData->partno = '';
        $productData->ean = '8845781246033';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('TS-9080 replacement bag for textile VP-9080', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Replacement textile bag for vacuum cleaners CONCEPT Sprinter - VP9070. Package: 1 pc', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Replacement textile bag for vacuum cleaners CONCEPT Sprinter - VP9070. Package: 1 pc', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '89');

        $this->setSellingFrom($productData, '9.2.2014');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 654;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 654);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_GARDEN_TOOLS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '61990028';
        $productData->partno = '8594049737529';
        $productData->ean = '8845781246034';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('The filter carbon OPP-1060', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('310 x 480 x 10', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('310 x 480 x 10', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '399');

        $this->setSellingFrom($productData, '15.2.2014');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 83;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 83);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_GARDEN_TOOLS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = 'SprejNerez';
        $productData->partno = '4039286802721';
        $productData->ean = '8845781246035';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Cleaner 3in1 stainless steel appliances (4039286078461)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Perfectly cleans, treats and protects stainless steel surfaces in one step.<ul> <li> <strong> remove </strong> without smudges dust, dirt, fingerprints and grease </li><li> long-lasting protective film <strong> repellent </strong> water and prevents new settling of dirt </li><li> <strong> acts </strong> antistatically </li></ul>', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '499');

        $this->setSellingFrom($productData, '3.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 78;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 78);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_GARDEN_TOOLS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = 'SprejSklo';
        $productData->partno = '4019786908147';
        $productData->ean = '8845781246036';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Glass cleaner and a glass-ceramic plates (4019786908123)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('<p> A perfectly clean glass ceramic cooktop without leaving stains and does not endanger the environment. </p><br /> <ul> <li> <strong> remove </strong> leftover food, grease, nicotine coating and many other impurities </li><li> <strong> does not harm </strong> rubber and plastics </li><li> <strong> does not </strong> AOX - Adsorbable organic halogens </li><li> biologically <strong> degradable </strong> by OECD </li></ul><br />', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '499');

        $this->setSellingFrom($productData, '3.2.2014');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 8;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 8);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_GARDEN_TOOLS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '61990030';
        $productData->partno = '8594049737543';
        $productData->ean = '8845781246037';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('The filter carbon OPK-7790', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('295 x 245 x 15 mm <br /> cartridge with active carbon', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '369');

        $this->setSellingFrom($productData, '15.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 9;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 9);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_GARDEN_TOOLS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '44290823';
        $productData->partno = '8594049736584';
        $productData->ean = '8845781246038';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('HEPA filter VP-9241', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '199');

        $this->setSellingFrom($productData, '15.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 879;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 879);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_GARDEN_TOOLS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '44291542';
        $productData->partno = '';
        $productData->ean = '8845781246039';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Crevice nozzle VP-4290', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '89');

        $this->setSellingFrom($productData, '14.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 98;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 98);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_GARDEN_TOOLS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '44291543';
        $productData->partno = '';
        $productData->ean = '8845781246040';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Nozzle with brush VP-4290', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '138');

        $this->setSellingFrom($productData, '14.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 654;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 654);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_GARDEN_TOOLS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '44290845';
        $productData->partno = '';
        $productData->ean = '8845781246041';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Hose VP-9241', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '289');

        $this->setSellingFrom($productData, '14.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 3524;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 3524);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_GARDEN_TOOLS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '44290872';
        $productData->partno = '';
        $productData->ean = '8845781246042';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Telescopic metal pipes VP-9161', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '299');

        $this->setSellingFrom($productData, '14.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 78;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 78);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_GARDEN_TOOLS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = 'ts9170';
        $productData->partno = '';
        $productData->ean = '8845781246043';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('TS-9170 replacement bag for textile VP-9171', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '169');

        $this->setSellingFrom($productData, '14.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 789;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 789);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_GARDEN_TOOLS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '44290851';
        $productData->partno = '8594049736591';
        $productData->ean = '8845781246044';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('HEPA filter CN-9240 (VP-9241)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '199');

        $this->setSellingFrom($productData, '14.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 564;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 564);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_GARDEN_TOOLS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = 'ns9310';
        $productData->partno = '8594049736645';
        $productData->ean = '8845781246045';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('NS-9310 Infant replacement bags of paper for the VP-9310', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Spare paper bags for vacuum cleaners CONCEPT INFANT VP 9310th Package: 5 pieces of bags + 2.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Spare paper bags for vacuum cleaners CONCEPT INFANT VP 9310th Package: 5 pieces of bags + 2.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '119');

        $this->setSellingFrom($productData, '14.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 456;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 456);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_GARDEN_TOOLS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = 'sms9170';
        $productData->partno = '8594049736638';
        $productData->ean = '8845781246046';

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('SMS-9170 SMS IQ SPACE spare bags', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('fits into VP812x VP9520', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('fits into VP812x VP9520', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
        }

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '149');

        $this->setSellingFrom($productData, '14.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 456;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 456);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_GARDEN_TOOLS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '44290852';
        $productData->partno = '8594049736639';
        $productData->ean = '8845781246047';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Canon PIXMA iP7250', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Powerful color inkjet printer A4 size, quality photo printing, high resolution up to 9600 x 2400dpi, the rate of up to 15 st. / Min in monochrome and up to 10 st. / Min in color, duplex printing, tray for up to 125 sheets, USB 2.0, WiFi 802.11b / g / n, possibility of printing discs. Canon PIXMA iP7250 is a compact portable printer for home or office thous. This is a quality color inkjet printer up to A4 with gentle tiskem.Má stylish and compact design with a hinged upper portion serving as a feeder print media. It is intended primarily for use as a small family or a printer portable printer - especially for printing photographs. It allows users to print documents and fotografií.Je equipped with precision 1 picolitre FINE for printing with tiny particles. It allows you to achieve resolutions up to 9600 x 2400dpi. The printer has five individual ink tanks with the ChromaLife100 + .Tiskárna can print borderless photos. Maximum print speed is 15 pages per minute in black and white or 10 in color. E.g. print borderless 10 x 15 cm photo takes about 21 seconds. The printer also offers direct disc printing and automatic two-sided tisk.Připojení to the computer is done via the USB 2.0 port or through a wireless interface, WiFi 802.11b / g / n. The printer has the dimensions 451 x 368 x 128 mm and weighs 6.6 kg. The included software for easy photo printing My Image Garden.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Powerful color inkjet printer A4 size, quality photo printing', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Print technology', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('inkjet', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Maximum size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('A3', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('LCD', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Print resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('2400x600', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Color printing', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('WiFi', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Weight', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('3.5 kg', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Dimensions', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('449x304x152 mm', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '19990');

        $this->setSellingFrom($productData, '14.1.2001');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 457;
        $this->setStocksQuantity($productData, 457);
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_PRINTERS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_CANON);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '44290853';
        $productData->partno = '8594049736639';
        $productData->ean = '8845781246047';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Canon PIXMA iP7350', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Powerful color inkjet printer A4 size, quality photo printing, high resolution up to 9600 x 2400dpi, the rate of up to 15 st. / Min in monochrome and up to 10 st. / Min in color, duplex printing, tray for up to 125 sheets, USB 2.0, WiFi 802.11b / g / n, possibility of printing discs. Canon PIXMA iP7250 is a compact portable printer for home or office thous. This is a quality color inkjet printer up to A4 with gentle tiskem.Má stylish and compact design with a hinged upper portion serving as a feeder print media. It is intended primarily for use as a small family or a printer portable printer - especially for printing photographs. It allows users to print documents and fotografií.Je equipped with precision 1 picolitre FINE for printing with tiny particles. It allows you to achieve resolutions up to 9600 x 2400dpi. The printer has five individual ink tanks with the ChromaLife100 +. Printer can print borderless photos. Maximum print speed is 15 pages per minute in black and white or 10 in color. E.g. print borderless 10 x 15 cm photo takes about 21 seconds. The printer also offers direct disc printing and automatic two-sided tisk.Připojení to the computer is done via the USB 2.0 port or through a wireless interface, WiFi 802.11b / g / n. The printer has the dimensions 451 x 368 x 128 mm and weighs 6.6 kg. The included software for easy photo printing My Image Garden.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Powerful color inkjet printer A4 size, quality photo printing', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Print technology', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('inkjet', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Maximum size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('A4', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('LCD', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('No', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Print resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('2400x600', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Color printing', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('WiFi', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Weight', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('3.5 kg', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Dimensions', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('449x304x152 mm', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '19990');

        $this->setSellingFrom($productData, '14.1.2001');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 457;
        $this->setStocksQuantity($productData, 457);
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_PRINTERS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_CANON);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '9184536';
        $productData->partno = '8331B006';
        $productData->ean = '8845781245938';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Canon MG3650', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('<h2>Canon PIXMA MG3350 black</h2><p>Features of modern and elegantly prepared MFPs<strong> s new wireless capabilities</strong>. Function <strong>automatic two-sided printing</strong> printing on both sides, which saves paper while producing professional looking documents. The printer uses<strong> ChromaLife100 ink system </strong>with four colors of ink hidden <strong>two print cartridges</strong>That provide easy user service and stable print quality throughout the life. You reach for XL FINE cartridges provide printing multiple pages significantly between individual ink replacement. This is ideal if you often print.<br><br>Do smart device application download <strong>Canon PIXMA Printing Solutions</strong> a straight print or scan. In addition, you can check the printer status and ink levels. They also supported services <strong>Apple AirPrint</strong> and access to the Internet and <strong>Google Cloud Print</strong>. Software <strong>My Image Garden</strong> has a solution for organizing and printing photos, scanning, and access to online services. Due to advanced features such as face detection, you will always find exactly what you\'re looking for.<br><strong>Additional information:</strong><br><strong>Print:</strong><br>Technology: 4-ink (in 2 packs) ChromaLife100 system, the head of FINE (2 pl)<br>Borderless printing: A4, Letter, 20 x 25 cm, 13 x 18 cm, 10 x 15 cm<br>Automatic two-sided printing: A4, A5, B5, Letter<br>Printing from Application PIXMA Printing Solutions, Google Cloud Print, Apple AirPrint</p>', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('The printer uses ChromaLife100 ink system with four colors of ink hidden two print cartridges', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Print technology', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('inkjet', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Maximum size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('A4', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('LCD', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('No', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Print resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('2400x600', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Color printing', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('WiFi', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Dimensions', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('449x304x152 mm', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Weight', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('3.5 kg', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '1314.1');

        $this->setSellingFrom($productData, '24.1.2014');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 0;
        $this->setStocksQuantity($productData, 0);
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_PRINTERS, CategoryDataFixture::CATEGORY_PC]);
        $productData->categoriesByDomainId[Domain::SECOND_DOMAIN_ID] = [];
        $productData->categoriesByDomainId[Domain::SECOND_DOMAIN_ID][] = $this->persistentReferenceFacade->getReference(CategoryDataFixture::CATEGORY_PHOTO);

        $this->setFlags($productData, [FlagDataFixture::FLAG_PRODUCT_NEW, FlagDataFixture::FLAG_PRODUCT_ACTION]);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_CANON);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '9184440';
        $productData->partno = '8328B006';
        $productData->ean = '8845781245936';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('Canon PIXMA MG2650', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('<h2>Canon PIXMA MG2450</h2><p>Stylish and affordable, accessible multifunction devices for the home. Easy <strong> printing, scanning and copying </strong> in one device will take much less space and you\'ll save money than buying individual components. The printing machine uses an innovative print <strong> FINE technology </strong> Which guarantee excellent print quality. The printer has a system of four ink colors hidden in two ink cartridges, which provide easy user service and stable print quality throughout the life. <strong> You can reach the XL cartridges </strong> FINE, which provide significantly greater number of print pages between ink replacement. This is ideal if you are printing large volumes.<br><br>Software <strong> My Image Garden </strong> will reveal the full range of functions PIXMA printers. It offers solutions for the layout and printing photos, scanning, and access to online services. Due to advanced features such as face detection, it will scan all the pictures on your computer (even those long forgotten), and compile them into great designs to print. Service <strong> CREATIVE PARK PREMIUM</strong> you can download and print photos, images and artwork from internationally recognized photographers and artists. Create greeting cards, calendars or stunning 3D paper products, such as the space shuttle Endeavour.</p>', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Easy printing, scanning and copying in one device will take much less space and you\'ll save money than buying individual components.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Print technology', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('inkjet', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Maximum size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('A4', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('LCD', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('No', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Print resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('2400x600', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Color printing', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('WiFi', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Weight', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('3.5 kg', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Dimensions', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('449x304x152 mm', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '818');

        $this->setSellingFrom($productData, '22.1.2014');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 459;
        $this->setStocksQuantity($productData, 459);
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_PRINTERS, CategoryDataFixture::CATEGORY_PC]);
        $productData->categoriesByDomainId[Domain::SECOND_DOMAIN_ID] = [];
        $productData->categoriesByDomainId[Domain::SECOND_DOMAIN_ID][] = $this->persistentReferenceFacade->getReference(CategoryDataFixture::CATEGORY_PHOTO);

        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_CANON);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '718254';
        $productData->partno = 'B2L57C';
        $productData->ean = '8845781245937';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('HP Deskjet Ink Advantage 1615 (B2L57C)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('<p>Hewlett-Packard was founded in the difficult times of the Great Depression. The founders were a pair of friends whose name the company still proudly bears. They started their business in an unobtrusive garage near the city of Palo Alto. It is now a national monument. HP’s success lay not in copying existing products, but in the ability and courage to come up with something new.</p><p>The first commercial triumph was an oscillator that surpassed all competition in quality, yet sold at a quarter of the price. In 1968, HP released their first desktop computer - a desktop calculator. The company currently manufactures products primarily related to computer technology - computers and laptops, printers, scanners, digital cameras, servers, and last but not least, calculators.</p><p>Unless otherwise indicated in the product description, packaging does not contain a USB interface cable.</p>', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Hewlett-Packard was founded in the difficult times of the Great Depression.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Print technology', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('inkjet', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Maximum size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('A3', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('LCD', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Print resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('2400x600', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Color printing', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('WiFi', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Weight', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('3.5 kg', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Dimensions', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('449x304x152 mm', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '1238');

        $this->setSellingFrom($productData, '23.1.2014');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 460;
        $this->setStocksQuantity($productData, 460);
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_PRINTERS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_HP);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '8980686';
        $productData->partno = '1318206';
        $productData->ean = '8845781245935';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('OKI MC861cdxm', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('Toner for MC861/ 851, 7000 pages', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('Toner for MC861/ 851, 7000 pages', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Print technology', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('inkjet', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Maximum size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('A3', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('LCD', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('No', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Print resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('2400x600', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Color printing', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('WiFi', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('No', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Dimensions', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('426x306x145 mm', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Weight', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('3.5 kg', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '67771.9');

        $this->setSellingFrom($productData, '21.1.2014');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 200;
        $this->setStocksQuantity($productData, 200);
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_PRINTERS, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, []);

        $productData->sellingDenied = false;
        $this->setBrand($productData, null);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '9176522';
        $productData->partno = '32PFL4308J';
        $productData->ean = '8845781243205';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('24" Philips 32PFL4308', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('TV LED, 100Hz, diagonal 80cm 100Hz, Full HD 1920 x 1080, DVB-T / C, 2x HDMI, USB, CI +, VGA, SCART, speakers 16W, energy. Class A + ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('TV LED, 100Hz, diagonal 80cm 100Hz, Full HD 1920 x 1080, DVB-T / C, 2x HDMI, USB, CI +, VGA, SCART, speakers 16W, energy. Class A + ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Screen size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('24"', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Color', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('black', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Technology', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('LED', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('1366×768 (HD Ready)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('HDMI', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('No', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '30173.5');

        $this->setSellingFrom($productData, '15.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 200;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 10);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_TV, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, [FlagDataFixture::FLAG_PRODUCT_MADEIN_CZ]);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_PHILIPS);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '917652236';
        $productData->partno = '32PFL4308J';
        $productData->ean = '8845781243205';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('36" Philips CRT 32PFL4308', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('TV CRT, 100Hz, diagonal 80cm 100Hz, Full HD 1920 x 1080, DVB-T / C, 2x HDMI, USB, CI +, VGA, SCART, speakers 16W, energy. Class A + ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('TV CRT, 100Hz, diagonal 80cm 100Hz, Full HD 1920 x 1080, DVB-T / C, 2x HDMI, USB, CI +, VGA, SCART, speakers 16W, energy. Class A + ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Screen size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('36"', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Color', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('white', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Technology', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('CRT', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('1366×768 (HD Ready)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('HDMI', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('No', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '30173.5');

        $this->setSellingFrom($productData, '15.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 200;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 10);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_TV, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, [FlagDataFixture::FLAG_PRODUCT_MADEIN_CZ]);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_PHILIPS);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '917652254';
        $productData->partno = '32PFL4308J';
        $productData->ean = '8845781243205';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('54" Philips 32PFL4308', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('TV LED, 100Hz, diagonal 80cm 100Hz, Full HD 1920 x 1080, DVB-T / C, 2x HDMI, USB, CI +, VGA, SCART, speakers 16W, energy. Class A + ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('TV LED, 100Hz, diagonal 80cm 100Hz, Full HD 1920 x 1080, DVB-T / C, 2x HDMI, USB, CI +, VGA, SCART, speakers 16W, energy. Class A + ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Screen size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('54"', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Color', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('black', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Technology', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('LED', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('1366×768 (HD Ready)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('HDMI', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('No', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '40173.5');

        $this->setSellingFrom($productData, '15.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 200;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 10);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_TV, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, [FlagDataFixture::FLAG_PRODUCT_MADEIN_CZ]);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_PHILIPS);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '91765223';
        $productData->partno = '32PFL4308J';
        $productData->ean = '8845781243205';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('24" Philips CRT 32PFL4308', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('TV CRT, 100Hz, diagonal 80cm 100Hz, Full HD 1920 x 1080, DVB-T / C, 2x HDMI, USB, CI +, VGA, SCART, speakers 16W, energy. Class A + ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('TV CRT, 100Hz, diagonal 80cm 100Hz, Full HD 1920 x 1080, DVB-T / C, 2x HDMI, USB, CI +, VGA, SCART, speakers 16W, energy. Class A + ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Screen size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('24"', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Color', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('white', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Technology', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('CRT', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('1366×768 (HD Ready)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('HDMI', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('No', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '30173.5');

        $this->setSellingFrom($productData, '15.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 200;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 10);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_TV, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, [FlagDataFixture::FLAG_PRODUCT_MADEIN_CZ]);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_PHILIPS);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '91765542';
        $productData->partno = '32PFL4360';
        $productData->ean = '8845781243205';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('36" Hyundai PLASMA 32PFL4308', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('TV PLASMA, 100Hz, diagonal 80cm 100Hz, Full HD 1920 x 1080, DVB-T / C, 2x HDMI, USB, CI +, VGA, SCART, speakers 16W, energy. Class A + ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('TV PLASMA, 100Hz, diagonal 80cm 100Hz, Full HD 1920 x 1080, DVB-T / C, 2x HDMI, USB, CI +, VGA, SCART, speakers 16W, energy. Class A + ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Screen size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('36"', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Technology', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('PLASMA', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('1366×768 (HD Ready)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('HDMI', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '9173.5');

        $this->setSellingFrom($productData, '15.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 200;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 10);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_TV, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, [FlagDataFixture::FLAG_PRODUCT_ACTION, FlagDataFixture::FLAG_PRODUCT_MADEIN_CZ]);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_HYUNDAI);

        $this->createProduct($productData);

        $productData = $this->productDataFactory->create();

        $productData->catnum = '91765782';
        $productData->partno = 'T27D590EY';
        $productData->ean = '8845781243205';

        $parameterTranslations = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $locale = $domain->getLocale();
            $productData->name[$locale] = t('27” Hyundai PLASMA T27D590EY', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $productData->descriptions[$domain->getId()] = t('TV PLASMA, 100Hz, diagonal 80cm 100Hz, Full HD 1920 x 1080, DVB-T / C, 2x HDMI, USB, CI +, VGA, SCART, speakers 16W, energy. Class A + ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());
            $productData->shortDescriptions[$domain->getId()] = t('TV PLASMA, 100Hz, diagonal 80cm 100Hz, Full HD 1920 x 1080, DVB-T / C, 2x HDMI, USB, CI +, VGA, SCART, speakers 16W, energy. Class A + ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domain->getLocale());

            $i = 0;
            $this->addParameterTranslations($parameterTranslations, t('Screen size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('27"', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Technology', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('PLASMA', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('Resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('1366×768 (HD Ready)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
            $this->addParameterTranslations($parameterTranslations, t('HDMI', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), t('Yes', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), $locale, $i);
        }

        $this->setParametersByTranslations($productData, $parameterTranslations);

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setPriceForAllPricingGroups($productData, '9173.5');

        $this->setSellingFrom($productData, '15.1.2000');
        $this->setSellingTo($productData, null);
        $productData->usingStock = true;
        $productData->stockQuantity = 200;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
        $this->setStocksQuantity($productData, 10);

        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setAvailability($productData, AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $this->setCategoriesForAllDomains($productData, [CategoryDataFixture::CATEGORY_TV, CategoryDataFixture::CATEGORY_PC]);
        $this->setFlags($productData, [FlagDataFixture::FLAG_PRODUCT_ACTION]);

        $productData->sellingDenied = false;
        $this->setBrand($productData, BrandDataFixture::BRAND_HYUNDAI);

        $this->createProduct($productData);

        $this->createVariants();
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     * @return \App\Model\Product\Product
     */
    private function createProduct(ProductData $productData): Product
    {
        $this->fillShortDescriptionUpsBySeoDescription($productData);
        $productData->uuid = array_pop($this->uuidPool);
        /** @var \App\Model\Product\Product $product */
        $product = $this->productFacade->create($productData);

        $this->addProductReference($product);

        return $product;
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     */
    private function fillShortDescriptionUpsBySeoDescription(ProductData $productData): void
    {
        foreach ($productData->seoMetaDescriptions as $domainId => $seoDescription) {
            $productData->shortDescriptionUsp1[$domainId] = $seoDescription;
            $productData->shortDescriptionUsp2[$domainId] = $seoDescription;
            $productData->shortDescriptionUsp3[$domainId] = $seoDescription;
            $productData->shortDescriptionUsp4[$domainId] = $seoDescription;
            $productData->shortDescriptionUsp5[$domainId] = $seoDescription;
        }
    }

    /**
     * @return array
     */
    public static function getVariantCatnumsByMainVariantCatnum(): array
    {
        return [
            '9176544M' => [
                '9176544', //36 / led
                '9176588', //54 / crt
                '9176522', //24 / led
                '917652236', //36 / crt
                '917652254', //54 / led
                '91765223', //24 / crt
            ],
            '32PFL4400' => [
                '9176554', // 36 / led
                '9176578', // 27 / led
                '91765542', // 36 / plasma
                '91765782', // 27 / plasma
            ],
            '7700769XCX' => [
                '7700777',
                '7700769Z',
            ],
        ];
    }

    private function createVariants(): void
    {
        $variantCatnumsByMainVariantCatnum = self::getVariantCatnumsByMainVariantCatnum();

        foreach ($variantCatnumsByMainVariantCatnum as $mainVariantCatnum => $variantsCatnums) {
            $mainProduct = $this->getProductFromCacheByCatnum($mainVariantCatnum);
            $mainProduct->setAsMainVariant();
            $this->em->flush();

            foreach ($variantsCatnums as $variantCatnum) {
                $variant = $this->getProductFromCacheByCatnum($variantCatnum);
                $mainProduct->addVariant($variant);
                $this->em->flush();
            }
        }
    }

    /**
     * @param string[] $parameterNamesByLocale
     * @param string[]|null $parameterGroupNamesByLocale
     * @param string|null $unitReferenceName
     * @return \App\Model\Product\Parameter\Parameter
     */
    private function findParameterByNamesOrCreateNew(array $parameterNamesByLocale, ?array $parameterGroupNamesByLocale, ?string $unitReferenceName = null): BaseParameter
    {
        /** @var \App\Model\Product\Parameter\Parameter|null $parameter */
        $parameter = $this->parameterFacade->findParameterByNames($parameterNamesByLocale);

        $parameterGroup = null;
        if ($parameterGroupNamesByLocale !== null) {
            $parameterGroup = $this->findParameterGroupByNamesOrCreateNew($parameterGroupNamesByLocale);
        }

        if ($parameter === null) {
            $parameterData = $this->parameterDataFactory->create();
            $parameterData->name = $parameterNamesByLocale;
            $parameterData->group = $parameterGroup;

            if ($unitReferenceName !== null) {
                /** @var \App\Model\Product\Unit\Unit $unit */
                $unit = $this->persistentReferenceFacade->getReference($unitReferenceName);
                $parameterData->unit = $unit;
            }

            if ($parameterNamesByLocale['cs'] === 'Barva') {
                $parameterData->akeneoCode = Parameter::COLOR_PARAMETER_AKENEO_CODE;
            }

            /** @var \App\Model\Product\Parameter\Parameter|null $parameter */
            $parameter = $this->parameterFacade->create($parameterData);

            $firstDomainLocale = $this->domain->getDomainConfigById(1)->getLocale();
            $this->addReference(ParameterDataFixture::PARAMETER_PREFIX . $parameterNamesByLocale[$firstDomainLocale], $parameter);
        }

        return $parameter;
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     * @param array $parametersTranslations
     */
    private function setParametersByTranslations(ProductData $productData, array $parametersTranslations): void
    {
        foreach ($parametersTranslations as $parameterTranslations) {
            $parameter = $this->findParameterByNamesOrCreateNew($parameterTranslations['names'], $parameterTranslations['group_name'], $parameterTranslations['unit_name']);

            foreach ($parameterTranslations['values'] as $locale => $parameterValue) {
                $productParameterValueData = $this->productParameterValueDataFactory->create();
                $parameterValueData = $this->parameterValueDataFactory->create();
                $parameterValueData->text = $parameterValue;
                $parameterValueData->locale = $locale;
                $productParameterValueData->parameterValueData = $parameterValueData;
                $productParameterValueData->parameter = $parameter;

                $productData->parameters[] = $productParameterValueData;
            }
        }
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     * @param string $price
     */
    private function setPriceForAllPricingGroups(ProductData $productData, string $price): void
    {
        foreach ($this->pricingGroupFacade->getAll() as $pricingGroup) {
            /** @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vat */
            $vat = $this->getReferenceForDomain(VatDataFixture::VAT_HIGH, $pricingGroup->getDomainId());
            /** @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currencyCzk */
            $currencyCzk = $this->getReference(CurrencyDataFixture::CURRENCY_CZK);

            $money = $this->priceConverter->convertPriceToInputPriceWithoutVatInDomainDefaultCurrency(
                Money::create($price),
                $currencyCzk,
                $vat->getPercent(),
                $pricingGroup->getDomainId()
            );

            $productData->manualInputPricesByPricingGroupId[$pricingGroup->getId()] = $money;
        }
    }

    /**
     * @param array $parameterTranslations
     * @param string $parameterName
     * @param string $parameterValue
     * @param string $locale
     * @param int $i
     * @param string|null $parameterGroupName
     * @param string|null $unitReferenceName
     */
    private function addParameterTranslations(array &$parameterTranslations, string $parameterName, string $parameterValue, string $locale, int &$i, ?string $parameterGroupName = null, ?string $unitReferenceName = null): void
    {
        $parameterTranslations[$i]['names'][$locale] = $parameterName;
        $parameterTranslations[$i]['values'][$locale] = $parameterValue;
        $parameterTranslations[$i]['group_name'][$locale] = $parameterGroupName;
        $parameterTranslations[$i]['unit_name'] = $unitReferenceName;

        if ($parameterGroupName === null) {
            $parameterTranslations[$i]['group_name'] = null;
        }

        $i++;
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     * @param string[] $categoryReferences
     */
    private function setCategoriesForAllDomains(ProductData $productData, array $categoryReferences): void
    {
        foreach ($this->domain->getAllIds() as $domainId) {
            foreach ($categoryReferences as $categoryReference) {
                $productData->categoriesByDomainId[$domainId][] = $this->persistentReferenceFacade->getReference($categoryReference);
            }
        }
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     * @param string[] $flagReferences
     */
    private function setFlags(ProductData $productData, array $flagReferences): void
    {
        foreach ($this->domain->getAllIds() as $domainId) {
            foreach ($flagReferences as $flagReference) {
                $productData->flags[$domainId][] = $this->persistentReferenceFacade->getReference($flagReference);
            }
        }
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     * @param string $unitReference
     */
    private function setUnit(ProductData $productData, string $unitReference): void
    {
        $productData->unit = $this->persistentReferenceFacade->getReference($unitReference);
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     * @param string $availabilityReference
     */
    private function setAvailability(ProductData $productData, string $availabilityReference): void
    {
        $productData->availability = $this->persistentReferenceFacade->getReference($availabilityReference);
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     * @param string|null $date
     */
    private function setSellingFrom(ProductData $productData, ?string $date): void
    {
        $productData->sellingFrom = $date === null ? null : new DateTime($date);
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     * @param string|null $date
     */
    private function setSellingTo(ProductData $productData, ?string $date): void
    {
        $productData->sellingTo = $date === null ? null : new DateTime($date);
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     * @param string|null $brandReference
     */
    private function setBrand(ProductData $productData, ?string $brandReference): void
    {
        /** @var \App\Model\Product\Brand\Brand|null $brand */
        $brand = $brandReference === null ? null : $this->persistentReferenceFacade->getReference($brandReference);
        $productData->brand = $brand;
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     * @param string|null $vatReference
     */
    private function setVat(ProductData $productData, ?string $vatReference): void
    {
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat[] $productVatsIndexedByDomainId */
        $productVatsIndexedByDomainId = [];

        foreach ($this->domain->getAllIds() as $domainId) {
            if ($vatReference !== null) {
                $productVatsIndexedByDomainId[$domainId] = $this->persistentReferenceFacade->getReferenceForDomain($vatReference, Domain::FIRST_DOMAIN_ID);
            }
        }
        $productData->vatsIndexedByDomainId = $productVatsIndexedByDomainId;
    }

    /**
     * @param \App\Model\Product\Product $product
     */
    public function addProductReference(Product $product)
    {
        $this->addReference(self::PRODUCT_PREFIX . $this->productNo, $product);
        $this->productNo++;

        if (in_array($product->getCatnum(), $this->getAllVariantCatnumsFromAssociativeArray(self::getVariantCatnumsByMainVariantCatnum()), true)) {
            $this->saveProductToCache($product);
        }

        $this->em->clear();
    }

    /**
     * @param \App\Model\Product\Product $product
     */
    private function saveProductToCache(Product $product): void
    {
        $this->productIdsByCatnum[$product->getCatnum()] = $product->getId();
    }

    /**
     * @param string $catnum
     * @return \App\Model\Product\Product
     */
    private function getProductFromCacheByCatnum(string $catnum): Product
    {
        /** @var \App\Model\Product\Product $product */
        $product = $this->productFacade->getById($this->productIdsByCatnum[$catnum]);

        return $product;
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies(): array
    {
        return [
            VatDataFixture::class,
            AvailabilityDataFixture::class,
            CategoryDataFixture::class,
            BrandDataFixture::class,
            UnitDataFixture::class,
            PricingGroupDataFixture::class,
            SettingValueDataFixture::class,
        ];
    }

    /**
     * @param array $productCatnumsByMainVariantCatnum
     * @return string[]
     */
    private function getAllVariantCatnumsFromAssociativeArray(array $productCatnumsByMainVariantCatnum): array
    {
        $catnums = [];

        foreach ($productCatnumsByMainVariantCatnum as $mainVariantCatnum => $variantCatnums) {
            $catnums[] = $mainVariantCatnum;
            $catnums = array_merge($catnums, $variantCatnums);
        }

        return array_unique($catnums);
    }

    /**
     * @param string[] $parameterGroupNameByLocale
     * @return \App\Model\Product\Parameter\ParameterGroup|mixed|null
     */
    private function findParameterGroupByNamesOrCreateNew(array $parameterGroupNameByLocale)
    {
        $parameterGroup = $this->parameterGroupFacade->findParameterGroupByNames($parameterGroupNameByLocale);

        if ($parameterGroup === null) {
            $parameterGroupData = $this->parameterGroupDataFactory->create();
            $parameterGroupData->names = $parameterGroupNameByLocale;
            $parameterGroupData->akeneoCode = $this->generator->lexify('???????');
            $parameterGroup = $this->parameterGroupFacade->create($parameterGroupData);
        }

        return $parameterGroup;
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     * @param int $quantity
     */
    public function setStocksQuantity(ProductData $productData, int $quantity)
    {
        $stocks = $this->stockRepository->getAllStocks();
        foreach ($stocks as $stock) {
            $productStockData = $this->productStockDataFactory->createFromStock($stock);
            $productStockData->productQuantity = $quantity;
            $productData->stockProductData[$stock->getId()] = $productStockData;
        }
    }

    /**
     * @param string[] $storeReferenceNames
     * @return \App\Model\Store\ProductStoreData[]
     */
    private function getProductStoreDataWithExposedOnStores(array $storeReferenceNames): array
    {
        $productStoreDataArray = [];
        foreach ($storeReferenceNames as $storeReferenceName) {
            /** @var \App\Model\Store\Store $store */
            $store = $this->getReference($storeReferenceName);
            $productStoreData = $this->productStoreDataFactory->createFromStore($store);
            $productStoreData->productExposed = true;
            $productStoreDataArray[] = $productStoreData;
        }

        return $productStoreDataArray;
    }
}
