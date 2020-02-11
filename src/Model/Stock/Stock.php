<?php

declare(strict_types=1);

namespace App\Model\Stock;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="stocks")
 * @ORM\Entity
 */
class Stock
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
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $domainId;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    protected $name;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $centralStock;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, unique=true)
     */
    protected $externalId;

    /**
     * @param \App\Model\Stock\StockData $stockData
     */
    public function __construct(StockData $stockData)
    {
        $this->domainId = $stockData->domainId;
        $this->name = $stockData->name;
        $this->centralStock = $stockData->centralStock;
        $this->externalId = $stockData->externalId;
    }

    /**
     * @param \App\Model\Stock\StockData $stockData
     */
    public function edit(StockData $stockData)
    {
        $this->name = $stockData->name;
        $this->centralStock = $stockData->centralStock;
        $this->externalId = $stockData->externalId;
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
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isCentralStock(): bool
    {
        return $this->centralStock;
    }

    /**
     * @return string
     */
    public function getExternalId(): string
    {
        return $this->externalId;
    }
}
