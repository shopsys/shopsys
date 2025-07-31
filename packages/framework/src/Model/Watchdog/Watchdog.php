<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Watchdog;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="watchdogs")
 * @ORM\Entity
 */
class Watchdog
{
    protected const VALIDITY_PERIOD = '2 years';

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
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $updatedAt;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $validUntil;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Watchdog\WatchdogData $watchdogData
     */
    public function __construct(WatchdogData $watchdogData)
    {
        $this->product = $watchdogData->product;
        $this->email = $watchdogData->email;
        $this->domainId = $watchdogData->domainId;
        $this->createdAt = $watchdogData->createdAt ?? new DateTime();
        $this->updatedAt = $watchdogData->updatedAt ?? new DateTime();
        $this->validUntil = $watchdogData->validUntil ?? new DateTime(static::VALIDITY_PERIOD);
    }

    public function updateValidity()
    {
        $this->updatedAt = new DateTime();
        $this->validUntil = new DateTime(static::VALIDITY_PERIOD);
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
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @return \DateTime
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
