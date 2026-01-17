<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Watchdog;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Clock\DatePoint;

/**
 * @ORM\Table(name="watchdogs")
 * @ORM\Entity
 */
class Watchdog
{
    protected const string VALIDITY_PERIOD = '2 years';

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $domainId;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    protected $email;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Product\Product")
     * @ORM\JoinColumn(nullable=false, name="product_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $product;

    /**
     * @var \DateTimeImmutable
     * @ORM\Column(type="datetime_immutable")
     */
    protected $createdAt;

    /**
     * @var \DateTimeImmutable
     * @ORM\Column(type="datetime_immutable")
     */
    protected $updatedAt;

    /**
     * @var \DateTimeImmutable
     * @ORM\Column(type="datetime_immutable")
     */
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
