<?php

declare(strict_types=1);

namespace App\Model\Product\Series\Category;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(
 *     name="product_series_category_domains",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="product_series_category_domain", columns={"product_series_category_id", "domain_id"})
 *     }
 * )
 *
 * @ORM\Entity
 */
class ProductSeriesCategoryDomain
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
     * @var \App\Model\Product\Series\Category\ProductSeriesCategory
     *
     * @ORM\ManyToOne(targetEntity="App\Model\Product\Series\Category\ProductSeriesCategory", inversedBy="domains")
     * @ORM\JoinColumn(nullable=false, name="product_series_category_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $productSeriesCategory;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $domainId;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $seoTitle;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $seoMetaDescription;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $seoH1;

    /**
     * @param \App\Model\Product\Series\Category\ProductSeriesCategory $productSeriesCategory
     * @param int $domainId
     */
    public function __construct(ProductSeriesCategory $productSeriesCategory, int $domainId)
    {
        $this->productSeriesCategory = $productSeriesCategory;
        $this->domainId = $domainId;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getDomainId(): int
    {
        return $this->domainId;
    }

    /**
     * @return string|null
     */
    public function getSeoTitle(): ?string
    {
        return $this->seoTitle;
    }

    /**
     * @param string|null $seoTitle
     */
    public function setSeoTitle(?string $seoTitle): void
    {
        $this->seoTitle = $seoTitle;
    }

    /**
     * @return string|null
     */
    public function getSeoMetaDescription(): ?string
    {
        return $this->seoMetaDescription;
    }

    /**
     * @param string|null $seoMetaDescription
     */
    public function setSeoMetaDescription(?string $seoMetaDescription): void
    {
        $this->seoMetaDescription = $seoMetaDescription;
    }

    /**
     * @return string|null
     */
    public function getSeoH1(): ?string
    {
        return $this->seoH1;
    }

    /**
     * @param string|null $seoH1
     */
    public function setSeoH1(?string $seoH1): void
    {
        $this->seoH1 = $seoH1;
    }
}
