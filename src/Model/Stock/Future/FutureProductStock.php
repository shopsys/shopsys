<?php

declare(strict_types=1);

namespace App\Model\Stock\Future;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="future_product_stocks")
 * @ORM\Entity
 */
class FutureProductStock
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
     * @var string
     *
     * @ORM\Column(type="string", length=50, unique=true)
     */
    private $erpId;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100)
     */
    private $storeCode;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100)
     */
    private $sku;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $amount;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateExpectedArrival;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateConfirmedArrival;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $isLate;

    /**
     * @param \App\Model\Stock\Future\FutureProductStockData $futureProductStockData
     */
    public function __construct(FutureProductStockData $futureProductStockData)
    {
        $this->erpId = $futureProductStockData->erpId;
        $this->sku = $futureProductStockData->sku;
        $this->storeCode = $futureProductStockData->storeCode;
        $this->amount = $futureProductStockData->amount;
        $this->dateExpectedArrival = $futureProductStockData->dateExpectedArrival;
        $this->dateConfirmedArrival = $futureProductStockData->dateConfirmedArrival;
        $this->isLate = $futureProductStockData->isLate;
    }

    /**
     * @param \App\Model\Stock\Future\FutureProductStockData $futureProductStockData
     */
    public function edit(FutureProductStockData $futureProductStockData): void
    {
        $this->erpId = $futureProductStockData->erpId;
        $this->sku = $futureProductStockData->sku;
        $this->storeCode = $futureProductStockData->storeCode;
        $this->amount = $futureProductStockData->amount;
        $this->dateExpectedArrival = $futureProductStockData->dateExpectedArrival;
        $this->dateConfirmedArrival = $futureProductStockData->dateConfirmedArrival;
        $this->isLate = $futureProductStockData->isLate;
    }

    /**
     * @return string
     */
    public function getErpId(): string
    {
        return $this->erpId;
    }

    /**
     * @return string
     */
    public function getStoreCode(): string
    {
        return $this->storeCode;
    }

    /**
     * @return string
     */
    public function getSku(): string
    {
        return $this->sku;
    }

    /**
     * @return int
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * @return \DateTime|null
     */
    public function getDateExpectedArrival(): ?\DateTime
    {
        return $this->dateExpectedArrival;
    }

    /**
     * @return \DateTime|null
     */
    public function getDateConfirmedArrival(): ?\DateTime
    {
        return $this->dateConfirmedArrival;
    }

    /**
     * @return bool
     */
    public function isLate(): bool
    {
        return $this->isLate;
    }
}
