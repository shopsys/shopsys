<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Watchdog;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;
use Symfony\Component\Clock\DatePoint;

#[AsMcpTable]
#[ORM\Table(name: 'watchdogs')]
#[ORM\Entity]
class Watchdog
{
    protected const string VALIDITY_PERIOD = '2 years';

    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    protected $domainId;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 255)]
    protected $email;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(nullable: false, name: 'product_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Product::class)]
    protected $product;

    /**
     * @var \DateTimeImmutable
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'datetime_immutable')]
    protected $createdAt;

    /**
     * @var \DateTimeImmutable
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'datetime_immutable')]
    protected $updatedAt;

    /**
     * @var \DateTimeImmutable
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'datetime_immutable')]
    protected $validUntil;

    public function __construct(WatchdogData $watchdogData)
    {
        $this->product = $watchdogData->product;
        $this->email = $watchdogData->email;
        $this->domainId = $watchdogData->domainId;
        $this->createdAt = $watchdogData->createdAt ?? new DatePoint();
        $this->updatedAt = $watchdogData->updatedAt ?? new DatePoint();
        $this->validUntil = $watchdogData->validUntil ?? new DatePoint(static::VALIDITY_PERIOD);
    }

    public function updateValidity(): void
    {
        $this->updatedAt = new DatePoint();
        $this->validUntil = new DatePoint(static::VALIDITY_PERIOD);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getValidUntil()
    {
        return $this->validUntil;
    }

    /**
     * @return int
     */
    public function getDomainId()
    {
        return $this->domainId;
    }
}
