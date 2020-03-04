<?php

declare(strict_types=1);

namespace App\Model\Product\Transfer\Akeneo;

use App\Component\Akeneo\Transfer\AbstractAkeneoImportTransfer;
use App\Component\Akeneo\Transfer\AkeneoImportTransferDependency;
use App\Component\Akeneo\Transfer\MediaFiles\MediaFilesTransferAkeneoFacade;
use League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\FilesystemInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;

class AkeneoImportProductFilesFacade extends AbstractAkeneoImportTransfer
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
    private $localFilesystem;

    /**
     * @var \App\Model\Product\ProductRepository
     */
    private $productRepository;

    /**
     * @var string
     */
    private $productFilesDir;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @param string $productFilesDir
     * @param \App\Component\Akeneo\Transfer\AkeneoImportTransferDependency $akeneoImportTransferDependency
     * @param \App\Model\Product\ProductRepository $productRepository
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Component\Akeneo\Transfer\MediaFiles\MediaFilesTransferAkeneoFacade $mediaFilesTransferAkeneoFacade
     * @param \League\Flysystem\FilesystemInterface $localFilesystem
     */
    public function __construct(
        string $productFilesDir,
        AkeneoImportTransferDependency $akeneoImportTransferDependency,
        ProductRepository $productRepository,
        Domain $domain,
        MediaFilesTransferAkeneoFacade $mediaFilesTransferAkeneoFacade,
        FilesystemInterface $localFilesystem
    ) {
        parent::__construct($akeneoImportTransferDependency);
        $this->productRepository = $productRepository;
        $this->productFilesDir = $productFilesDir;
        $this->domain = $domain;
        $this->mediaFilesTransferAkeneoFacade = $mediaFilesTransferAkeneoFacade;
        $this->localFilesystem = $localFilesystem;
    }

    /**
     * @return \Generator
     */
    protected function getData(): \Generator
    {

        foreach ($this->productRepository->getProductsWithoutAssemblyInstructionFilesIterator() as $row) {

            $this->product = $row[0];
            $akeneoDataPerDomain = [];
            /** @var \App\Model\Product\ProductDomain $productDomain */
            foreach ($this->product->getProductDomains() as $productDomain) {
                if ($productDomain->getAssemblyInstructionCode()) {
                    $akeneoDataPerDomain[$productDomain->getDomainId()] = $this->mediaFilesTransferAkeneoFacade
                        ->getProductMediaFile($productDomain->getAssemblyInstructionCode())
                        ->getBody()
                        ->getContents();

                    $this->logger->addInfo(sprintf('Getting data from API for media file : %s', $productDomain->getAssemblyInstructionCode()));
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
        foreach ($akeneoData as $domainId => $content){
            $this->storeFile($this->product->getProductFileNameByType($domainId, 'assemblyInstruction'), $content);
        }

        $this->product->setAssemblyInstruction(false);
        $this->em->flush();
    }

    /**
     * @param string $fileName
     * @param string $content
     */
    private function storeFile(string $fileName, string $content): void
    {
        $fullPathWithFileName = $this->productFilesDir . $fileName;
        try {
            $this->localFilesystem->write($fullPathWithFileName, $content);
            $this->logger->addInfo('File was successfully stored.');
        } catch (FileExistsException $exception) {
            try {
                $this->localFilesystem->delete($fullPathWithFileName);
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
        return 'productMediaFilesTransfer';
    }
}
