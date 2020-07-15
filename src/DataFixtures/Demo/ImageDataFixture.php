<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Slider\SliderItemFacade;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ObjectManager;
use League\Flysystem\FilesystemInterface;
use League\Flysystem\MountManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\String\TransformString;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class ImageDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    public const IMAGES_TABLE_NAME = 'images';

    public const IMAGE_TYPE = 'jpg';

    /**
     * @var string
     */
    private $dataFixturesImagesDirectory;

    /**
     * @var string
     */
    private $targetDomainImagesDirectory;

    /**
     * @var \League\Flysystem\FilesystemInterface
     */
    private $filesystem;

    /**
     * @var string
     */
    private $targetImagesDirectory;

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    private $localFilesystem;

    /**
     * @var \League\Flysystem\MountManager
     */
    private $mountManager;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @param mixed $dataFixturesImagesDirectory
     * @param mixed $targetImagesDirectory
     * @param mixed $targetDomainImagesDirectory
     * @param \League\Flysystem\FilesystemInterface $filesystem
     * @param \Symfony\Component\Filesystem\Filesystem $symfonyFilesystem
     * @param \League\Flysystem\MountManager $mountManager
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        $dataFixturesImagesDirectory,
        $targetImagesDirectory,
        $targetDomainImagesDirectory,
        FilesystemInterface $filesystem,
        Filesystem $symfonyFilesystem,
        MountManager $mountManager,
        EntityManagerInterface $em
    ) {
        $this->dataFixturesImagesDirectory = $dataFixturesImagesDirectory;
        $this->targetDomainImagesDirectory = $targetDomainImagesDirectory;
        $this->targetImagesDirectory = $targetImagesDirectory;
        $this->filesystem = $filesystem;
        $this->localFilesystem = $symfonyFilesystem;
        $this->mountManager = $mountManager;
        $this->em = $em;
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->truncateImagesFromDb();

        if (file_exists($this->dataFixturesImagesDirectory)) {
            $this->moveFilesFromLocalFilesystemToFilesystem($this->dataFixturesImagesDirectory . 'domain/', $this->targetDomainImagesDirectory . '/');
            $this->moveFilesFromLocalFilesystemToFilesystem($this->dataFixturesImagesDirectory, $this->targetImagesDirectory);
            $this->processDbImagesChanges();
        }
    }

    private function processDbImagesChanges()
    {
        $this->processBrandsImages();
        $this->processCategoriesImages();
        $this->processPaymentsImages();
        $this->processTransportsImages();
        $this->processProductsImages();
        $this->processSliderItemsImages();
        $this->processProductSeriesImages();
        $this->restartImagesIdsDbSequence();
    }

    private function processBrandsImages()
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
            /** @var \App\Model\Product\Brand\Brand $brand */
            $brand = $this->getReference($brandName);

            $this->saveImageIntoDb($brand->getId(), 'brand', $imageId);
        }
    }

    private function processCategoriesImages()
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
            /** @var \App\Model\Category\Category $category */
            $category = $this->getReference($categoryName);

            $this->saveImageIntoDb($category->getId(), 'category', $imageId);
        }
    }

    private function processPaymentsImages()
    {
        $paymentsImagesData = [
            53 => PaymentDataFixture::PAYMENT_CARD,
            55 => PaymentDataFixture::PAYMENT_CASH_ON_DELIVERY,
            54 => PaymentDataFixture::PAYMENT_CASH,
        ];

        foreach ($paymentsImagesData as $imageId => $paymentName) {
            /** @var \App\Model\Payment\Payment $payment */
            $payment = $this->getReference($paymentName);

            $this->saveImageIntoDb($payment->getId(), 'payment', $imageId);
        }
    }

    private function processTransportsImages()
    {
        $transportsImagesData = [
            56 => TransportDataFixture::TRANSPORT_CZECH_POST,
            57 => TransportDataFixture::TRANSPORT_PPL,
            58 => TransportDataFixture::TRANSPORT_PERSONAL,
        ];

        foreach ($transportsImagesData as $imageId => $transportName) {
            /** @var \App\Model\Transport\Transport $transport */
            $transport = $this->getReference($transportName);

            $this->saveImageIntoDb($transport->getId(), 'transport', $imageId);
        }
    }

    private function processProductsImages()
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

        $maxImageId = 109;
        for ($productId = 53; $productId <= 153; $productId++) {
            if (in_array($productId, [70, 71], true)) {
                continue;
            }

            $specificProductsIdsIndexedByImagesIds[$maxImageId] = $productId;
            $maxImageId++;
        }

        foreach ($productsIdsWithImageIdSameAsProductId as $productId) {
            $this->saveImageIntoDb($productId, 'product', $productId, null, 'image_main');
        }

        foreach ($specificProductsIdsIndexedByImagesIds as $maxImageId => $productId) {
            $this->saveImageIntoDb($productId, 'product', $maxImageId, null, 'image_main');
        }
    }

    private function processSliderItemsImages()
    {
        $imagesIdsIndexedBySliderItemsIds = [
            1 => 59,
            2 => 60,
            3 => 61,
        ];

        foreach ($imagesIdsIndexedBySliderItemsIds as $sliderItemId => $imageId) {
            $this->saveImageIntoDb($sliderItemId, 'sliderItem', $imageId, SliderItemFacade::IMAGE_TYPE_WEB);
        }

        //mobile version
        $imagesIdsIndexedBySliderItemsIds = [
            1 => 103,
            2 => 104,
            3 => 105,
        ];

        foreach ($imagesIdsIndexedBySliderItemsIds as $sliderItemId => $imageId) {
            $this->saveImageIntoDb($sliderItemId, 'sliderItem', $imageId, SliderItemFacade::IMAGE_TYPE_MOBILE);
        }
    }

    private function processProductSeriesImages(): void
    {
        $productSeriesData = [
            106 => ProductSeriesDataFixture::PRODUCT_SERIES_YENNEFER,
        ];

        foreach ($productSeriesData as $imageId => $productSeriesName) {
            /** @var \App\Model\Product\Series\ProductSeries $productSeries */
            $productSeries = $this->getReference($productSeriesName);

            $this->saveImageIntoDb($productSeries->getId(), 'productSeries', $imageId);
        }
    }

    /**
     * @param int $entityId
     * @param string $entityName
     * @param int $imageId
     * @param string|null $type
     * @param string|null $akeneoImageType
     */
    private function saveImageIntoDb(int $entityId, string $entityName, int $imageId, ?string $type = null, ?string $akeneoImageType = null)
    {
        $query = $this->em->createNativeQuery(
            'INSERT INTO images (id, entity_name, entity_id, type, extension, position, modified_at, processed_by_kraken, akeneo_image_type)
            VALUES (:id, :entity_name, :entity_id, :type, :extension, :position, :modified_at, :processed_by_kraken, :akeneo_image_type)',
            new ResultSetMapping()
        );

        $query->execute([
            'id' => $imageId,
            'entity_name' => $entityName,
            'entity_id' => $entityId,
            'type' => $type,
            'extension' => self::IMAGE_TYPE,
            'position' => null,
            'modified_at' => '2015-04-16 11:36:06',
            'processed_by_kraken' => true,
            'akeneo_image_type' => $akeneoImageType,
        ]);
    }

    /**
     * @param string $origin
     * @param string $target
     */
    private function moveFilesFromLocalFilesystemToFilesystem(string $origin, string $target)
    {
        $finder = new Finder();
        $finder->files()->in($origin);
        foreach ($finder as $file) {
            $filepath = TransformString::removeDriveLetterFromPath($file->getPathname());

            if ($this->localFilesystem->exists($filepath)) {
                $newFilepath = $target . $file->getRelativePathname();

                if ($this->filesystem->has($newFilepath)) {
                    $this->filesystem->delete($newFilepath);
                }
                $this->mountManager->copy('local://' . $filepath, 'main://' . $newFilepath);
            }
        }
    }

    private function truncateImagesFromDb()
    {
        $this->em->createNativeQuery('TRUNCATE TABLE ' . self::IMAGES_TABLE_NAME, new ResultSetMapping())->execute();
    }

    private function restartImagesIdsDbSequence()
    {
        $this->em->createNativeQuery('ALTER SEQUENCE images_id_seq RESTART WITH 208', new ResultSetMapping())->execute();
    }

    /**
     * {@inheritDoc}
     */
    public function getDependencies()
    {
        return [
            BrandDataFixture::class,
            CategoryDataFixture::class,
            PaymentDataFixture::class,
            TransportDataFixture::class,
            ProductDataFixture::class,
            SliderItemDataFixture::class,
            ProductSeriesDataFixture::class,
        ];
    }
}
