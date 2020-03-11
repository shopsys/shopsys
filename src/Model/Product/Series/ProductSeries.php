<?php

declare(strict_types=1);

namespace App\Model\Product\Series;

use App\Model\Product\Series\Exception\ProductSeriesDomainNotFoundException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Prezent\Doctrine\Translatable\Annotation as Prezent;
use Shopsys\FrameworkBundle\Model\Localization\AbstractTranslatableEntity;

/**
 * @ORM\Table(name="product_series")
 * @ORM\Entity
 *
 * @method ProductSeriesTranslation translation(?string $locale = null)
 */
class ProductSeries extends AbstractTranslatableEntity
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $akeneoCode;

    /**
     * @var \App\Model\Product\Series\ProductSeriesTranslation[]|\Doctrine\Common\Collections\Collection
     *
     * @Prezent\Translations(targetEntity="App\Model\Product\Series\ProductSeriesTranslation")
     */
    protected $translations;

    /**
     * @var \App\Model\Product\Series\ProductSeriesDomain[]|\Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="App\Model\Product\Series\ProductSeriesDomain", mappedBy="productSeries", cascade={"persist"}, fetch="EXTRA_LAZY")
     */
    protected $domains;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection|\App\Model\Product\Series\Category\ProductSeriesCategory[]
     *
     * @ORM\ManyToMany(targetEntity="App\Model\Product\Series\Category\ProductSeriesCategory", fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"position" = "ASC"})
     */
    protected $productSeriesCategories;

    /**
     * @param \App\Model\Product\Series\ProductSeriesData $productSeriesData
     */
    public function __construct(ProductSeriesData $productSeriesData)
    {
        $this->translations = new ArrayCollection();
        $this->domains = new ArrayCollection();
        $this->productSeriesCategories = new ArrayCollection($productSeriesData->productSeriesCategories);
        $this->setTranslations($productSeriesData);
        $this->createDomains($productSeriesData);
        $this->akeneoCode = $productSeriesData->akeneoCode;
    }

    /**
     * @param \App\Model\Product\Series\ProductSeriesData $productSeriesData
     */
    public function edit(ProductSeriesData $productSeriesData)
    {
        $this->setTranslations($productSeriesData);
        $this->setDomains($productSeriesData);
        $this->editProductSeriesCategories($productSeriesData->productSeriesCategories);
        $this->akeneoCode = $productSeriesData->akeneoCode;
    }

    /**
     * @param \App\Model\Product\Series\Category\ProductSeriesCategory[] $productSeriesCategories
     */
    private function editProductSeriesCategories(array $productSeriesCategories): void
    {
        $this->productSeriesCategories->clear();
        foreach ($productSeriesCategories as $productSeriesCategory) {
            $this->productSeriesCategories->add($productSeriesCategory);
        }
    }

    /**
     * @param \App\Model\Product\Series\ProductSeriesData $productSeriesData
     */
    private function setTranslations(ProductSeriesData $productSeriesData): void
    {
        foreach ($productSeriesData->name as $locale => $name) {
            $this->translation($locale)->setName($name);
        }

        foreach ($productSeriesData->description as $locale => $description) {
            $this->translation($locale)->setDescription($description);
        }
    }

    /**
     * @param \App\Model\Product\Series\ProductSeriesData $productSeriesData
     */
    private function createDomains(ProductSeriesData $productSeriesData): void
    {
        $domainIds = array_keys($productSeriesData->hidden);

        foreach ($domainIds as $domainId) {
            $productSeriesDomain = new ProductSeriesDomain($this, $domainId);
            $this->domains->add($productSeriesDomain);
        }

        $this->setDomains($productSeriesData);
    }

    /**
     * @param \App\Model\Product\Series\ProductSeriesData $productSeriesData
     */
    private function setDomains(ProductSeriesData $productSeriesData): void
    {
        foreach ($this->domains as $productSeriesDomain) {
            $domainId = $productSeriesDomain->getDomainId();
            $productSeriesDomain->setSeoTitle($productSeriesData->seoTitle[$domainId]);
            $productSeriesDomain->setSeoH1($productSeriesData->seoH1[$domainId]);
            $productSeriesDomain->setSeoMetaDescription($productSeriesData->seoMetaDescription[$domainId]);
            $productSeriesDomain->setHidden($productSeriesData->hidden[$domainId]);
        }
    }

    /**
     * @param int $domainId
     * @return \App\Model\Product\Series\ProductSeriesDomain
     */
    private function getProductSeriesDomain(int $domainId): ProductSeriesDomain
    {
        foreach ($this->domains as $domain) {
            if ($domain->getDomainId() === $domainId) {
                return $domain;
            }
        }

        throw new ProductSeriesDomainNotFoundException($this);
    }

    /**
     * @return \App\Model\Product\Series\Category\ProductSeriesCategory[]
     */
    public function getProductSeriesCategories(): array
    {
        return $this->productSeriesCategories->toArray();
    }

    /**
     * @return \App\Model\Product\Series\ProductSeriesTranslation
     */
    public function createTranslation(): ProductSeriesTranslation
    {
        return new ProductSeriesTranslation();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return \App\Model\Product\Series\ProductSeriesTranslation[]|\Doctrine\Common\Collections\Collection
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @param string|null $locale
     * @return string|null
     */
    public function getName($locale = null): ?string
    {
        return $this->translation($locale)->getName();
    }

    /**
     * @param string|null $locale
     * @return string|null
     */
    public function getDescription($locale = null): ?string
    {
        return $this->translation($locale)->getDescription();
    }

    /**
     * @param int $domainId
     * @return string|null
     */
    public function getSeoTitle(int $domainId): ?string
    {
        return $this->getProductSeriesDomain($domainId)->getSeoTitle();
    }

    /**
     * @param int $domainId
     * @return string|null
     */
    public function getSeoMetaDescription(int $domainId): ?string
    {
        return $this->getProductSeriesDomain($domainId)->getSeoMetaDescription();
    }

    /**
     * @param int $domainId
     * @return string|null
     */
    public function getSeoH1(int $domainId): ?string
    {
        return $this->getProductSeriesDomain($domainId)->getSeoH1();
    }

    /**
     * @param int $domainId
     * @return bool
     */
    public function isHidden(int $domainId): bool
    {
        return $this->getProductSeriesDomain($domainId)->isHidden();
    }

    /**
     * @return string|null
     */
    public function getAkeneoCode(): ?string
    {
        return $this->akeneoCode;
    }
}
