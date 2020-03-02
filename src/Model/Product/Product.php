<?php

declare(strict_types=1);

namespace App\Model\Product;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Product\Product as BaseProduct;
use Shopsys\FrameworkBundle\Model\Product\ProductData as BaseProductData;

/**
 * @ORM\Table(name="products")
 * @ORM\Entity
 * @property \App\Model\Product\Brand\Brand|null $brand
 * @property \App\Model\Product\Product[]|\Doctrine\Common\Collections\Collection $variants
 * @property \App\Model\Product\Product|null $mainVariant
 * @method static \App\Model\Product\Product create(\App\Model\Product\ProductData $productData)
 * @method static \App\Model\Product\Product createMainVariant(\App\Model\Product\ProductData $productData, \App\Model\Product\Product[] $variants)
 * @method \App\Model\Category\Category[][] getCategoriesIndexedByDomainId()
 * @method \App\Model\Product\Brand\Brand|null getBrand()
 * @method \App\Model\Product\Product getMainVariant()
 * @method \App\Model\Product\Product[] getVariants()
 * @method setAvailabilityAndStock(\App\Model\Product\ProductData $productData)
 * @method addVariant(\App\Model\Product\Product $variant)
 * @method addVariants(\App\Model\Product\Product[] $variants)
 * @method setMainVariant(\App\Model\Product\Product $mainVariant)
 * @method refreshVariants(\App\Model\Product\Product[] $currentVariants)
 * @method addNewVariants(\App\Model\Product\Product[] $currentVariants)
 * @method unsetRemovedVariants(\App\Model\Product\Product[] $currentVariants)
 * @method translation(?string $locale = null): ProductTranslation
 * @property \App\Model\Product\ProductTranslation[]|\Doctrine\Common\Collections\Collection $translations
 * @property \App\Model\Product\ProductDomain[]|\Doctrine\Common\Collections\Collection $domains
 * @method \App\Model\Product\ProductDomain getProductDomain(int $domainId)
 */
class Product extends BaseProduct
{
    public const PDF_SUFFIX = '.pdf';
    public const FILE_IDENTIFICATOR_ASSEMBLY_INSTRUCTION_TYPE = 'assemblyInstruction';
    public const FILE_IDENTIFICATOR_PRODUCT_TYPE_PLAN_TYPE = 'productTypePlan';

    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $downloadAssemblyInstructionFiles;

    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $downloadProductTypePlanFiles;

    /**
     * @param \App\Model\Product\ProductData $productData
     * @param \App\Model\Product\Product[]|null $variants
     */
    protected function __construct(ProductData $productData, ?array $variants = null)
    {
        parent::__construct($productData, $variants);
        $this->downloadAssemblyInstructionFiles = $productData->assemblyInstruction;
        $this->downloadProductTypePlanFiles = $productData->productTypePlan;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductCategoryDomain[] $productCategoryDomains
     * @param \App\Model\Product\ProductData $productData
     */
    public function edit(array $productCategoryDomains, BaseProductData $productData)
    {
        parent::edit($productCategoryDomains, $productData);
        $this->downloadAssemblyInstructionFiles = $productData->assemblyInstruction;
        $this->downloadProductTypePlanFiles = $productData->productTypePlan;
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     */
    protected function setTranslations(BaseProductData $productData)
    {
        parent::setTranslations($productData);

        foreach ($productData->namePrefix as $locale => $namePrefix) {
            $this->translation($locale)->setNamePrefix($namePrefix);
        }
        foreach ($productData->nameSufix as $locale => $nameSufix) {
            $this->translation($locale)->setNameSufix($nameSufix);
        }
    }

    /**
     * @return \App\Model\Product\ProductTranslation
     */
    protected function createTranslation()
    {
        return new ProductTranslation();
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     */
    protected function setDomains(BaseProductData $productData): void
    {
        parent::setDomains($productData);

        foreach ($this->domains as $productDomain) {
            $domainId = $productDomain->getDomainId();
            $productDomain->setShortDescriptionUsp1($productData->shortDescriptionUsp1[$domainId]);
            $productDomain->setShortDescriptionUsp2($productData->shortDescriptionUsp2[$domainId]);
            $productDomain->setShortDescriptionUsp3($productData->shortDescriptionUsp3[$domainId]);
            $productDomain->setShortDescriptionUsp4($productData->shortDescriptionUsp4[$domainId]);
            $productDomain->setShortDescriptionUsp5($productData->shortDescriptionUsp5[$domainId]);
            $productDomain->setLowPriceWithVat($productData->lowPriceWithVat[$domainId]);
            $productDomain->setHighPriceWithVat($productData->highPriceWithVat[$domainId]);
            $productDomain->setAssemblyInstructionCode($productData->assemblyInstructionCode[$domainId]);
            $productDomain->setProductTypePlanCode($productData->productTypePlanCode[$domainId]);
        }
    }

    /**
     * @param \App\Model\Product\ProductFilesData $productFilesData
     */
    public function editFileAttributes(ProductFilesData $productFilesData): void
    {
        foreach ($this->domains as $productDomain) {
            $domainId = $productDomain->getDomainId();
            if ($this->getAssemblyInstructionCode($domainId) !== $productFilesData->assemblyInstructionCode[$domainId]) {
                $productDomain->setAssemblyInstructionCode($productFilesData->assemblyInstructionCode[$domainId]);
                $this->setDownloadAssemblyInstructionFiles(true);
            }

            if ($this->getProductTypePlanCode($domainId) !== $productFilesData->productTypePlanCode[$domainId]) {
                $productDomain->setProductTypePlanCode($productFilesData->productTypePlanCode[$domainId]);
                $this->setDownloadProductTypePlanFiles(true);
            }
        }
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     */
    protected function createDomains(BaseProductData $productData): void
    {
        $domainIds = array_keys($productData->seoTitles);

        foreach ($domainIds as $domainId) {
            $productDomain = new ProductDomain($this, $domainId);
            $this->domains->add($productDomain);
        }

        $this->setDomains($productData);
    }

    /**
     * @param int $domainId
     * @return string|null
     */
    public function getShortDescriptionUsp1(int $domainId): ?string
    {
        return $this->getProductDomain($domainId)->getShortDescriptionUsp1();
    }

    /**
     * @param int $domainId
     * @return string|null
     */
    public function getShortDescriptionUsp2(int $domainId): ?string
    {
        return $this->getProductDomain($domainId)->getShortDescriptionUsp2();
    }

    /**
     * @param int $domainId
     * @return string|null
     */
    public function getShortDescriptionUsp3(int $domainId): ?string
    {
        return $this->getProductDomain($domainId)->getShortDescriptionUsp3();
    }

    /**
     * @param int $domainId
     * @return string|null
     */
    public function getShortDescriptionUsp4(int $domainId): ?string
    {
        return $this->getProductDomain($domainId)->getShortDescriptionUsp4();
    }

    /**
     * @param int $domainId
     * @return string|null
     */
    public function getShortDescriptionUsp5(int $domainId): ?string
    {
        return $this->getProductDomain($domainId)->getShortDescriptionUsp5();
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Component\Money\Money|null
     */
    public function getLowPriceWithVat(int $domainId): ?Money
    {
        return $this->getProductDomain($domainId)->getLowPriceWithVat();
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Component\Money\Money|null
     */
    public function getHighPriceWithVat(int $domainId): ?Money
    {
        return $this->getProductDomain($domainId)->getHighPriceWithVat();
    }

    /**
     * @param string|null $locale
     * @return string|null
     */
    public function getNamePrefix($locale = null): ?string
    {
        return $this->translation($locale)->getNamePrefix();
    }

    /**
     * @param string|null $locale
     * @return string|null
     */
    public function getNameSufix($locale = null): ?string
    {
        return $this->translation($locale)->getNameSufix();
    }

    /**
     * @param string|null $locale
     * @return string
     */
    public function getFullname(?string $locale = null): string
    {
        return trim(
            $this->getNamePrefix($locale)
            . ' '
            . $this->getName($locale)
            . ' '
            . $this->getNameSufix($locale)
        );
    }

    /**
     * @return string[]
     */
    public function getFullnames()
    {
        $fullnamesByLocale = [];

        foreach ($this->translations as $translation) {
            $fullnamesByLocale[$translation->getLocale()] = $this->getFullname($translation->getLocale());
        }

        return $fullnamesByLocale;
    }

    /**
     * @param string|null $locale
     * @return string|null
     */
    public function getNameFirstLine(?string $locale = null): ?string
    {
        return $this->getNamePrefix($locale);
    }

    /**
     * @param string|null $locale
     * @return string
     */
    public function getNameSecondLine(?string $locale = null): string
    {
        return trim(
            $this->getName($locale)
            . ' '
            . $this->getNameSufix($locale)
        );
    }

    /**
     * @param bool $downloadAssemblyInstructionFiles
     */
    public function setDownloadAssemblyInstructionFiles(bool $downloadAssemblyInstructionFiles): void
    {
        $this->downloadAssemblyInstructionFiles = $downloadAssemblyInstructionFiles;
    }

    /**
     * @param bool $downloadProductTypePlanFiles
     */
    public function setDownloadProductTypePlanFiles(bool $downloadProductTypePlanFiles): void
    {
        $this->downloadProductTypePlanFiles = $downloadProductTypePlanFiles;
    }

    /**
     * @param int $domainId
     * @return string|null
     */
    public function getAssemblyInstructionCode(int $domainId): ?string
    {
        return $this->getProductDomain($domainId)->getAssemblyInstructionCode();
    }

    /**
     * @param int $domainId
     * @return string|null
     */
    public function getProductTypePlanCode(int $domainId): ?string
    {
        return $this->getProductDomain($domainId)->getProductTypePlanCode();
    }

    /**
     * @param int $domainId
     * @param string $type
     * @return string
     */
    public function getProductFileNameByType(int $domainId, string $type): string
    {
        return $type . '_' . $this->getId() . '_' . $domainId . self::PDF_SUFFIX;
    }

    /**
     * @return \App\Model\Product\ProductDomain[]|\Doctrine\Common\Collections\Collection
     */
    public function getProductDomains()
    {
        return $this->domains;
    }

    /**
     * @return bool
     */
    public function isDownloadAssemblyInstructionFiles(): bool
    {
        return $this->downloadAssemblyInstructionFiles;
    }

    /**
     * @return bool
     */
    public function isDownloadProductTypePlanFiles(): bool
    {
        return $this->downloadProductTypePlanFiles;
    }
}
