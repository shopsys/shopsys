<?php

declare(strict_types=1);

namespace App\Model\Stock;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(
 *     name="stock_domains",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="stock_domain", columns={"stock_id", "domain_id"})
 *     }
 * )
 * @ORM\Entity
 */
class StockDomain
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected int $id;

    /**
     * @var \App\Model\Stock\Stock
     * @ORM\ManyToOne(targetEntity="App\Model\Stock\Stock", inversedBy="domains")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    protected Stock $stock;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected int $domainId;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected bool $isEnabled = false;

    /**
     * @param \App\Model\Stock\Stock $stock
     * @param int $domainId
     */
    public function __construct(Stock $stock, int $domainId)
    {
        $this->stock = $stock;
        $this->domainId = $domainId;
        $this->isEnabled = true;
    }

    /**
     * @return int
     */
    public function getDomainId(): int
    {
        return $this->domainId;
    }

    /**
     * @param bool $isEnabled
     */
    public function setEnabled(bool $isEnabled): void
    {
        $this->isEnabled = $isEnabled;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }
}
