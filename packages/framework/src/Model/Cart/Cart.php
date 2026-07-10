<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Cart;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Cart\Exception\InvalidCartItemException;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItem;
use Shopsys\FrameworkBundle\Model\Cart\Payment\CartPaymentData;
use Shopsys\FrameworkBundle\Model\Cart\Transport\CartTransportData;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucher;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;
use Symfony\Component\Clock\DatePoint;

#[AsMcpTable]
#[ORM\Table(name: 'carts')]
#[ORM\Entity]
class Cart
{
    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 127)]
    protected $cartIdentifier;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(name: 'customer_user_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: CustomerUser::class)]
    protected $customerUser;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Cart\Item\CartItem>
     */
    #[ORM\OneToMany(targetEntity: CartItem::class, mappedBy: 'cart')]
    #[ORM\OrderBy(['id' => 'ASC'])]
    protected $items;

    /**
     * @var \DateTimeImmutable
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'datetime_immutable')]
    protected $modifiedAt;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode>
     */
    #[ORM\JoinTable(name: 'cart_promo_codes')]
    #[ORM\ManyToMany(targetEntity: PromoCode::class)]
    #[ORM\OrderBy(['id' => 'DESC'])]
    protected $promoCodes;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucher>
     */
    #[ORM\JoinTable(name: 'cart_gift_vouchers')]
    #[ORM\ManyToMany(targetEntity: GiftVoucher::class)]
    #[ORM\OrderBy(['id' => 'ASC'])]
    protected $giftVouchers;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\Transport|null
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Transport::class)]
    protected $transport;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'money', precision: 20, scale: 6, nullable: true)]
    protected $transportWatchedPrice;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', nullable: true)]
    protected $pickupPlaceIdentifier;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\Payment|null
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Payment::class)]
    protected $payment;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'money', precision: 20, scale: 6, nullable: true)]
    protected $paymentWatchedPrice;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 15, nullable: true)]
    protected $paymentGoPayBankSwift;

    public function __construct(string $cartIdentifier, ?CustomerUser $customerUser)
    {
        $this->cartIdentifier = $cartIdentifier;
        $this->customerUser = $customerUser;
        $this->items = new ArrayCollection();
        $this->modifiedAt = new DatePoint();
        $this->promoCodes = new ArrayCollection();
        $this->giftVouchers = new ArrayCollection();
    }

    public function addItem(CartItem $item): void
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $this->setModifiedNow();
        }
    }

    /**
     * @param int $itemId
     */
    public function removeItemById($itemId): void
    {
        foreach ($this->items as $item) {
            if ($item->getId() === $itemId) {
                $this->items->removeElement($item);
                $this->setModifiedNow();

                return;
            }
        }
        $message = 'Cart item with ID = ' . $itemId . ' is not in cart for remove.';

        throw new InvalidCartItemException($message);
    }

    public function clean(): void
    {
        $this->items->clear();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Cart\Item\CartItem[]
     */
    public function getItems()
    {
        return $this->items->getValues();
    }

    /**
     * @return int
     */
    public function getItemsCount()
    {
        return $this->items->count();
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return $this->getItemsCount() === 0;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[]
     */
    public function getQuantifiedProducts()
    {
        $quantifiedProducts = [];

        foreach ($this->items as $item) {
            try {
                $quantifiedProducts[$item->getId()] = $this->createQuantifiedProduct($item);
            } catch (ProductNotFoundException) {
                continue;
            }
        }

        return $quantifiedProducts;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[]
     */
    public function getQuantifiedProductsWithoutGiftVouchers()
    {
        return array_filter(
            $this->getQuantifiedProducts(),
            static fn (QuantifiedProduct $quantifiedProduct) => !$quantifiedProduct->getProduct()->isGiftVoucher(),
        );
    }

    protected function createQuantifiedProduct(CartItem $cartItem): QuantifiedProduct
    {
        $quantifiedProduct = new QuantifiedProduct($cartItem->getProduct(), $cartItem->getQuantity());
        $quantifiedProduct->setAdditionalData(QuantifiedProduct::CART_ITEM_TYPE_KEY, $cartItem->getType());

        return $quantifiedProduct;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function getProducts(): array
    {
        return array_map(
            static fn (QuantifiedProduct $quantifiedProduct) => $quantifiedProduct->getProduct(),
            $this->getQuantifiedProducts(),
        );
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function getPersonalPickupOnlyProducts(): array
    {
        return array_filter(
            $this->getProducts(),
            static fn (Product $product) => $product->isPersonalPickupOnly(),
        );
    }

    public function isPersonalPickupRequired(): bool
    {
        return $this->getPersonalPickupOnlyProducts() !== [];
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function getElectronicGiftVoucherProducts(): array
    {
        return array_filter(
            $this->getProducts(),
            static fn (Product $product) => $product->isElectronicGiftVoucher(),
        );
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function getProductsOtherThanElectronicGiftVouchers(): array
    {
        return array_filter(
            $this->getProducts(),
            static fn (Product $product) => !$product->isElectronicGiftVoucher(),
        );
    }

    public function hasOnlyElectronicGiftVoucherProducts(): bool
    {
        return $this->getProducts() !== [] && $this->getProductsOtherThanElectronicGiftVouchers() === [];
    }

    public function findSimilarItemByItem(CartItem $item): ?CartItem
    {
        foreach ($this->items as $similarItem) {
            if ($similarItem->isSimilarItemAs($item)) {
                return $similarItem;
            }
        }

        return null;
    }

    /**
     * @return string
     */
    public function getCartIdentifier()
    {
        return $this->cartIdentifier;
    }

    public function setModifiedNow(): void
    {
        $this->modifiedAt = new DatePoint();
    }

    /**
     * @param \DateTimeImmutable $modifiedAt
     */
    public function setModifiedAt($modifiedAt): void
    {
        $this->modifiedAt = $modifiedAt;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null
     */
    public function getCustomerUser()
    {
        return $this->customerUser;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode[]
     */
    public function getAllAppliedPromoCodes()
    {
        return $this->promoCodes->getValues();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode|null
     */
    public function getFirstAppliedPromoCode()
    {
        $firstAppliedPromoCode = $this->promoCodes->first();

        if ($firstAppliedPromoCode === false) {
            return null;
        }

        return $firstAppliedPromoCode;
    }

    public function applyPromoCode(PromoCode $promoCode): void
    {
        if (!$this->promoCodes->contains($promoCode)) {
            $this->promoCodes->add($promoCode);
            $this->setModifiedNow();
        }
    }

    public function removePromoCodeById(int $promoCodeId): void
    {
        foreach ($this->promoCodes as $promoCode) {
            if ($promoCode->getId() === $promoCodeId) {
                $this->promoCodes->removeElement($promoCode);
                $this->setModifiedNow();

                return;
            }
        }
        $message = 'Promo code with ID = ' . $promoCodeId . ' is not applied.';

        throw new InvalidCartItemException($message);
    }

    public function isPromoCodeApplied(string $promoCodeCode): bool
    {
        return $this->promoCodes->exists(
            static function ($key, PromoCode $promoCode) use ($promoCodeCode) {
                return $promoCode->getCode() === $promoCodeCode;
            },
        );
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucher[]
     */
    public function getAllAppliedGiftVouchers()
    {
        return $this->giftVouchers->getValues();
    }

    public function applyGiftVoucher(GiftVoucher $giftVoucher): void
    {
        if (!$this->giftVouchers->contains($giftVoucher)) {
            $this->giftVouchers->add($giftVoucher);
            $this->setModifiedNow();
        }
    }

    public function removeGiftVoucherById(int $giftVoucherId): void
    {
        foreach ($this->giftVouchers as $giftVoucher) {
            if ($giftVoucher->getId() === $giftVoucherId) {
                $this->giftVouchers->removeElement($giftVoucher);
                $this->setModifiedNow();

                return;
            }
        }
        $message = 'Gift voucher with ID = ' . $giftVoucherId . ' is not applied.';

        throw new InvalidCartItemException($message);
    }

    public function isGiftVoucherApplied(string $giftVoucherCode): bool
    {
        return $this->giftVouchers->exists(
            static function ($key, GiftVoucher $giftVoucher) use ($giftVoucherCode) {
                return $giftVoucher->getCode() === $giftVoucherCode;
            },
        );
    }

    public function unsetCartTransport(): void
    {
        $this->transport = null;
        $this->transportWatchedPrice = null;
        $this->pickupPlaceIdentifier = null;
        $this->setModifiedNow();
    }

    public function editCartTransport(CartTransportData $cartTransportData): void
    {
        $this->transport = $cartTransportData->transport;
        $this->transportWatchedPrice = $cartTransportData->watchedPrice;
        $this->pickupPlaceIdentifier = $cartTransportData->pickupPlaceIdentifier;
        $this->setModifiedNow();
    }

    public function editCartPayment(CartPaymentData $cartPaymentData): void
    {
        $this->payment = $cartPaymentData->payment;
        $this->paymentWatchedPrice = $cartPaymentData->watchedPrice;
        $this->paymentGoPayBankSwift = $cartPaymentData->goPayBankSwift;
        $this->setModifiedNow();
    }

    public function unsetCartPayment(): void
    {
        $this->payment = null;
        $this->paymentWatchedPrice = null;
        $this->paymentGoPayBankSwift = null;
        $this->setModifiedNow();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Transport\Transport|null
     */
    public function getTransport()
    {
        return $this->transport;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money|null
     */
    public function getTransportWatchedPrice()
    {
        return $this->transportWatchedPrice;
    }

    /**
     * @return string|null
     */
    public function getPickupPlaceIdentifier()
    {
        return $this->pickupPlaceIdentifier;
    }

    public function unsetPickupPlaceIdentifier(): void
    {
        $this->pickupPlaceIdentifier = null;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money|null $transportWatchedPrice
     */
    public function setTransportWatchedPrice($transportWatchedPrice): void
    {
        $this->transportWatchedPrice = $transportWatchedPrice;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Payment\Payment|null
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money|null
     */
    public function getPaymentWatchedPrice()
    {
        return $this->paymentWatchedPrice;
    }

    /**
     * @return string|null
     */
    public function getPaymentGoPayBankSwift()
    {
        return $this->paymentGoPayBankSwift;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money|null $paymentWatchedPrice
     */
    public function setPaymentWatchedPrice($paymentWatchedPrice): void
    {
        $this->paymentWatchedPrice = $paymentWatchedPrice;
    }

    public function getTotalWeight(): int
    {
        $totalWeight = 0;

        foreach ($this->items as $item) {
            try {
                $product = $item->getProduct();
                $totalWeight += $product->getWeight() * $item->getQuantity();
            } catch (ProductNotFoundException $productNotFoundException) {
                continue;
            }
        }

        return $totalWeight;
    }

    public function getItemByUuid(string $itemUuid): CartItem
    {
        foreach ($this->items as $item) {
            if ($item->getUuid() === $itemUuid) {
                return $item;
            }
        }

        $message = 'Cart item with UUID "' . $itemUuid . '" not found in cart.';

        throw new InvalidCartItemException($message);
    }

    public function assignCartToCustomerUser(?CustomerUser $customerUser): void
    {
        $this->customerUser = $customerUser;
        $this->cartIdentifier = '';
        $this->setModifiedNow();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Cart\Item\CartItem[]
     */
    public function getProductGiftCartItems(): array
    {
        return array_filter($this->getItems(), fn (CartItem $cartItem) => $cartItem->isProductGift());
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Cart\Item\CartItem[]
     */
    public function getProductCartItems(): array
    {
        return array_filter($this->getItems(), fn (CartItem $cartItem) => $cartItem->isProduct());
    }
}
