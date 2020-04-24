<?php

declare(strict_types=1);

namespace App\Model\Product\Transfer\Akeneo;

use App\Component\Akeneo\Transfer\AbstractAkeneoImportTransfer;
use App\Component\Akeneo\Transfer\AkeneoImportTransferDependency;
use App\Component\Image\Image;
use App\Component\Image\ImageFacade;
use App\Component\Setting\Setting;
use App\Model\Product\Parameter\ParameterFacade;
use App\Model\Product\Parameter\Transfer\Akeneo\AkeneoImportProductGroupParameterFacade;
use App\Model\Product\Parameter\Transfer\Akeneo\AkeneoImportProductParameterFacade;
use App\Model\Product\Product;
use App\Model\Product\ProductFacade;
use App\Model\Product\Series\ProductSeriesFacade;
use App\Model\Product\Series\ProductSeriesProductFacade;
use App\Model\Product\Series\Transfer\Akeneo\AkeneoImportProductSeriesFacade;
use DateTime;
use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade;

class AkeneoImportProductFacade extends AbstractAkeneoImportTransfer
{
    private const AKENEO_IMAGES_KEYS = [
        'image_main',
        'image_dimensions',
        'image_inspiration',
    ];

    private const AKENEO_IMAGES_TYPE_POSITION = [
        'image_main' => 0,
        'image_dimensions' => 1,
        'image_inspiration' => 2,
    ];

    /**
     * @var \App\Component\Setting\Setting
     */
    protected $setting;

    /**
     * @var \App\Model\Product\Transfer\Akeneo\ProductTransferAkeneoFacade
     */
    private $productTransferAkeneoFacade;

    /**
     * @var \App\Model\Product\Transfer\Akeneo\ProductTransferAkeneoValidator
     */
    private $productTransferAkeneoValidator;

    /**
     * @var \App\Model\Product\Transfer\Akeneo\ProductTransferAkeneoMapper
     */
    private $productTransferAkeneoMapper;

    /**
     * @var \App\Model\Product\ProductFacade
     */
    private $productFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade
     */
    private $productVisibilityFacade;

    /**
     * @var \DateTime|null
     */
    private $lastProductUpdatedAtFromAkeneo;

    /**
     * @var \App\Model\Product\Parameter\Transfer\Akeneo\AkeneoImportProductParameterFacade
     */
    private $akeneoImportProductParameterFacade;

    /**
     * @var \App\Model\Product\Parameter\Transfer\Akeneo\AkeneoImportProductGroupParameterFacade
     */
    private $akeneoImportProductGroupParameterFacade;

    /**
     * @var \App\Model\Product\Parameter\ParameterFacade
     */
    private $parameterFacade;

    /**
     * @var \App\Model\Product\Series\ProductSeriesProductFacade
     */
    private $productSeriesProductFacade;

    /**
     * @var \App\Model\Product\Series\ProductSeriesFacade
     */
    private $productSeriesFacade;

    /**
     * @var \App\Model\Product\Series\Transfer\Akeneo\AkeneoImportProductSeriesFacade
     */
    private $akeneoImportProductSeriesFacade;

    /**
     * @var \App\Component\Image\ImageFacade
     */
    private $imageFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\FileUpload\FileUpload
     */
    private $fileUpload;

    /**
     * @param \App\Component\Akeneo\Transfer\AkeneoImportTransferDependency $akeneoImportTransferDependency
     * @param \App\Model\Product\Transfer\Akeneo\ProductTransferAkeneoFacade $productTransferAkeneoFacade
     * @param \App\Model\Product\Transfer\Akeneo\ProductTransferAkeneoValidator $productTransferAkeneoValidator
     * @param \App\Model\Product\Transfer\Akeneo\ProductTransferAkeneoMapper $productTransferAkeneoMapper
     * @param \App\Model\Product\ProductFacade $productFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade $productVisibilityFacade
     * @param \App\Component\Setting\Setting $setting
     * @param \App\Model\Product\Parameter\Transfer\Akeneo\AkeneoImportProductParameterFacade $akeneoImportProductParameterFacade
     * @param \App\Model\Product\Parameter\Transfer\Akeneo\AkeneoImportProductGroupParameterFacade $akeneoImportProductGroupParameterFacade
     * @param \App\Model\Product\Parameter\ParameterFacade $parameterFacade
     * @param \App\Model\Product\Series\ProductSeriesProductFacade $productSeriesProductFacade
     * @param \App\Model\Product\Series\ProductSeriesFacade $productSeriesFacade
     * @param \App\Model\Product\Series\Transfer\Akeneo\AkeneoImportProductSeriesFacade $akeneoImportProductSeriesFacade
     * @param \App\Component\Image\ImageFacade $imageFacade
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\FileUpload $fileUpload
     */
    public function __construct(
        AkeneoImportTransferDependency $akeneoImportTransferDependency,
        ProductTransferAkeneoFacade $productTransferAkeneoFacade,
        ProductTransferAkeneoValidator $productTransferAkeneoValidator,
        ProductTransferAkeneoMapper $productTransferAkeneoMapper,
        ProductFacade $productFacade,
        ProductVisibilityFacade $productVisibilityFacade,
        Setting $setting,
        AkeneoImportProductParameterFacade $akeneoImportProductParameterFacade,
        AkeneoImportProductGroupParameterFacade $akeneoImportProductGroupParameterFacade,
        ParameterFacade $parameterFacade,
        ProductSeriesProductFacade $productSeriesProductFacade,
        ProductSeriesFacade $productSeriesFacade,
        AkeneoImportProductSeriesFacade $akeneoImportProductSeriesFacade,
        ImageFacade $imageFacade,
        FileUpload $fileUpload
    ) {
        parent::__construct($akeneoImportTransferDependency);

        $this->productTransferAkeneoFacade = $productTransferAkeneoFacade;
        $this->productTransferAkeneoValidator = $productTransferAkeneoValidator;
        $this->productTransferAkeneoMapper = $productTransferAkeneoMapper;
        $this->productFacade = $productFacade;
        $this->productVisibilityFacade = $productVisibilityFacade;
        $this->setting = $setting;
        $this->akeneoImportProductParameterFacade = $akeneoImportProductParameterFacade;
        $this->akeneoImportProductGroupParameterFacade = $akeneoImportProductGroupParameterFacade;
        $this->parameterFacade = $parameterFacade;
        $this->productSeriesProductFacade = $productSeriesProductFacade;
        $this->productSeriesFacade = $productSeriesFacade;
        $this->akeneoImportProductSeriesFacade = $akeneoImportProductSeriesFacade;
        $this->imageFacade = $imageFacade;
        $this->fileUpload = $fileUpload;
    }

    /**
     * @return \Generator
     */
    protected function getData(): \Generator
    {
        $lastProductsUpdatedAt = $this->setting->get(Setting::AKENEO_TRANSFER_PRODUCTS_LAST_UPDATED_DATETIME);

        $this->lastProductUpdatedAtFromAkeneo = $lastProductsUpdatedAt;

        $this->logger->addInfo(sprintf('Getting data from API for search greater than last updated : %s', $lastProductsUpdatedAt->format(DATE_ATOM)));

        foreach ($this->productTransferAkeneoFacade->getAllUpdatedProductsFromLastUpdate($lastProductsUpdatedAt) as $product) {
            yield $product;
        }
    }

    protected function doBeforeTransfer(): void
    {
        $this->logger->addInfo('Transfer products data from Akeneo ...');

        $akeneoProductsData = $this->getData();

        $allProductSeriesCodes = [];
        $isAllParametersImported = true;
        foreach ($akeneoProductsData as $akeneoProductData) {
            if ($isAllParametersImported === true) {
                $isAllParametersImported = $this->checkIsAllParametersExistFromAkeneoData($akeneoProductData);
            }

            $allProductSeriesCodes = array_merge(
                $allProductSeriesCodes,
                $this->productTransferAkeneoMapper->mapAkeneoProductDataToProductSeriesCodeList($akeneoProductData)
            );
        }

        if ($isAllParametersImported === false) {
            $this->logger->addInfo('Transfer lost parameters from Akeneo');
            $this->akeneoImportProductGroupParameterFacade->runTransfer();
            $this->akeneoImportProductParameterFacade->runTransfer();
        }

        if ($this->checkIsAllProductSeriesImported($allProductSeriesCodes) === false) {
            $this->logger->addInfo('Transfer missing Product Series from Akeneo');
            $this->akeneoImportProductSeriesFacade->runTransfer();
        }
    }

    /**
     * @param array $akeneoProductData
     */
    protected function processItem($akeneoProductData): void
    {
        $this->productTransferAkeneoValidator->validate($akeneoProductData);

        if($akeneoProductData['identifier'] == 413608301){
            d($akeneoProductData);
        }


        $product = $this->productFacade->findOneByCatnumExcludeMainVariants($akeneoProductData['identifier']);
        $productData = $this->productTransferAkeneoMapper->mapAkeneoProductDataToProductData($akeneoProductData, $product);

        if ($product === null) {
            $this->logger->addInfo(sprintf('Creating product catnum: %s', $productData->catnum));
            $product = $this->productFacade->create($productData);
        } else {
            $this->logger->addInfo(sprintf('Updating product catnum: %s', $product->getCatnum()));
            $product = $this->productFacade->edit($product->getId(), $productData);
        }

        $this->setProductForImportFiles($product, $akeneoProductData);
        $this->setRelationProductSeriesWithProduct($product, $akeneoProductData);
        $this->setProductImages($product, $akeneoProductData);
        $this->setProductPackageDetailInformationFormProduct($product, $akeneoProductData);

        $this->setLastUpdatedProduct($akeneoProductData['updated']);
    }

    protected function doAfterTransfer(): void
    {
        //$this->setting->set(Setting::AKENEO_TRANSFER_PRODUCTS_LAST_UPDATED_DATETIME, $this->lastProductUpdatedAtFromAkeneo);
        $this->logger->addInfo('Transfer is done.');
        $this->productVisibilityFacade->refreshProductsVisibilityForMarked();
    }

    /**
     * @param string $lastUpdated
     */
    private function setLastUpdatedProduct(string $lastUpdated): void
    {
        $lastUpdatedDateTime = new DateTime($lastUpdated);

        if ($lastUpdatedDateTime > $this->lastProductUpdatedAtFromAkeneo) {
            $this->lastProductUpdatedAtFromAkeneo = $lastUpdatedDateTime;
        }
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
    private function setProductForImportFiles(Product $product, array $akeneoProductData): void
    {
        $productFilesData = $this->productTransferAkeneoMapper->mapAkeneoProductDataToProductFilesData($akeneoProductData, $product);
        $this->productFacade->editProductFileAttributes($product, $productFilesData);
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param array $akeneoProductData
     */
    private function setProductPackageDetailInformationFormProduct(Product $product, array $akeneoProductData): void
    {

    }

    /**
     * @return string
     */
    public function getTransferIdentifier(): string
    {
        return 'productTransfer';
    }

    /**
     * @return string
     */
    public function getTransferName(): string
    {
        return t('Přenos produktů');
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
    private function checkIsAllProductSeriesImported(array $allProductSeriesCodes): bool
    {
        $storedAkeneoCodes = $this->productSeriesFacade->findProductSeriesCodesWithAkeneoCode();
        $difference = array_diff($allProductSeriesCodes, $storedAkeneoCodes);

        if (count($difference) > 0) {
            return false;
        }

        return true;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param array $akeneoProductData
     */
    private function setProductImages(Product $product, array $akeneoProductData): void
    {
        $processedAkeneoImageKeyType = [];

        foreach (self::AKENEO_IMAGES_KEYS as $akeneoImageKeyType) {
            if (array_key_exists($akeneoImageKeyType, $akeneoProductData['values'])) {
                $akeneoMediaFileData = current($akeneoProductData['values'][$akeneoImageKeyType]);
                $processedAkeneoImageKeyType[] = $akeneoImageKeyType;

                $oldImage = $this->imageFacade->findImageByEntityForAkeneoImageType($product, $akeneoImageKeyType);
                if ($oldImage === null) {
                    $this->createProductImage($product, $akeneoMediaFileData, $akeneoImageKeyType);
                    continue;
                }

                if ($oldImage->getAkeneoCode() !== $akeneoMediaFileData['data']) {
                    $this->imageFacade->deleteImages($product, [$oldImage]);
                    $this->createProductImage($product, $akeneoMediaFileData, $akeneoImageKeyType);
                }
            }
        }

        $akeneoImageKeyTypeForDelete = array_diff(self::AKENEO_IMAGES_KEYS, $processedAkeneoImageKeyType);
        foreach ($akeneoImageKeyTypeForDelete as $akeneoImageKeyType) {
            $oldImage = $this->imageFacade->findImageByEntityForAkeneoImageType($product, $akeneoImageKeyType);
            if ($oldImage !== null) {
                $this->imageFacade->deleteImages($product, [$oldImage]);
                $this->em->flush();
            }
        }

        if (count($processedAkeneoImageKeyType) > 0) {
            foreach (self::AKENEO_IMAGES_TYPE_POSITION as $akeneoImageKeyType => $position) {
                $image = $this->imageFacade->findImageByEntityForAkeneoImageType($product, $akeneoImageKeyType);
                if ($image !== null) {
                    $image->setPosition($position);
                    $this->em->flush();
                }
            }
        }
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param array $akeneoMediaFileData
     * @param string $akeneoImageType
     */
    private function createProductImage(Product $product, array $akeneoMediaFileData, string $akeneoImageType): void
    {
        $mediaFileResponse = $this->productTransferAkeneoFacade->getProductMediaFileFromApi($akeneoMediaFileData['data']);
        $akeneoMediaFileName = $akeneoMediaFileData['data'];

        $tempFileName = $this->fileUpload->getTemporaryFilepath($akeneoMediaFileName);
        $uploadDirectory = $this->fileUpload->getTemporaryDirectory();
        if (!is_dir($uploadDirectory)) {
            if (!mkdir($uploadDirectory) && !is_dir($uploadDirectory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $uploadDirectory));
            }
        }

        file_put_contents($tempFileName, $mediaFileResponse->getBody()->getContents());
        $createdImage = $this->imageFacade->uploadImage($product, [$akeneoMediaFileName], null, false);

        $this->em->clear(Image::class);

        $image = $this->imageFacade->getById($createdImage->getId());
        $image->setAkeneoCode($akeneoMediaFileName);
        $image->setAkeneoImageType($akeneoImageType);
        $this->em->flush();
    }
}
