<?php

namespace Shopsys\FrameworkBundle\Model\Product;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(
 *     name="product_domains",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="product_domain", columns={"product_id", "domain_id"})
 *     }
 * )
 *
 * @ORM\Entity
 */
class ProductDomain
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
     * @var \Shopsys\FrameworkBundle\Model\Product\Product
     *
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Product\Product", inversedBy="domains")
     * @ORM\JoinColumn(nullable=false, name="product_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $product;

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
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    protected $shortDescription;

    /**
     * @var string
     *
     * @ORM\Column(type="tsvector", nullable=false)
     */
    protected $descriptionTsvector;

    /**
     * @var string
     *
     * @ORM\Column(type="tsvector", nullable=false)
     */
    protected $fulltextTsvector;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $seoH1;

    /**
     * @param int $domainId
     */
    public function __construct(Product $product, $domainId)
    {
        $this->product = $product;
        $this->domainId = $domainId;
    }

    public function getDomainId(): int
    {
        return $this->domainId;
    }

    public function getSeoTitle(): ?string
    {
        return $this->seoTitle;
    }

    public function getSeoMetaDescription(): ?string
    {
        return $this->seoMetaDescription;
    }

    public function getSeoH1(): ?string
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    /**
     * @param string|null $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @param string|null $shortDescription
     */
    public function setShortDescription($shortDescription)
    {
        $this->shortDescription = $shortDescription;
    }
}
