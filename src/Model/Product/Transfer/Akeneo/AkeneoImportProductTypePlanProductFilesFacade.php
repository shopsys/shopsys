<?php

declare(strict_types=1);

namespace App\Model\Product\Transfer\Akeneo;

use App\Component\Akeneo\Transfer\AbstractAkeneoImportTransfer;
use App\Component\Akeneo\Transfer\AkeneoImportTransferDependency;
use App\Component\Akeneo\Transfer\MediaFiles\MediaFilesTransferAkeneoFacade;
use App\Model\Product\Product;
use App\Model\Product\ProductRepository;
use League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\FilesystemInterface;

class AkeneoImportProductTypePlanProductFilesFacade extends AbstractAkeneoImportTransfer
{
    protected const DS = DIRECTORY_SEPARATOR;

    /**
     * @var \App\Component\Akeneo\Transfer\MediaFiles\MediaFilesTransferAkeneoFacade
     */
    private $mediaFilesTransferAkeneoFacade;

    /**
     * @var \App\Model\Product\Product|null
     */
    private $product;

    /**
     * @var \League\Flysystem\FilesystemInterface
     */
    private $filesystem;

    /**
     * @var \App\Model\Product\ProductRepository
     */
    private $productRepository;

    /**
     * @var string
     */
    private $productFilesDir;

    /**
     * @param string $productFilesDir
     * @param \App\Component\Akeneo\Transfer\AkeneoImportTransferDependency $akeneoImportTransferDependency
     * @param \App\Model\Product\ProductRepository $productRepository
     * @param \App\Component\Akeneo\Transfer\MediaFiles\MediaFilesTransferAkeneoFacade $mediaFilesTransferAkeneoFacade
     * @param \League\Flysystem\FilesystemInterface $localFilesystem
     */
    public function __construct(
        string $productFilesDir,
        AkeneoImportTransferDependency $akeneoImportTransferDependency,
        ProductRepository $productRepository,
        MediaFilesTransferAkeneoFacade $mediaFilesTransferAkeneoFacade,
        FilesystemInterface $localFilesystem
    ) {
        parent::__construct($akeneoImportTransferDependency);
        $this->productRepository = $productRepository;
        $this->productFilesDir = $productFilesDir;
        $this->mediaFilesTransferAkeneoFacade = $mediaFilesTransferAkeneoFacade;
        $this->filesystem = $localFilesystem;
    }

    /**
     * @return \Generator
     */
    protected function getData(): \Generator
    {
        foreach ($this->productRepository->getProductsWithoutProductTypePlanFilesIterator() as $row) {
            $this->product = $row[0];
            $akeneoDataPerDomain = [];
            /** @var \App\Model\Product\ProductDomain $productDomain */
            foreach ($this->product->getProductDomains() as $productDomain) {
                if ($productDomain->getProductTypePlanCode()) {
                    $this->logger->addInfo(sprintf('Getting data from API for media file : %s', $productDomain->getProductTypePlanCode()));

                    $akeneoDataPerDomain[$productDomain->getDomainId()] = $this->mediaFilesTransferAkeneoFacade
                        ->getProductMediaFile($productDomain->getProductTypePlanCode())
                        ->getBody()
                        ->getContents();
                } else {
                    $akeneoDataPerDomain[$productDomain->getDomainId()] = null;
                }
            }

            yield $akeneoDataPerDomain;
        }
    }

    /**
     * @param array $akeneoData
     */
    protected function processItem($akeneoData): void
    {
        foreach ($akeneoData as $domainId => $content) {
            if ($content !== null) {
                $this->storeFile($this->product->getProductFileNameByType($domainId, Product::FILE_IDENTIFICATOR_PRODUCT_TYPE_PLAN_TYPE), $content);
            } else {
                $this->removeFile($this->product->getProductFileNameByType($domainId, Product::FILE_IDENTIFICATOR_PRODUCT_TYPE_PLAN_TYPE));
            }
        }

        $this->product->setDownloadProductTypePlanFiles(false);
        $this->em->flush();
    }

    /**
     * @param string $fileName
     * @param string $content
     */
    private function storeFile(string $fileName, string $content): void
    {
        try {
            $this->filesystem->write($this->getFullPathWithName($fileName), $content);
            $this->logger->addInfo('File was successfully stored.');
        } catch (FileExistsException $exception) {
            try {
                $this->filesystem->delete($this->getFullPathWithName($fileName));
            } catch (FileNotFoundException $exception) {
            }

            $this->storeFile($fileName, $content);
        } catch (\Throwable $exception) {
            $this->logger->addError('File save failed', [
                'reason' => $exception->getMessage(),
                'dictionary' => $this->productFilesDir,
                'filename' => $fileName,
            ]);
        }
    }

    /**
     * @param string $fileName
     */
    private function removeFile(string $fileName): void
    {
        try {
            $this->filesystem->delete($this->getFullPathWithName($fileName));
        } catch (FileNotFoundException $exception) {
        }
    }

    /**
     * @param string $fileName
     * @return string
     */
    private function getFullPathWithName(string $fileName): string
    {
        return $this->productFilesDir . $fileName;
    }

    protected function doBeforeTransfer(): void
    {
        $this->logger->addInfo('Transfer media file data from Akeneo ...');
    }

    protected function doAfterTransfer(): void
    {
        $this->logger->addInfo('Transfer is done.');
    }

    /**
     * @return string
     */
    public function getTransferIdentifier(): string
    {
        return 'productTypePlanMediaFilesTransfer';
    }

    /**
     * @return string
     */
    public function getTransferName(): string
    {
        return t('přenos souborů "Typový plán"');
    }
}
