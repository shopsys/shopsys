<?php

declare(strict_types=1);

namespace App\Model\Order\Transfer\ScontoBridge\Entity\ScontoBridgeErpOrder;

use JsonSerializable;

class ScontoBridgeOrderItem implements JsonSerializable
{
    /**
     * @var int
     */
    private int $eshopId;

    /**
     * @var string|null
     */
    private ?string $storeCode;

    /**
     * @var string|null
     */
    private ?string $sku;

    /**
     * @var int
     */
    private int $quantity;

    /**
     * @var float
     */
    private float $unitPriceWithVat;

    /**
     * @var float
     */
    private float $priceWithVat;

    /**
     * @var int
     */
    private int $type;

    /**
     * @var string|null
     */
    private ?string $promocodeIdentifier;

    public function __construct()
    {
        $this->storeCode = null;
        $this->sku = null;
        $this->promocodeIdentifier = null;
    }

    /**
     * @param int $eshopId
     */
    public function setEshopId(int $eshopId): void
    {
        $this->eshopId = $eshopId;
    }

    /**
     * @param string $storeCode
     */
    public function setStoreCode(string $storeCode): void
    {
        $this->storeCode = $storeCode;
    }

    /**
     * @param string $sku
     */
    public function setSku(string $sku): void
    {
        $this->sku = $sku;
    }

    /**
     * @param int $quantity
     */
    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    /**
     * @param float $unitPriceWithVat
     */
    public function setUnitPriceWithVat(float $unitPriceWithVat): void
    {
        $this->unitPriceWithVat = $unitPriceWithVat;
    }

    /**
     * @param float $priceWithVat
     */
    public function setPriceWithVat(float $priceWithVat): void
    {
        $this->priceWithVat = $priceWithVat;
    }

    /**
     * @param int $type
     */
    public function setType(int $type): void
    {
        $this->type = $type;
    }

    /**
     * @param string $promocodeIdentifier
     */
    public function setPromocodeIdentifier(string $promocodeIdentifier): void
    {
        $this->promocodeIdentifier = $promocodeIdentifier;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'eshopId' => $this->eshopId,
            'storeCode' => $this->storeCode,
            'sku' => $this->sku,
            'quantity' => $this->quantity,
            'unitPriceWithVat' => $this->unitPriceWithVat,
            'priceWithVat' => $this->priceWithVat,
            'type' => $this->type,
            'promocodeIdentifier' => $this->promocodeIdentifier,
        ];
    }
}
