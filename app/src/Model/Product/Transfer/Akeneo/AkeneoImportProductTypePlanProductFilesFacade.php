<?php

declare(strict_types=1);

namespace App\Model\Product\Transfer\Akeneo;

use App\Component\Akeneo\Transfer\AbstractAkeneoImportTransfer;
use App\Component\Akeneo\Transfer\AkeneoImportTransferDependency;
use App\Component\Akeneo\Transfer\MediaFiles\MediaFilesTransferAkeneoFacade;
use App\Model\Product\Product;
use App\Model\Product\ProductRepository;
use App\Model\Product\Transfer\Akeneo\Exception\FileSaveFailedException;
use Generator;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Throwable;

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
     * @var \League\Flysystem\FilesystemOperator
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
     * @param \League\Flysystem\FilesystemOperator $localFilesystem
     */
    public function __construct(
        string $productFilesDir,
        AkeneoImportTransferDependency $akeneoImportTransferDependency,
        ProductRepository $productRepository,
        MediaFilesTransferAkeneoFacade $mediaFilesTransferAkeneoFacade,
        FilesystemOperator $localFilesystem
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
    protected function getData(): Generator
    {
        foreach ($this->productRepository->getProductsWithoutProductTypePlanFilesIterator() as $row) {
            $this->product = $row[0];
            $akeneoDataPerDomain = [];
            /** @var \App\Model\Product\ProductDomain $productDomain */
            foreach ($this->product->getProductDomains() as $productDomain) {
                if ($productDomain->getProductTypePlanCode()) {
                    $this->logger->info(sprintf('Getting data from API for media file : %s', $productDomain->getProductTypePlanCode()));

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
            $this->logger->info('File was successfully stored.');
        } catch (FilesystemException $exception) {
            try {
                $this->filesystem->delete($this->getFullPathWithName($fileName));
            } catch (FilesystemException $exception) {
            }

            $this->storeFile($fileName, $content);
        } catch (Throwable $exception) {
            throw new FileSaveFailedException($exception->getMessage(), $this->productFilesDir, $fileName, 0, $exception);
        }
    }

    /**
     * @param string $fileName
     */
    private function removeFile(string $fileName): void
    {
        try {
            $this->filesystem->delete($this->getFullPathWithName($fileName));
        } catch (FilesystemException $exception) {
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
        $this->logger->info('Transfer media file data from Akeneo ...');
    }

    protected function doAfterTransfer(): void
    {
        $this->logger->info('Transfer is done.');
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
