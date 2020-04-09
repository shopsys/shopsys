<?php

declare(strict_types=1);

namespace App\Model\Product\Type;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Component\Money\Money;

/**
 * @ORM\Table(
 *     name="product_type_domains",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="product_type_domain", columns={"product_type_id", "domain_id"})
 *     }
 * )
 *
 * @ORM\Entity
 */
class ProductTypeDomain
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \App\Model\Product\Type\ProductType
     *
     * @ORM\ManyToOne(targetEntity="App\Model\Product\Type\ProductType", inversedBy="domains")
     * @ORM\JoinColumn(nullable=false, name="product_type_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $productType;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $domainId;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money|null
     *
     * @ORM\Column(type="money", precision=20, scale=6, nullable=true)
     */
    private $freeTransportMinimalPrice;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $freeTransport;

    /**
     * @param \App\Model\Product\Type\ProductType $productType
     * @param int $domainId
     */
    public function __construct(ProductType $productType, int $domainId)
    {
        $this->productType = $productType;
        $this->domainId = $domainId;
        $this->freeTransport = false;
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
     * @return \Shopsys\FrameworkBundle\Component\Money\Money|null
     */
    public function getFreeTransportMinimalPrice(): ?Money
    {
        return $this->freeTransportMinimalPrice;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money|null $freeTransportMinimalPrice
     */
    public function setFreeTransportMinimalPrice(?Money $freeTransportMinimalPrice): void
    {
        $this->freeTransportMinimalPrice = $freeTransportMinimalPrice;
    }

    /**
     * @return bool
     */
    public function isFreeTransport(): bool
    {
        return $this->freeTransport;
    }

    /**
     * @param bool $freeTransport
     */
    public function setFreeTransport(bool $freeTransport): void
    {
        $this->freeTransport = $freeTransport;
    }
}
