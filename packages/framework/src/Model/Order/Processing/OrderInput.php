<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Processing;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Transport\Transport;

class OrderInput
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[]
     */
    protected array $products = [];

    protected ?Transport $transport = null;

    protected ?Payment $payment = null;

    protected ?CustomerUser $customerUser = null;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode[]
     */
    protected array $promoCodes = [];

    /**
     * @var array<int, mixed>
     */
    protected array $additionalData = [];

    public function __construct(
        protected readonly DomainConfig $domainConfig,
    ) {
    }

    public function addProduct(Product $product, int $quantity): void
    {
        $quantifiedProduct = new QuantifiedProduct($product, $quantity);
        $quantifiedProduct->setAdditionalData(QuantifiedProduct::CART_ITEM_TYPE_KEY, CartItemTypeEnum::TYPE_PRODUCT);

        $this->addQuantifiedProduct($quantifiedProduct);
    }

    public function addQuantifiedProduct(QuantifiedProduct $quantifiedProduct): void
    {
        $this->products[] = $quantifiedProduct;
    }

    public function cleanProducts(): void
    {
        $this->products = [];
    }

    public function setTransport(?Transport $transport): void
    {
        $this->transport = $transport;
    }

    public function setPayment(?Payment $payment): void
    {
        $this->payment = $payment;
    }

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

    public function getPayment(): ?Payment
    {
        return $this->payment;
    }

    public function getTransport(): ?Transport
    {
        return $this->transport;
    }

    public function findAdditionalData(string $key): mixed
    {
        return $this->additionalData[$key] ?? null;
    }

    public function addAdditionalData(string $key, mixed $value): void
    {
        $this->additionalData[$key] = $value;
    }

    public function cleanAdditionalData(string $key): void
    {
        unset($this->additionalData[$key]);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode[]
     */
    public function getPromoCodes(): array
    {
        return $this->promoCodes;
    }

    public function getCustomerUser(): ?CustomerUser
    {
        return $this->customerUser;
    }

    public function setCustomerUser(?CustomerUser $customerUser): void
    {
        $this->customerUser = $customerUser;
    }

    public function getDomainConfig(): DomainConfig
    {
        return $this->domainConfig;
    }

    public function isFreeTransportAndPaymentPromoCodeApplied(): bool
    {
        foreach ($this->getPromoCodes() as $appliedPromoCode) {
            if ($appliedPromoCode->isFreeTransportAndPaymentType()) {
                return true;
            }
        }

        return false;
    }
}
