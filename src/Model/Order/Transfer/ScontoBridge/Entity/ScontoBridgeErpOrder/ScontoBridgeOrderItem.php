<?php
declare(strict_types=1);

namespace App\Model\Product\Transfer\ScontoBridge\Mapper\Entity\ScontoBridgeErpOrder;

use JsonSerializable;

class ScontoBridgeOrderItem implements JsonSerializable
{
    private int $eshopId;
    private string $storeCode;
    private string $sku;
    private int $quantity;
    private float $unitPriceWithVat;
    private float $priceWithVat;
    private int $type;
    private string $promocodeIdentifier;

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