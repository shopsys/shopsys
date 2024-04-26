<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Cart;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Cart\Exception\InvalidCartItemException;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItem;
use Shopsys\FrameworkBundle\Model\Cart\Payment\CartPaymentData;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;
use Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException;
use Shopsys\FrontendApiBundle\Model\Cart\Transport\CartTransportData;

/**
 * @ORM\Table(name="carts")
 * @ORM\Entity
 */
class Cart
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=127)
     */
    protected $cartIdentifier;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser")
     * @ORM\JoinColumn(name="customer_user_id", referencedColumnName="id", nullable = true, onDelete="CASCADE")
     */
    protected $customerUser;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Cart\Item\CartItem>
     * @ORM\OneToMany(
     *     targetEntity="Shopsys\FrameworkBundle\Model\Cart\Item\CartItem",
     *     mappedBy="cart"
     * )
     * @ORM\OrderBy({"id" = "DESC"})
     */
    protected $items;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $modifiedAt;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode>
     * @ORM\ManyToMany(
     *     targetEntity="\Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode"
     * )
     * @ORM\JoinTable(name="cart_promo_codes")
     * @ORM\OrderBy({"id" = "DESC"})
     */
    protected $promoCodes;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\Transport|null
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Transport\Transport")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    protected $transport;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money|null
     * @ORM\Column(type="money", precision=20, scale=6, nullable=true)
     */
    protected $transportWatchedPrice;

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    protected $pickupPlaceIdentifier;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\Payment|null
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Payment\Payment")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    protected $payment;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money|null
     * @ORM\Column(type="money", precision=20, scale=6, nullable=true)
     */
    protected $paymentWatchedPrice;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=15, nullable=true)
     */
    protected $paymentGoPayBankSwift;

    /**
     * @param string $cartIdentifier
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null $customerUser
     */
    public function __construct(string $cartIdentifier, ?CustomerUser $customerUser = null)
    {
        $this->cartIdentifier = $cartIdentifier;
        $this->customerUser = $customerUser;
        $this->items = new ArrayCollection();
        $this->modifiedAt = new DateTime();
        $this->promoCodes = new ArrayCollection();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Item\CartItem $item
     */
    public function addItem(CartItem $item)
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $this->setModifiedNow();
        }
    }

    /**
     * @param int $itemId
     */
    public function removeItemById($itemId)
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

    public function clean()
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
     * @param array $quantitiesByItemId
     */
    public function changeQuantities(array $quantitiesByItemId)
    {
        foreach ($this->items as $item) {
            if (array_key_exists($item->getId(), $quantitiesByItemId)) {
                $item->changeQuantity((int)$quantitiesByItemId[$item->getId()]);
            }
        }

        $this->setModifiedNow();
    }

    /**
     * @param int $itemId
     * @return \Shopsys\FrameworkBundle\Model\Cart\Item\CartItem
     */
    public function getItemById($itemId)
    {
        foreach ($this->items as $item) {
            if ($item->getId() === $itemId) {
                return $item;
            }
        }
        $message = 'CartItem with id = ' . $itemId . ' not found in cart.';

        throw new InvalidCartItemException($message);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[]
     */
    public function getQuantifiedProducts()
    {
        $quantifiedProducts = [];

        foreach ($this->items as $item) {
            try {
                $quantifiedProducts[$item->getId()] = new QuantifiedProduct($item->getProduct(), $item->getQuantity());
            } catch (ProductNotFoundException) {
                continue;
            }
        }

        return $quantifiedProducts;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Item\CartItem $item
     * @return \Shopsys\FrameworkBundle\Model\Cart\Item\CartItem|null
     */
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
        $this->modifiedAt = new DateTime();
    }

    /**
     * @param \DateTime $modifiedAt
     */
    public function setModifiedAt($modifiedAt)
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

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode $promoCode
     */
    public function applyPromoCode(PromoCode $promoCode): void
    {
        if (!$this->promoCodes->contains($promoCode)) {
            $this->promoCodes->add($promoCode);
            $this->setModifiedNow();
        }
    }

    /**
     * @param int $promoCodeId
     */
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

    /**
     * @param string $promoCodeCode
     * @return bool
     */
    public function isPromoCodeApplied(string $promoCodeCode): bool
    {
        return $this->promoCodes->exists(
            static function ($key, PromoCode $promoCode) use ($promoCodeCode) {
                return $promoCode->getCode() === $promoCodeCode;
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

    /**
     * @param \Shopsys\FrontendApiBundle\Model\Cart\Transport\CartTransportData $cartTransportData
     */
    public function editCartTransport(CartTransportData $cartTransportData): void
    {
        $this->transport = $cartTransportData->transport;
        $this->transportWatchedPrice = $cartTransportData->watchedPrice;
        $this->pickupPlaceIdentifier = $cartTransportData->pickupPlaceIdentifier;
        $this->setModifiedNow();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Payment\CartPaymentData $cartPaymentData
     */
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

    /**
     * @return int
     */
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

    /**
     * @param string $itemUuid
     * @return \Shopsys\FrameworkBundle\Model\Cart\Item\CartItem
     */
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

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null $customerUser
     */
    public function assignCartToCustomerUser(?CustomerUser $customerUser): void
    {
        $this->customerUser = $customerUser;
        $this->cartIdentifier = '';
        $this->setModifiedNow();
    }
}
