<?php

declare(strict_types=1);

namespace App\Model\Product\Transfer\Akeneo;

use App\Component\Image\Config\ImageConfig;
use App\Component\Image\Image;
use App\Component\Image\ImageCacheFacade;
use App\Component\Image\ImageFacade;
use App\Model\Product\Package\ProductPackageFacade;
use App\Model\Product\Parameter\ParameterFacade;
use App\Model\Product\Product;
use App\Model\Product\ProductData;
use App\Model\Product\ProductFacade;
use App\Model\Product\Series\ProductSeriesFacade;
use App\Model\Product\Series\ProductSeriesProductFacade;
use App\Model\Transfer\TransferLoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemInterface;
use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;

class TransferredProductProcessor
{
    private const AKENEO_IMAGES_KEYS_WITH_SORTING_PRIORITY = [
        'image_main',
        'image_inspiration',
        'image_function',
        'image_internal_equipment',
        'image_galery',
        'image_upholstery_fabric',
        'image_dimensions',
    ];

    private const ASSET_FAMILY = 'Galerie';

    private const AKENEO_IMAGE_TYPE_GALLERY = 'image_galery';

    /**
     * @var \App\Model\Product\ProductFacade
     */
    private $productFacade;

    /**
     * @var \App\Model\Product\Transfer\Akeneo\ProductTransferAkeneoMapper
     */
    private $productTransferAkeneoMapper;

    /**
     * @var \App\Model\Product\Transfer\Akeneo\ProductTransferAkeneoValidator
     */
    private $productTransferAkeneoValidator;

    /**
     * @var \App\Model\Product\Series\ProductSeriesProductFacade
     */
    private $productSeriesProductFacade;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @var \App\Model\Product\Package\ProductPackageFacade
     */
    private $productPackageFacade;

    /**
     * @var \App\Component\Image\ImageFacade
     */
    private $imageFacade;

    /**
     * @var \App\Model\Product\Transfer\Akeneo\ProductTransferAkeneoFacade
     */
    private $productTransferAkeneoFacade;

    /**
     * @var \App\Model\Product\Transfer\Akeneo\AssetTransferAkeneoFacade
     */
    private $assetTransferAkeneoFacade;

    /**
     * @var \App\Component\FileUpload\FileUpload
     */
    private $fileUpload;

    /**
     * @var \App\Model\Product\Parameter\ParameterFacade
     */
    private $parameterFacade;

    /**
     * @var \App\Model\Product\Series\ProductSeriesFacade
     */
    private $productSeriesFacade;

    /**
     * @var \App\Component\Image\ImageCacheFacade
     */
    private $imageCacheFacade;

    /**
     * @var \App\Component\Image\Config\ImageConfig
     */
    private $imageConfig;

    /**
     * @var \League\Flysystem\FilesystemInterface
     */
    private $filesystem;

    /**
     * @param \App\Model\Product\ProductFacade $productFacade
     * @param \App\Model\Product\Transfer\Akeneo\ProductTransferAkeneoMapper $productTransferAkeneoMapper
     * @param \App\Model\Product\Transfer\Akeneo\ProductTransferAkeneoValidator $productTransferAkeneoValidator
     * @param \App\Model\Product\Series\ProductSeriesProductFacade $productSeriesProductFacade
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Product\Package\ProductPackageFacade $productPackageFacade
     * @param \App\Component\Image\ImageFacade $imageFacade
     * @param \App\Model\Product\Transfer\Akeneo\ProductTransferAkeneoFacade $productTransferAkeneoFacade
     * @param \App\Model\Product\Transfer\Akeneo\AssetTransferAkeneoFacade $assetTransferAkeneoFacade
     * @param \App\Component\FileUpload\FileUpload $fileUpload
     * @param \App\Model\Product\Parameter\ParameterFacade $parameterFacade
     * @param \App\Model\Product\Series\ProductSeriesFacade $productSeriesFacade
     * @param \App\Component\Image\Config\ImageConfig $imageConfig
     * @param \App\Component\Image\ImageCacheFacade $imageCacheFacade
     * @param \League\Flysystem\FilesystemInterface $filesystem
     */
    public function __construct(
        ProductFacade $productFacade,
        ProductTransferAkeneoMapper $productTransferAkeneoMapper,
        ProductTransferAkeneoValidator $productTransferAkeneoValidator,
        ProductSeriesProductFacade $productSeriesProductFacade,
        EntityManagerInterface $em,
        ProductPackageFacade $productPackageFacade,
        ImageFacade $imageFacade,
        ProductTransferAkeneoFacade $productTransferAkeneoFacade,
        AssetTransferAkeneoFacade $assetTransferAkeneoFacade,
        FileUpload $fileUpload,
        ParameterFacade $parameterFacade,
        ProductSeriesFacade $productSeriesFacade,
        ImageConfig $imageConfig,
        ImageCacheFacade $imageCacheFacade,
        FilesystemInterface $filesystem
    ) {
        $this->productFacade = $productFacade;
        $this->productTransferAkeneoMapper = $productTransferAkeneoMapper;
        $this->productTransferAkeneoValidator = $productTransferAkeneoValidator;
        $this->productSeriesProductFacade = $productSeriesProductFacade;
        $this->em = $em;
        $this->productPackageFacade = $productPackageFacade;
        $this->imageFacade = $imageFacade;
        $this->productTransferAkeneoFacade = $productTransferAkeneoFacade;
        $this->assetTransferAkeneoFacade = $assetTransferAkeneoFacade;
        $this->fileUpload = $fileUpload;
        $this->parameterFacade = $parameterFacade;
        $this->productSeriesFacade = $productSeriesFacade;
        $this->imageCacheFacade = $imageCacheFacade;
        $this->imageConfig = $imageConfig;
        $this->filesystem = $filesystem;
    }

    /**
     * @param array $akeneoProductData
     * @param \App\Model\Transfer\TransferLoggerInterface $logger
     * @param bool $isMainVariant
     * @return \App\Model\Product\Product
     */
    public function processProduct(array $akeneoProductData, TransferLoggerInterface $logger, bool $isMainVariant = false): Product
    {
        $this->productTransferAkeneoValidator->validate($akeneoProductData, $isMainVariant);

        $product = $this->findProductByIdentifier((string)$akeneoProductData['identifier'], $isMainVariant);
        if ($product !== null) {
            $entityName = $this->imageConfig->getEntityName($product);
            $entityId = $product->getId();
            $this->imageCacheFacade->invalidateCacheByEntityNameAndEntityIdAndType($entityName, $entityId, null);
        }
        $productData = $this->productTransferAkeneoMapper->mapAkeneoProductDataToProductData($akeneoProductData, $product);

        if ($product === null) {
            $product = $this->createProduct($productData, $isMainVariant, $logger);
        } else {
            $logger->addInfo(sprintf('Updating product catnum: %s', $product->getCatnum()));
            $product = $this->productFacade->edit($product->getId(), $productData);
        }

        $this->setProductForImportFiles($product, $akeneoProductData);
        $this->setRelationProductSeriesWithProduct($product, $akeneoProductData);
        $this->setProductImages($product, $akeneoProductData);
        $this->setProductPackageDetailInformationFormProduct($product, $akeneoProductData);
        $this->setProductAsVariant($product, $akeneoProductData, $isMainVariant);
        $this->setProductAsDefaultVariant($product, $akeneoProductData);

        return $product;
    }

    /**
     * @param array $akeneoProductDetailData
     * @param \App\Model\Transfer\TransferLoggerInterface $logger
     * @param bool $isMainVariant
     */
    public function processProductDetail(array $akeneoProductDetailData, TransferLoggerInterface $logger, bool $isMainVariant = false): void
    {
        $this->productTransferAkeneoValidator->validateIdentifier($akeneoProductDetailData);

        $product = $this->findProductByIdentifier((string)$akeneoProductDetailData['identifier'], $isMainVariant);
        if ($product !== null) {
            $this->setProductAccessoriesByAkeneoProductDetailData($product, $akeneoProductDetailData, $logger);
        }
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     * @param bool $isMainVariant
     * @param \App\Model\Transfer\TransferLoggerInterface $logger
     * @return \App\Model\Product\Product
     */
    private function createProduct(ProductData $productData, bool $isMainVariant, TransferLoggerInterface $logger): Product
    {
        if ($isMainVariant) {
            $logger->addInfo(sprintf('Creating product main variant catnum: %s', $productData->catnum));
            $product = $this->productFacade->createProductAsMainVariant($productData);
        } else {
            $logger->addInfo(sprintf('Creating product catnum: %s', $productData->catnum));
            $product = $this->productFacade->create($productData);
        }

        return $product;
    }

    /**
     * @param string $identifier
     * @param bool $isMainVariant
     * @return \App\Model\Product\Product|null
     */
    private function findProductByIdentifier(string $identifier, bool $isMainVariant): ?Product
    {
        if ($isMainVariant) {
            return $this->productFacade->findMainVariantByCatnum($identifier);
        } else {
            return $this->productFacade->findOneByCatnumExcludeMainVariants($identifier);
        }
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param array $akeneoProductData
     * @param bool $isMainVariant
     */
    private function setProductAsVariant(Product $product, array $akeneoProductData, bool $isMainVariant): void
    {
        if ($isMainVariant) {
            return;
        }

        if ($product->isVariant() || $product->isMainVariant()) {
            return;
        }

        $mainVariantCatnum = $this->productTransferAkeneoMapper->mapAkeneoProductDataToParentCatnum($akeneoProductData);
        if ($mainVariantCatnum === null) {
            return;
        }

        $mainVariantProduct = $this->findProductByIdentifier($mainVariantCatnum, true);
        if ($mainVariantProduct === null) {
            return;
        }

        $mainVariantProduct->addVariant($product);
        $this->em->flush();
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param array $akeneoProductData
     */
    private function setProductAsDefaultVariant(Product $product, array $akeneoProductData): void
    {
        if ($product->isMainVariant()) {
            return;
        }

        if (!$product->isVariant()) {
            return;
        }

        $mainVariantCatnum = $this->productTransferAkeneoMapper->mapAkeneoProductDataToParentCatnum($akeneoProductData);
        if ($mainVariantCatnum === null) {
            return;
        }

        $mainVariantProduct = $this->findProductByIdentifier($mainVariantCatnum, true);
        if ($mainVariantProduct === null) {
            return;
        }

        $defaultVariantCatnum = $this->productTransferAkeneoMapper->mapAkeneoProductDataToDefaultVariantCatnum($akeneoProductData);
        if ($defaultVariantCatnum !== null && $defaultVariantCatnum === $product->getCatnum()) {
            $this->productFacade->setDefaultVariant($mainVariantProduct, $product);
        }
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param array $akeneoProductDetailData
     * @param \App\Model\Transfer\TransferLoggerInterface $logger
     */
    private function setProductAccessoriesByAkeneoProductDetailData(Product $product, array $akeneoProductDetailData, TransferLoggerInterface $logger): void
    {
        $accessoryCatnums = $this->productTransferAkeneoMapper->getProductAccessoryCatnumListFromAkeneoProductData($akeneoProductDetailData);
        $accessories = $this->getAccessoriesByCatnums($accessoryCatnums);
        $this->productFacade->refreshProductAccessories($product, $accessories);
        $accessoriesCount = count($accessories);
        $logger->addInfo(sprintf('Refresh %s accessories for product catnum: %s', $accessoriesCount, $product->getCatnum()));
    }

    /**
     * @param string[] $catnums
     * @return \App\Model\Product\Product[]
     */
    private function getAccessoriesByCatnums(array $catnums): array
    {
        $accessories = [];
        foreach ($catnums as $catnum) {
            $product = $this->productFacade->findOneByCatnumExcludeMainVariants($catnum);
            if ($product !== null) {
                $accessories[] = $product;
            }
        }

        return $accessories;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param array $akeneoProductData
     */
    private function setProductForImportFiles(Product $product, array $akeneoProductData): void
    {
        $productFilesData = $this->productTransferAkeneoMapper->mapAkeneoProductDataToProductFilesData($akeneoProductData, $product);
        $this->productFacade->editProductFileAttributes($product, $productFilesData);
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param array $akeneoProductData
     */
    private function setRelationProductSeriesWithProduct(Product $product, array $akeneoProductData): void
    {
        $productSeriesCodeList = $this->productTransferAkeneoMapper->mapAkeneoProductDataToProductSeriesCodeList($akeneoProductData);
        $this->productSeriesProductFacade->editProductSeriesProductRelation($product, $productSeriesCodeList);
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param array $akeneoProductData
     */
    private function setProductPackageDetailInformationFormProduct(Product $product, array $akeneoProductData): void
    {
        $dontDropProductPositions = [];

        $productPackageDataList = $this->productTransferAkeneoMapper->mapAkeneoProductPackageDetailInformationToProductPackageDataList($akeneoProductData);
        foreach ($productPackageDataList as $productPackageData) {
            $this->productPackageFacade->createOrEdit($productPackageData, $product);
            $dontDropProductPositions[] = $productPackageData->position;
        }

        $productPackages = $this->productPackageFacade->getProductPackagesByProduct($product);
        $canFlush = false;
        foreach ($productPackages as $productPackage) {
            if (in_array($productPackage->getPosition(), $dontDropProductPositions, true) === false) {
                $this->em->remove($productPackage);
                $canFlush = true;
            }
        }

        if ($canFlush) {
            $this->em->flush();
        }
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param array $akeneoProductData
     */
    private function setProductImages(Product $product, array $akeneoProductData): void
    {
        $imagesCollection = [];
        $originalImages = [];
        $i = 0;

        foreach ($this->imageFacade->getAllImagesByEntity($product) as $image) {
            $originalImages[$image->getAkeneoCode()] = $image;
        }

        foreach (self::AKENEO_IMAGES_KEYS_WITH_SORTING_PRIORITY as $akeneoImageKeyType) {
            if ($akeneoImageKeyType === self::AKENEO_IMAGE_TYPE_GALLERY) {
                foreach ($this->getGalleryImagesForProduct($akeneoProductData) as $imageInfo) {
                    $importedImage = current($imageInfo['values']['media']);
                    if (isset($originalImages[$importedImage['data']])) {
                        $image = $originalImages[$importedImage['data']];
                        $imagesCollection[$i++] = $image;
                        unset($originalImages[$image->getAkeneoCode()]);
                    } else {
                        $imagesCollection[$i++] = array_merge($importedImage, ['type' => $akeneoImageKeyType]);
                    }
                }
            } elseif (array_key_exists($akeneoImageKeyType, $akeneoProductData['values'])) {
                $importedImage = current($akeneoProductData['values'][$akeneoImageKeyType]);
                if (isset($originalImages[$importedImage['data']])
                    && $originalImages[$importedImage['data']]->getAkeneoImageType() === $akeneoImageKeyType
                ) {
                    $image = $originalImages[$importedImage['data']];
                    $imagesCollection[$i++] = $image;
                    unset($originalImages[$image->getAkeneoCode()]);
                } else {
                    $imagesCollection[$i++] = array_merge($importedImage, ['type' => $akeneoImageKeyType]);
                }
            }
        }

        $this->imageFacade->deleteImages($product, $originalImages);

        foreach ($imagesCollection as $position => $image) {
            if (is_object($image) && $image instanceof Image) {
                $newImage = $this->imageFacade->getById($image->getId());
                $newImage->setPosition($position);
                $this->em->persist($newImage);
                $this->em->flush();
            } elseif (is_array($image)) {
                $this->createProductImage($product, $image, $image['type'], $position);
            }
        }
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param array $akeneoMediaFileData
     * @param string $akeneoImageType
     * @param int $position
     */
    private function createProductImage(Product $product, array $akeneoMediaFileData, string $akeneoImageType, int $position): void
    {
        if ($akeneoImageType === self::AKENEO_IMAGE_TYPE_GALLERY) {
            $mediaFileResponse = $this->assetTransferAkeneoFacade->getAssetMediaFileFromApi($akeneoMediaFileData['data']);
        } else {
            $mediaFileResponse = $this->productTransferAkeneoFacade->getProductMediaFileFromApi($akeneoMediaFileData['data']);
        }
        $akeneoMediaFileName = $akeneoMediaFileData['data'];

        $tempFileName = $this->fileUpload->getTemporaryFilepath($akeneoMediaFileName);

        $this->filesystem->put($tempFileName, $mediaFileResponse->getBody()->getContents());
        $createdImage = $this->imageFacade->uploadAndReturnImage($product, [$akeneoMediaFileName], null, false);

        $this->em->clear(Image::class);

        $image = $this->imageFacade->getById($createdImage->getId());
        $image->setAkeneoCode($akeneoMediaFileName);
        $image->setAkeneoImageType($akeneoImageType);
        $image->setPosition($position);
        $this->em->flush();
    }

    /**
     * @param array $akeneoProductData
     * @return \Generator
     */
    private function getGalleryImagesForProduct(array $akeneoProductData): \Generator
    {
        $imageCodes = $akeneoProductData['values']['image_galery'][0]['data'] ?? [];

        foreach ($imageCodes as $imageCode) {
            yield $this->assetTransferAkeneoFacade->getImageData(self::ASSET_FAMILY, $imageCode);
        }
    }

    /**
     * @param array $akeneoProductData
     * @return bool
     */
    public function checkIsAllParametersExistFromAkeneoData(array $akeneoProductData): bool
    {
        $akeneoProductParameters = $this->productTransferAkeneoMapper->getParametersFromAkeneoData($akeneoProductData);

        foreach ($akeneoProductParameters as $akeneoParameterCode => $parameterValue) {
            $parameter = $this->parameterFacade->findParameterByAkeneoCode($akeneoParameterCode);
            if ($parameter === null) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string[] $allProductSeriesCodes
     * @return bool
     */
    public function checkIsAllProductSeriesImported(array $allProductSeriesCodes): bool
    {
        $storedAkeneoCodes = $this->productSeriesFacade->findProductSeriesCodesWithAkeneoCode();
        $difference = array_diff($allProductSeriesCodes, $storedAkeneoCodes);

        if (count($difference) > 0) {
            return false;
        }

        return true;
    }
}
