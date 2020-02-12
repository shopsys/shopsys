<?php

declare(strict_types=1);

namespace App\Model\Product\Series;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(
 *     name="product_series_domains",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="product_series_domain", columns={"product_series_id", "domain_id"})
 *     }
 * )
 *
 * @ORM\Entity
 */
class ProductSeriesDomain
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
     * @var \App\Model\Product\Series\ProductSeries
     *
     * @ORM\ManyToOne(targetEntity="App\Model\Product\Series\ProductSeries", inversedBy="domains")
     * @ORM\JoinColumn(nullable=false, name="product_series_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $productSeries;

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
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $hidden;

    /**
     * @param \App\Model\Product\Series\ProductSeries $productSeries
     * @param int $domainId
     */
    public function __construct(ProductSeries $productSeries, $domainId)
    {
        $this->productSeries = $productSeries;
        $this->domainId = $domainId;
        $this->hidden = false;
    }

    /**
     * @return int
     */
    public function getDomainId()
    {
        return $this->domainId;
    }

    /**
     * @return string|null
     */
    public function getSeoTitle()
    {
        return $this->seoTitle;
    }

    /**
     * @return string|null
     */
    public function getSeoMetaDescription()
    {
        return $this->seoMetaDescription;
    }

    /**
     * @return string|null
     */
    public function getSeoH1()
    {
        return $this->seoH1;
    }

    /**
     * @param string|null $seoTitle
     */
    public function setSeoTitle($seoTitle)
    {
        $this->seoTitle = $seoTitle;
    }

    /**
     * @param string|null $seoMetaDescription
     */
    public function setSeoMetaDescription($seoMetaDescription)
    {
        $this->seoMetaDescription = $seoMetaDescription;
    }

    /**
     * @param string $seoH1
     */
    public function setSeoH1($seoH1)
    {
        $this->seoH1 = $seoH1;
    }

    /**
     * @return bool
     */
    public function isHidden(): bool
    {
        return $this->hidden;
    }

    /**
     * @param bool $hidden
     */
    public function setHidden(bool $hidden): void
    {
        $this->hidden = $hidden;
    }
}
