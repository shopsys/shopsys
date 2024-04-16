<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Processing;

use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Transport\Transport;

class InputOrderData
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $product
     */
    protected array $products = [];

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport|null $product
     */
    protected ?Transport $transport = null;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment|null $product
     */
    protected ?Payment $payment = null;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode[] $product
     */
    protected array $promoCodes = [];

    /**
     * @var array
     */
    protected array $additionalData = [];

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param int $quantity
     */
    public function addProduct(Product $product, int $quantity): void
    {
        $this->products[] = new QuantifiedProduct($product, $quantity);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport|null $transport
     */
    public function setTransport(?Transport $transport): void
    {
        $this->transport = $transport;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment|null $payment
     */
    public function setPayment(?Payment $payment): void
    {
        $this->payment = $payment;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode $promoCode
     */
    public function addPromoCode(PromoCode $promoCode): void
    {
        $this->promoCodes[] = $promoCode;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[]
     */
    public function getQuantifiedProducts(): array
    {
        return $this->products;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Payment\Payment|null
     */
    public function getPayment(): ?Payment
    {
        return $this->payment;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Transport\Transport|null
     */
    public function getTransport(): ?Transport
    {
        return $this->transport;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function findAdditionalData(string $key): mixed
    {
        return $this->additionalData[$key] ?? null;
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function addAdditionalData(string $key, mixed $value): void
    {
        $this->additionalData[$key] = $value;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode[]
     */
    public function getPromoCodes(): array
    {
        return $this->promoCodes;
    }
}
