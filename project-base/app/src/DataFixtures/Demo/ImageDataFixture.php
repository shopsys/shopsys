<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Category\Category;
use App\Model\Payment\Payment;
use App\Model\Product\Brand\Brand;
use App\Model\Slider\SliderItemFacade;
use App\Model\Transport\Transport;
use DateTimeImmutable;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\MountManager;
use Shopsys\FrameworkBundle\Component\Image\Image;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory;
use Shopsys\FrameworkBundle\Model\Store\Store;
use Symfony\Component\Filesystem\Filesystem;

class ImageDataFixture extends AbstractFileFixture implements DependentFixtureInterface
{
    public const string IMAGES_TABLE_NAME = 'images';
    public const string IMAGES_TRANSLATIONS_TABLE_NAME = 'images_translations';
    public const string IMAGE_TYPE = 'jpg';

    /**
     * @param \League\Flysystem\FilesystemOperator $filesystem
     * @param \Symfony\Component\Filesystem\Filesystem $localFilesystem
     * @param \League\Flysystem\MountManager $mountManager
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param string $dataFixturesImagesDirectory
     * @param string $targetImagesDirectory
     * @param string $targetDomainImagesDirectory
     */
    public function __construct(
        FilesystemOperator $filesystem,
        Filesystem $localFilesystem,
        MountManager $mountManager,
        EntityManagerInterface $em,
        private readonly string $dataFixturesImagesDirectory,
        private readonly string $targetImagesDirectory,
        private readonly string $targetDomainImagesDirectory,
    ) {
        parent::__construct($filesystem, $localFilesystem, $mountManager, $em);
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $this->truncateDatabaseTables([self::IMAGES_TABLE_NAME, self::IMAGES_TRANSLATIONS_TABLE_NAME]);

        if (!file_exists($this->dataFixturesImagesDirectory)) {
            return;
        }

        $this->moveFilesFromLocalFilesystemToFilesystem(
            $this->dataFixturesImagesDirectory . 'domain/',
            $this->targetDomainImagesDirectory . '/',
        );
        $this->moveFilesFromLocalFilesystemToFilesystem(
            $this->dataFixturesImagesDirectory,
            $this->targetImagesDirectory,
        );
        $this->processDbImagesChanges();
    }

    private function processDbImagesChanges(): void
    {
        $this->processBrandsImages();
        $this->processCategoriesImages();
        $this->processPaymentsImages();
        $this->processTransportsImages();
        $this->processProductsImages();
        $this->processSliderItemsImages();
        $this->processStoresImages();
        $this->processMainBlogCategoryImage();

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
            53 => PaymentDataFixture::PAYMENT_CARD,
            54 => PaymentDataFixture::PAYMENT_CASH,
            55 => PaymentDataFixture::PAYMENT_CASH_ON_DELIVERY,
        ];

        foreach ($paymentsImagesData as $imageId => $paymentName) {
            $payment = $this->getReference($paymentName, Payment::class);

            $names = [];

            foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
                $names[$locale] = $paymentName;
            }

            $this->saveImageIntoDb($payment->getId(), 'payment', $imageId, $names);
        }
    }

    private function processTransportsImages(): void
    {
        $transportsImagesData = [
            56 => TransportDataFixture::TRANSPORT_CZECH_POST,
            57 => TransportDataFixture::TRANSPORT_PPL,
            58 => TransportDataFixture::TRANSPORT_PERSONAL,
        ];

        foreach ($transportsImagesData as $imageId => $transportName) {
            $transport = $this->getReference($transportName, Transport::class);

            $names = [];

            foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
                $names[$locale] = $transportName;
            }

            $this->saveImageIntoDb($transport->getId(), 'transport', $imageId, $names);
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
            $names = [];

            foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
                $names[$locale] = 'Product ' . $productId . ' image';
            }

            $positions[$productId] = 0;
            $this->saveImageIntoDb($productId, 'product', $productId, $names, null, $positions[$productId]);
        }

        foreach ($specificProductsIdsIndexedByImagesIds as $imageId => $productId) {
            $names = [];

            foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
                $names[$locale] = 'Product ' . $productId . ' image';
            }

            $positions[$productId] = array_key_exists($productId, $positions) ? ++$positions[$productId] : 0;
            $this->saveImageIntoDb($productId, 'product', $imageId, $names, null, $positions[$productId]);
        }
    }

    private function processSliderItemsImages(): void
    {
        $imagesIdsIndexedBySliderItemsIds = [
            1 => 59,
            2 => 60,
            3 => 61,
            4 => 208,
            5 => 209,
            6 => 210,
        ];

        foreach ($imagesIdsIndexedBySliderItemsIds as $sliderItemId => $imageId) {
            $names = [];

            foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
                $names[$locale] = 'Slider item ' . $sliderItemId . ' image';
            }

            $this->saveImageIntoDb($sliderItemId, 'sliderItem', $imageId, $names, SliderItemFacade::IMAGE_TYPE_WEB);
        }

        //mobile version
        $imagesIdsIndexedBySliderItemsIds = [
            1 => 103,
            2 => 104,
            3 => 105,
            4 => 211,
            5 => 212,
            6 => 213,
        ];

        foreach ($imagesIdsIndexedBySliderItemsIds as $sliderItemId => $imageId) {
            $names = [];

            foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
                $names[$locale] = 'Slider item ' . $sliderItemId . ' image';
            }

            $this->saveImageIntoDb($sliderItemId, 'sliderItem', $imageId, $names, SliderItemFacade::IMAGE_TYPE_MOBILE);
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

    private function processMainBlogCategoryImage(): void
    {
        $mainBlogCategoryImageId = 500;

        $mainBlogCategory = $this->getReference(BlogArticleDataFixture::FIRST_DEMO_BLOG_CATEGORY, BlogCategory::class);
        $names = [];

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
            $names[$locale] = $mainBlogCategory->getName($locale);
        }

        $this->saveImageIntoDb($mainBlogCategory->getId(), 'blogCategory', $mainBlogCategoryImageId, $names);
    }

    /**
     * @param int $entityId
     * @param string $entityName
     * @param int $imageId
     * @param array $names
     * @param string|null $type
     * @param int $position
     */
    private function saveImageIntoDb(
        int $entityId,
        string $entityName,
        int $imageId,
        array $names = [],
        ?string $type = null,
        int $position = Image::DEFAULT_IMAGE_POSITION,
    ): void {
        $this->em->getConnection()->executeStatement(
            'INSERT INTO images (id, entity_name, entity_id, type, extension, position, modified_at)
            VALUES (:id, :entity_name, :entity_id, :type, :extension, :position, :modified_at)',
            [
                'id' => $imageId,
                'entity_name' => $entityName,
                'entity_id' => $entityId,
                'type' => $type,
                'extension' => self::IMAGE_TYPE,
                'position' => $position,
                'modified_at' => new DateTimeImmutable('2015-04-16 11:36:06'),
            ],
            [
                'id' => Types::INTEGER,
                'entity_name' => Types::STRING,
                'entity_id' => Types::INTEGER,
                'type' => Types::STRING,
                'extension' => Types::STRING,
                'position' => Types::INTEGER,
                'modified_at' => Types::DATETIME_IMMUTABLE,
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
    public function getDependencies(): array
    {
        return [
            BrandDataFixture::class,
            CategoryDataFixture::class,
            PaymentDataFixture::class,
            TransportDataFixture::class,
            ProductDataFixture::class,
            SliderItemDataFixture::class,
        ];
    }
}
