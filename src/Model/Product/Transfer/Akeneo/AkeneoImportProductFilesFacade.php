<?php

declare(strict_types=1);

namespace App\Model\Product\Transfer\Akeneo;

use App\Component\Akeneo\Transfer\MediaFiles\AkeneoImportMediaFilesFacade;
use App\Model\Product\Product;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;

class AkeneoImportProductFilesFacade
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductRepository
     */
    private $productRepository;

    /**
     * @var \App\Component\Akeneo\Transfer\MediaFiles\AkeneoImportMediaFilesFacade
     */
    private $akeneoImportMediaFilesFacade;

    /**
     * @var string
     */
    private $productFilesDir;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @param string $productFilesDir
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductRepository $productRepository
     * @param \App\Component\Akeneo\Transfer\MediaFiles\AkeneoImportMediaFilesFacade $akeneoImportMediaFilesFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        string $productFilesDir,
        ProductRepository $productRepository,
        AkeneoImportMediaFilesFacade $akeneoImportMediaFilesFacade,
        Domain $domain,
        EntityManagerInterface $em
    ) {
        $this->productRepository = $productRepository;
        $this->akeneoImportMediaFilesFacade = $akeneoImportMediaFilesFacade;
        $this->productFilesDir = $productFilesDir;
        $this->domain = $domain;
        $this->em = $em;
    }

    public function runTransfer()
    {
        $this->downloadAssemblyInstructionFiles();
        $this->downloadProductTypePlanFiles();
    }

    private function downloadAssemblyInstructionFiles(): void
    {
        $products = $this->productRepository->getProductsWithoutAssemblyInstructionFiles();

        /** @var \App\Model\Product\Product $product */
        foreach ($products as $product) {
            foreach ($this->domain->getAllIds() as $domainId) {
                if($product->getAssemblyInstructionCode($domainId)){
                    $this->importProductFile(
                        $product->getAssemblyInstructionCode($domainId),
                        $this->getAssemblyInstructionFilename($product, $domainId)
                    );
                }
            }
            $product->setAssemblyInstruction(true);
        }


        $this->em->flush();
    }

    private function downloadProductTypePlanFiles(): void
    {
        $products = $this->productRepository->getProductsWithoutProductTypePlanFiles();

        /** @var \App\Model\Product\Product $product */
        foreach ($products as $product) {
            foreach ($this->domain->getAllIds() as $domainId) {
                if($product->getProductTypePlanCode($domainId) !== null){
                    $this->importProductFile(
                        $product->getProductTypePlanCode($domainId),
                        $this->getProductTypePlanFilename($product, $domainId)
                    );
                }
            }
            $product->setProductTypePlan(true);
        }

        $this->em->flush();
    }

    /**
     * @param string $code
     * @param string $fileName
     */
    private function importProductFile(string $code, string $fileName): void
    {
        $this->akeneoImportMediaFilesFacade->downloadMediaFile($code, $this->productFilesDir, $fileName);
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @return string
     */
    public function getAssemblyInstructionFilename(Product $product, int $domainId): string
    {
        return $product->getAssemblyInstructionCode($domainId) ?? '';
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @return string
     */
    public function getProductTypePlanFilename(Product $product, int $domainId): string
    {
        return $product->getProductTypePlanCode($domainId) ?? '';
    }
}
