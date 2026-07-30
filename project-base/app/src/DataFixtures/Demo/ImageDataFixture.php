<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\DataFixtures\Demo\DemoDataFactory\ImageDataFixtureNameFactory;
use App\Model\Category\Category;
use App\Model\Payment\Payment;
use App\Model\Product\Brand\Brand;
use App\Model\Product\Product;
use App\Model\Slider\SliderItemFacade;
use App\Model\Transport\Transport;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\MountManager;
use LogicException;
use Override;
use Shopsys\FrameworkBundle\Component\Image\Image;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;
use Shopsys\FrameworkBundle\Model\Advert\Advert;
use Shopsys\FrameworkBundle\Model\Advert\AdvertFacade;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticle;
use Shopsys\FrameworkBundle\Model\Blog\Author\BlogArticleAuthor;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory;
use Shopsys\FrameworkBundle\Model\SalesRepresentative\SalesRepresentative;
use Shopsys\FrameworkBundle\Model\Store\Store;
use Shopsys\FrameworkBundle\Model\Transport\TransportGroup;
use Symfony\Component\Clock\DatePoint;
use Symfony\Component\Filesystem\Filesystem;

class ImageDataFixture extends AbstractFileFixture implements DependentFixtureInterface
{
    public const string IMAGES_TABLE_NAME = 'images';
    public const string IMAGES_TRANSLATIONS_TABLE_NAME = 'images_translations';
    public const string IMAGE_TYPE = 'jpg';
    public const string IMAGE_TYPE_PNG = 'png';

    // Repeated image motifs are deliberately spread across six-item storefront pages.
    private const array DEMO_BLOG_ARTICLE_IMAGE_IDS_BY_ARTICLE_NUMBER = [
        1 => 610,
        2 => 611,
        3 => 612,
        4 => 628,
        5 => 614,
        6 => 625,
        7 => 617,
        8 => 618,
        9 => 613,
        10 => 619,
        11 => 620,
        12 => 621,
        13 => 623,
        14 => 622,
        15 => 629,
        16 => 615,
        17 => 616,
        18 => 627,
        19 => 634,
        20 => 624,
        21 => 632,
        22 => 631,
        23 => 635,
        24 => 633,
        25 => 630,
        26 => 626,
        27 => 636,
    ];

    public function __construct(
        FilesystemOperator $filesystem,
        Filesystem $localFilesystem,
        MountManager $mountManager,
        EntityManagerInterface $em,
        TransformStringHelper $transformStringHelper,
        private readonly ImageDataFixtureNameFactory $imageDataFixtureNameFactory,
        private readonly string $dataFixturesResourcesDirectory,
        private readonly string $targetImagesDirectory,
        private readonly string $targetDomainImagesDirectory,
        private readonly string $targetFileManagerUploadDirectory,
    ) {
        parent::__construct($filesystem, $localFilesystem, $mountManager, $em, $transformStringHelper);
    }

    #[Override]
    public function load(ObjectManager $manager): void
    {
        $this->truncateDatabaseTables([self::IMAGES_TABLE_NAME, self::IMAGES_TRANSLATIONS_TABLE_NAME]);

        if (!file_exists($this->dataFixturesResourcesDirectory)) {
            return;
        }

        $this->moveFilesFromLocalFilesystemToFilesystem(
            $this->dataFixturesResourcesDirectory . '/domain/',
            $this->targetDomainImagesDirectory . '/',
        );
        $this->moveFilesFromLocalFilesystemToFilesystem(
            $this->dataFixturesResourcesDirectory . '/images/',
            $this->targetImagesDirectory,
        );
        $this->moveFilesFromLocalFilesystemToFilesystem(
            $this->dataFixturesResourcesDirectory . '/wysiwyg/',
            $this->targetFileManagerUploadDirectory . '/',
        );
        $this->processDbImagesChanges();
    }

    private function processDbImagesChanges(): void
    {
        $this->processBrandsImages();
        $this->processCategoriesImages();
        $this->processPaymentsImages();
        $this->processTransportsImages();
        $this->processTransportGroupsImages();
        $this->processProductsImages();
        $this->processSliderItemsImages();
        $this->processAdvertImages();
        $this->processStoresImages();
        $this->processBlogCategoryImages();
        $this->processBlogArticleImages();
        $this->processBlogArticleAuthorImages();
        $this->processSalesRepresentativeImages();

        $this->syncDatabaseSequences(['images.id']);
    }

    private function processBrandsImages(): void
    {
        $brandsImagesData = [
            79 => BrandDataFixture::BRAND_APPLE,
            80 => BrandDataFixture::BRAND_CANON,
            81 => BrandDataFixture::BRAND_LG,
            82 => BrandDataFixture::BRAND_PHILIPS,
            83 => BrandDataFixture::BRAND_SENCOR,
            84 => BrandDataFixture::BRAND_A4TECH,
            85 => BrandDataFixture::BRAND_BROTHER,
            86 => BrandDataFixture::BRAND_VERBATIM,
            87 => BrandDataFixture::BRAND_DLINK,
            88 => BrandDataFixture::BRAND_DEFENDER,
            89 => BrandDataFixture::BRAND_DELONGHI,
            90 => BrandDataFixture::BRAND_GENIUS,
            91 => BrandDataFixture::BRAND_GIGABYTE,
            92 => BrandDataFixture::BRAND_HP,
            93 => BrandDataFixture::BRAND_HTC,
            94 => BrandDataFixture::BRAND_JURA,
            95 => BrandDataFixture::BRAND_LOGITECH,
            96 => BrandDataFixture::BRAND_MICROSOFT,
            97 => BrandDataFixture::BRAND_SAMSUNG,
            98 => BrandDataFixture::BRAND_SONY,
            99 => BrandDataFixture::BRAND_ORAVA,
            100 => BrandDataFixture::BRAND_OLYMPUS,
            101 => BrandDataFixture::BRAND_HYUNDAI,
            102 => BrandDataFixture::BRAND_NIKON,
        ];

        foreach ($brandsImagesData as $imageId => $brandName) {
            $brand = $this->getReference($brandName, Brand::class);

            $names = [];

            foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
                $names[$locale] = $brandName;
            }

            $this->saveImageIntoDb($brand->getId(), 'brand', $imageId, $names);
        }
    }

    private function processCategoriesImages(): void
    {
        $categoriesImagesData = [
            68 => CategoryDataFixture::CATEGORY_ELECTRONICS,
            69 => CategoryDataFixture::CATEGORY_TV,
            70 => CategoryDataFixture::CATEGORY_PHOTO,
            71 => CategoryDataFixture::CATEGORY_PRINTERS,
            72 => CategoryDataFixture::CATEGORY_PC,
            73 => CategoryDataFixture::CATEGORY_PHONES,
            74 => CategoryDataFixture::CATEGORY_COFFEE,
            75 => CategoryDataFixture::CATEGORY_BOOKS,
            76 => CategoryDataFixture::CATEGORY_TOYS,
            77 => CategoryDataFixture::CATEGORY_GARDEN_TOOLS,
            78 => CategoryDataFixture::CATEGORY_FOOD,
        ];

        foreach ($categoriesImagesData as $imageId => $categoryName) {
            $category = $this->getReference($categoryName, Category::class);

            $names = [];

            foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
                $names[$locale] = $categoryName;
            }

            $this->saveImageIntoDb($category->getId(), 'category', $imageId, $names);
        }
    }

    private function processPaymentsImages(): void
    {
        $paymentsImagesData = [
            701 => PaymentDataFixture::PAYMENT_CARD,
            702 => PaymentDataFixture::PAYMENT_CASH_ON_DELIVERY,
            703 => PaymentDataFixture::PAYMENT_CASH,
            704 => PaymentDataFixture::PAYMENT_GOPAY_CARD,
            705 => PaymentDataFixture::PAYMENT_GOPAY_BANK_ACCOUNT,
            706 => PaymentDataFixture::PAYMENT_LATER,
            707 => PaymentDataFixture::PAYMENT_BANK_TRANSFER,
        ];

        foreach ($paymentsImagesData as $imageId => $paymentName) {
            $payment = $this->getReference($paymentName, Payment::class);

            $names = [];

            foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
                $names[$locale] = $payment->getName($locale) ?? $paymentName;
            }

            $this->saveImageIntoDb(
                $payment->getId(),
                'payment',
                $imageId,
                $names,
                null,
                Image::DEFAULT_IMAGE_POSITION,
                self::IMAGE_TYPE_PNG,
            );
        }
    }

    private function processTransportsImages(): void
    {
        $transportsImagesData = [
            711 => TransportDataFixture::TRANSPORT_CZECH_POST,
            712 => TransportDataFixture::TRANSPORT_PPL,
            713 => TransportDataFixture::TRANSPORT_PERSONAL,
            714 => TransportDataFixture::TRANSPORT_DRONE,
            715 => TransportDataFixture::TRANSPORT_PACKETERY,
        ];

        foreach ($transportsImagesData as $imageId => $transportName) {
            $transport = $this->getReference($transportName, Transport::class);

            $names = [];

            foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
                $names[$locale] = $transport->getName($locale) ?? $transportName;
            }

            $this->saveImageIntoDb(
                $transport->getId(),
                'transport',
                $imageId,
                $names,
                null,
                Image::DEFAULT_IMAGE_POSITION,
                self::IMAGE_TYPE_PNG,
            );
        }
    }

    private function processTransportGroupsImages(): void
    {
        $transportGroupsImagesData = [
            721 => TransportGroupDataFixture::TRANSPORT_GROUP_DELIVERY_TO_ADDRESS,
            722 => TransportGroupDataFixture::TRANSPORT_GROUP_PICKUP_POINT,
        ];

        foreach ($transportGroupsImagesData as $imageId => $transportGroupReferenceName) {
            $transportGroup = $this->getReference($transportGroupReferenceName, TransportGroup::class);

            $names = [];

            foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
                $names[$locale] = $transportGroup->getName($locale);
            }

            $this->saveImageIntoDb(
                $transportGroup->getId(),
                'transportGroup',
                $imageId,
                $names,
                null,
                Image::DEFAULT_IMAGE_POSITION,
                self::IMAGE_TYPE_PNG,
            );
        }
    }

    private function processProductsImages(): void
    {
        $productsIdsWithImageIdSameAsProductId = [
            1, 2, 3, 4, 5, 6, 7, 8, 9, 10,
            11, 12, 13, 14, 15, 16, 17, 18,
            19, 20, 21, 22, 24, 25, 26, 27,
            28, 29, 30, 31, 32, 33, 35, 36,
            37, 38, 39, 41, 42, 43, 44, 45,
            46, 47, 48, 49, 51, 52,
        ];

        $specificProductsIdsIndexedByImagesIds = [
            64 => 1,
            67 => 5,
            107 => 70,
            108 => 71,
        ];

        $positions = [];

        foreach ($productsIdsWithImageIdSameAsProductId as $productId) {
            $positions[$productId] = 0;
            $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . $productId, Product::class);
            $this->saveImageIntoDb(
                $productId,
                'product',
                $productId,
                $this->imageDataFixtureNameFactory->createProductImageNames(
                    $product,
                    false,
                    $positions[$productId],
                ),
                null,
                $positions[$productId],
            );
        }

        foreach ($specificProductsIdsIndexedByImagesIds as $imageId => $productId) {
            $positions[$productId] = array_key_exists($productId, $positions) ? ++$positions[$productId] : 0;
            $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . $productId, Product::class);
            $this->saveImageIntoDb(
                $productId,
                'product',
                $imageId,
                $this->imageDataFixtureNameFactory->createProductImageNames(
                    $product,
                    $imageId === 64,
                    $positions[$productId],
                ),
                null,
                $positions[$productId],
            );
        }
    }

    private function processSliderItemsImages(): void
    {
        $imagesIdsIndexedBySliderItemsIds = [
            1 => 650,
            2 => 651,
            3 => 652,
            4 => 656,
            5 => 657,
            6 => 658,
            7 => 662,
            8 => 663,
            9 => 664,
        ];

        foreach ($imagesIdsIndexedBySliderItemsIds as $sliderItemId => $imageId) {
            $names = $this->imageDataFixtureNameFactory->createSliderItemImageNames($sliderItemId);
            $this->saveImageIntoDb($sliderItemId, 'sliderItem', $imageId, $names, SliderItemFacade::IMAGE_TYPE_WEB);
        }

        // mobile version
        $imagesIdsIndexedBySliderItemsIds = [
            1 => 653,
            2 => 654,
            3 => 655,
            4 => 659,
            5 => 660,
            6 => 661,
            7 => 665,
            8 => 666,
            9 => 667,
        ];

        foreach ($imagesIdsIndexedBySliderItemsIds as $sliderItemId => $imageId) {
            $names = $this->imageDataFixtureNameFactory->createSliderItemImageNames($sliderItemId);
            $this->saveImageIntoDb($sliderItemId, 'sliderItem', $imageId, $names, SliderItemFacade::IMAGE_TYPE_MOBILE);
        }
    }

    private function processAdvertImages(): void
    {
        $this->processAdvertImagesByReferencePrefix(
            AdvertDataFixture::FOOTER_ADVERT_REFERENCE_PREFIX,
            727,
            730,
        );
        $this->processAdvertImagesByReferencePrefix(
            AdvertDataFixture::CATEGORY_ADVERT_REFERENCE_PREFIX,
            733,
            736,
        );
    }

    private function processAdvertImagesByReferencePrefix(
        string $advertReferencePrefix,
        int $webImageId,
        int $mobileImageId,
    ): void {
        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomains() as $domainConfig) {
            $advert = $this->getReference(
                $advertReferencePrefix . $domainConfig->getId(),
                Advert::class,
            );
            $names = [];

            foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
                $names[$locale] = $advert->getName();
            }

            $this->saveImageIntoDb(
                $advert->getId(),
                'noticer',
                $webImageId++,
                $names,
                AdvertFacade::IMAGE_TYPE_WEB,
                extension: self::IMAGE_TYPE,
            );
            $this->saveImageIntoDb(
                $advert->getId(),
                'noticer',
                $mobileImageId++,
                $names,
                AdvertFacade::IMAGE_TYPE_MOBILE,
                extension: self::IMAGE_TYPE,
            );
        }
    }

    private function processStoresImages(): void
    {
        $storesImagesData = [
            300,
            301,
            302,
        ];

        foreach ($storesImagesData as $imageId) {
            $store = $this->getReference(StoreDataFixture::STORE_PREFIX . '1', Store::class);
            $names = [];

            foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
                $names[$locale] = sprintf('%s - %s', $store->getName(), $store->getDescription());
            }

            $this->saveImageIntoDb($store->getId(), 'store', $imageId, $names);
        }
    }

    private function processBlogArticleImages(): void
    {
        $blogArticle = $this->getReference(BlogArticleDataFixture::FIRST_DEMO_BLOG_ARTICLE, BlogArticle::class);
        $names = [];

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
            $names[$locale] = sprintf('%s', $blogArticle->getName($locale));
        }

        $this->saveImageIntoDb($blogArticle->getId(), 'blogArticle', 602, $names);

        foreach (self::DEMO_BLOG_ARTICLE_IMAGE_IDS_BY_ARTICLE_NUMBER as $articleNumber => $imageId) {
            $blogArticle = $this->em->getRepository(BlogArticle::class)->findOneBy([
                'uuid' => BlogArticleDataFixture::getDemoBlogArticleUuid($articleNumber),
            ]);

            if (!$blogArticle instanceof BlogArticle) {
                throw new LogicException(sprintf('Demo blog article %d not found', $articleNumber));
            }

            $names = [];

            foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
                $names[$locale] = sprintf('%s', $blogArticle->getName($locale));
            }

            $this->saveImageIntoDb($blogArticle->getId(), 'blogArticle', $imageId, $names);
        }
    }

    private function processBlogArticleAuthorImages(): void
    {
        $blogArticleAuthorsImagesData = [
            BlogArticleAuthorDataFixture::BLOG_ARTICLE_AUTHOR_1 => 725,
            BlogArticleAuthorDataFixture::BLOG_ARTICLE_AUTHOR_2 => 726,
        ];

        foreach ($blogArticleAuthorsImagesData as $blogArticleAuthorReferenceName => $imageId) {
            $blogArticleAuthor = $this->getReference($blogArticleAuthorReferenceName, BlogArticleAuthor::class);

            $names = [];

            foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
                $names[$locale] = $blogArticleAuthor->getName();
            }

            $this->saveImageIntoDb($blogArticleAuthor->getId(), 'blogArticleAuthor', $imageId, $names);
        }
    }

    private function processSalesRepresentativeImages(): void
    {
        $salesRepresentativesImagesData = [
            SalesRepresentativeDataFixture::SALES_REPRESENTATIVE_1 => 739,
            SalesRepresentativeDataFixture::SALES_REPRESENTATIVE_2 => 740,
        ];

        foreach ($salesRepresentativesImagesData as $salesRepresentativeReferenceName => $imageId) {
            $salesRepresentative = $this->getReference(
                $salesRepresentativeReferenceName,
                SalesRepresentative::class,
            );

            $names = [];

            foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
                $names[$locale] = $salesRepresentative->getFullName();
            }

            $this->saveImageIntoDb($salesRepresentative->getId(), 'salesRepresentative', $imageId, $names);
        }
    }

    private function processBlogCategoryImages(): void
    {
        $blogCategoryImagesData = [
            BlogArticleDataFixture::FIRST_DEMO_BLOG_CATEGORY => 500,
            BlogArticleDataFixture::FIRST_DEMO_BLOG_SUBCATEGORY => 501,
        ];

        foreach ($blogCategoryImagesData as $blogCategoryReferenceName => $imageId) {
            $blogCategory = $this->getReference($blogCategoryReferenceName, BlogCategory::class);

            $names = [];

            foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
                $names[$locale] = $blogCategory->getName($locale);
            }

            $this->saveImageIntoDb($blogCategory->getId(), 'blogCategory', $imageId, $names);
        }
    }

    /**
     * @param array<string, string|null> $names
     */
    private function saveImageIntoDb(
        int $entityId,
        string $entityName,
        int $imageId,
        array $names = [],
        ?string $type = null,
        int $position = Image::DEFAULT_IMAGE_POSITION,
        string $extension = self::IMAGE_TYPE,
    ): void {
        $imageFilepath = $this->targetImagesDirectory
            . $entityName
            . ($type !== null ? '/' . $type : '')
            . '/'
            . $imageId
            . '.'
            . $extension;
        $filesize = $this->filesystem->has($imageFilepath) ? $this->filesystem->fileSize($imageFilepath) : null;

        $this->em->getConnection()->executeStatement(
            'INSERT INTO images (id, entity_name, entity_id, type, extension, position, modified_at, filesize)
            VALUES (:id, :entity_name, :entity_id, :type, :extension, :position, :modified_at, :filesize)',
            [
                'id' => $imageId,
                'entity_name' => $entityName,
                'entity_id' => $entityId,
                'type' => $type,
                'extension' => $extension,
                'position' => $position,
                'modified_at' => new DatePoint('2015-04-16 11:36:06'),
                'filesize' => $filesize,
            ],
            [
                'id' => Types::INTEGER,
                'entity_name' => Types::STRING,
                'entity_id' => Types::INTEGER,
                'type' => Types::STRING,
                'extension' => Types::STRING,
                'position' => Types::INTEGER,
                'modified_at' => Types::DATETIME_IMMUTABLE,
                'filesize' => Types::INTEGER,
            ],
        );

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
            $this->em->getConnection()->executeStatement(
                'INSERT INTO images_translations ( translatable_id, name, locale)
                VALUES (:translatable_id, :name, :locale)',
                [
                    'translatable_id' => $imageId,
                    'name' => $names[$locale] ?? null,
                    'locale' => $locale,
                ],
                [
                    'translatable_id' => Types::INTEGER,
                    'name' => Types::STRING,
                    'locale' => Types::STRING,
                ],
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getDependencies(): array
    {
        return [
            AdvertDataFixture::class,
            BlogArticleAuthorDataFixture::class,
            BlogArticleDataFixture::class,
            BrandDataFixture::class,
            CategoryDataFixture::class,
            PaymentDataFixture::class,
            SalesRepresentativeDataFixture::class,
            TransportDataFixture::class,
            TransportGroupDataFixture::class,
            ProductDataFixture::class,
            SliderItemDataFixture::class,
        ];
    }
}
