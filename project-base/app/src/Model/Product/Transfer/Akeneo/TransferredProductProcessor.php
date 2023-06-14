<?php

declare(strict_types=1);

namespace App\Model\Product\Transfer\Akeneo;

use App\Component\FileUpload\FileUpload;
use App\Component\Image\Image;
use App\Component\Image\ImageFacade;
use App\Model\Product\Parameter\ParameterFacade;
use App\Model\Product\Product;
use App\Model\Product\ProductData;
use App\Model\Product\ProductFacade;
use App\Model\Transfer\TransferLoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Generator;
use League\Flysystem\FilesystemOperator;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig;

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
     * @param \App\Model\Product\ProductFacade $productFacade
     * @param \App\Model\Product\Transfer\Akeneo\ProductTransferAkeneoMapper $productTransferAkeneoMapper
     * @param \App\Model\Product\Transfer\Akeneo\ProductTransferAkeneoValidator $productTransferAkeneoValidator
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Component\Image\ImageFacade $imageFacade
     * @param \App\Model\Product\Transfer\Akeneo\ProductTransferAkeneoFacade $productTransferAkeneoFacade
     * @param \App\Model\Product\Transfer\Akeneo\AssetTransferAkeneoFacade $assetTransferAkeneoFacade
     * @param \App\Component\FileUpload\FileUpload $fileUpload
     * @param \App\Model\Product\Parameter\ParameterFacade $parameterFacade
     * @param \Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig $imageConfig
     * @param \League\Flysystem\FilesystemOperator $filesystem
     */
    public function __construct(
        private readonly ProductFacade $productFacade,
        private readonly ProductTransferAkeneoMapper $productTransferAkeneoMapper,
        private readonly ProductTransferAkeneoValidator $productTransferAkeneoValidator,
        private readonly EntityManagerInterface $em,
        private readonly ImageFacade $imageFacade,
        private readonly ProductTransferAkeneoFacade $productTransferAkeneoFacade,
        private readonly AssetTransferAkeneoFacade $assetTransferAkeneoFacade,
        private readonly FileUpload $fileUpload,
        private readonly ParameterFacade $parameterFacade,
        private readonly ImageConfig $imageConfig,
        private readonly FilesystemOperator $filesystem,
    ) {
    }

    /**
     * @param array $akeneoProductData
     * @param \App\Model\Transfer\TransferLoggerInterface $logger
     * @return \App\Model\Product\Product
     */
    public function processProduct(array $akeneoProductData, TransferLoggerInterface $logger): Product
    {
        $this->productTransferAkeneoValidator->validate($akeneoProductData);

        $product = $this->findProductByIdentifier((string)$akeneoProductData['identifier']);

        if ($product !== null) {
            $entityName = $this->imageConfig->getEntityName($product);
            $entityId = $product->getId();
            $this->imageFacade->invalidateCacheByEntityNameAndEntityIdAndType($entityName, $entityId, null);
        }
        $productData = $this->productTransferAkeneoMapper->mapAkeneoProductDataToProductData($akeneoProductData, $product, $logger);

        if ($product === null) {
            $product = $this->createProduct($productData, $logger);
        } else {
            $logger->info(sprintf('Updating product catnum: %s', $product->getCatnum()));
            $product = $this->productFacade->edit($product->getId(), $productData);
        }

        $this->setProductForImportFiles($product, $akeneoProductData);
        $this->setProductImages($product, $akeneoProductData);

        return $product;
    }

    /**
     * @param array $akeneoProductDetailData
     * @param \App\Model\Transfer\TransferLoggerInterface $logger
     */
    public function processProductDetail(array $akeneoProductDetailData, TransferLoggerInterface $logger): void
    {
        $this->productTransferAkeneoValidator->validateIdentifier($akeneoProductDetailData);

        $product = $this->findProductByIdentifier((string)$akeneoProductDetailData['identifier']);

        if ($product !== null) {
            $this->setProductAccessoriesByAkeneoProductDetailData($product, $akeneoProductDetailData, $logger);
        }
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     * @param \App\Model\Transfer\TransferLoggerInterface $logger
     * @return \App\Model\Product\Product
     */
    private function createProduct(ProductData $productData, TransferLoggerInterface $logger): Product
    {
        $logger->info(sprintf('Creating product catnum: %s', $productData->catnum));

        return $this->productFacade->create($productData);
    }

    /**
     * @param string $identifier
     * @return \App\Model\Product\Product|null
     */
    private function findProductByIdentifier(string $identifier): ?Product
    {
        return $this->productFacade->findOneByCatnumExcludeMainVariants($identifier);
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param array $akeneoProductDetailData
     * @param \App\Model\Transfer\TransferLoggerInterface $logger
     */
    private function setProductAccessoriesByAkeneoProductDetailData(
        Product $product,
        array $akeneoProductDetailData,
        TransferLoggerInterface $logger,
    ): void {
        $accessoryCatnums = $this->productTransferAkeneoMapper->getProductAccessoryCatnumListFromAkeneoProductData($akeneoProductDetailData);
        $accessories = $this->getAccessoriesByCatnums($accessoryCatnums);
        $this->productFacade->refreshProductAccessories($product, $accessories);
        $accessoriesCount = count($accessories);
        $logger->info(sprintf('Refresh %s accessories for product catnum: %s', $accessoriesCount, $product->getCatnum()));
    }

    /**
     * @param string[] $catnums
     * @return \App\Model\Product\Product[]
     */
    private function getAccessoriesByCatnums(array $catnums): array
    {
        $accessories = [];

        foreach ($catnums as $catnum) {
            $product = $this->productFacade->findByCatnum($catnum);

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
    private function createProductImage(
        Product $product,
        array $akeneoMediaFileData,
        string $akeneoImageType,
        int $position,
    ): void {
        if ($akeneoImageType === self::AKENEO_IMAGE_TYPE_GALLERY) {
            $mediaFileResponse = $this->assetTransferAkeneoFacade->getAssetMediaFileFromApi($akeneoMediaFileData['data']);
        } else {
            $mediaFileResponse = $this->productTransferAkeneoFacade->getProductMediaFileFromApi($akeneoMediaFileData['data']);
        }
        $akeneoMediaFileName = $akeneoMediaFileData['data'];

        $tempFileName = $this->fileUpload->getTemporaryFilepath($akeneoMediaFileName);

        $this->filesystem->write($tempFileName, $mediaFileResponse->getBody()->getContents());
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
    private function getGalleryImagesForProduct(array $akeneoProductData): Generator
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

        foreach (array_keys($akeneoProductParameters) as $akeneoParameterCode) {
            $parameter = $this->parameterFacade->findParameterByAkeneoCode($akeneoParameterCode);

            if ($parameter === null) {
                return false;
            }
        }

        return true;
    }
}
