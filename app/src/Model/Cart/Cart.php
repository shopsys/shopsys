<?php

declare(strict_types=1);

namespace App\Model\Cart;

use App\Model\Cart\Item\CartItem;
use App\Model\Cart\Transport\CartTransportData;
use App\Model\Order\PromoCode\PromoCode;
use App\Model\Transport\Transport;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Cart\Cart as BaseCart;
use Shopsys\FrameworkBundle\Model\Cart\Exception\InvalidCartItemException;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct;

/**
 * @ORM\Table(name="carts")
 * @ORM\Entity
 * @property \App\Model\Customer\User\CustomerUser|null $customerUser
 * @property \App\Model\Cart\Item\CartItem[]|\Doctrine\Common\Collections\Collection $items
 * @method addItem(\App\Model\Cart\Item\CartItem $item)
 * @method \App\Model\Cart\Item\CartItem[] getItems()
 * @method \App\Model\Cart\Item\CartItem getItemById(int $itemId)
 * @method \App\Model\Cart\Item\CartItem|null findSimilarItemByItem(\App\Model\Cart\Item\CartItem $item)
 */
class Cart extends BaseCart
{
    /**
     * @var \App\Model\Order\PromoCode\PromoCode[]|\Doctrine\Common\Collections\ArrayCollection
     * @ORM\ManyToMany(
     *     targetEntity="\App\Model\Order\PromoCode\PromoCode"
     * )
     * @ORM\JoinTable(name="cart_promo_codes")
     * @ORM\OrderBy({"id" = "DESC"})
     */
    private $promoCodes;

    /**
     * @var \App\Model\Transport\Transport|null
     * @ORM\ManyToOne(targetEntity="App\Model\Transport\Transport")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private ?Transport $transport = null;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money|null
     * @ORM\Column(type="money", precision=20, scale=6, nullable=true)
     */
    private ?Money $transportWatchedPrice = null;

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $pickupPlaceIdentifier = null;

    /**
     * @param string $cartIdentifier
     * @param \App\Model\Customer\User\CustomerUser|null $customerUser
     */
    public function __construct(string $cartIdentifier, ?CustomerUser $customerUser = null)
    {
        parent::__construct($cartIdentifier, $customerUser);

        $this->promoCodes = new ArrayCollection();
    }

    /**
     * @inheritDoc
     */
    public function getQuantifiedProducts(): array
    {
        $quantifiedProducts = [];
        foreach ($this->items as $item) {
            $quantifiedProducts[$item->getId()] = new QuantifiedProduct($item->getProduct(), $item->getQuantity());
        }

        return $quantifiedProducts;
    }

    /**
     * @return int
     */
    public function getTotalWeight(): int
    {
        $totalWeight = 0;
        foreach ($this->items as $item) {
            $product = $item->getProduct();
            $totalWeight += $product->getWeight() * $item->getQuantity();
        }

        return $totalWeight;
    }

    /**
     * @param string $itemUuid
     * @return \App\Model\Cart\Item\CartItem
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
     * @return \App\Model\Order\PromoCode\PromoCode[]
     */
    public function getAllAppliedPromoCodes(): array
    {
        return $this->promoCodes->getValues();
    }

    /**
     * @return \App\Model\Order\PromoCode\PromoCode|null
     */
    public function getFirstAppliedPromoCode(): ?PromoCode
    {
        $firstAppliedPromoCode = $this->promoCodes->first();

        if ($firstAppliedPromoCode === false) {
            return null;
        }

        return $firstAppliedPromoCode;
    }

    /**
     * @param \App\Model\Order\PromoCode\PromoCode $promoCode
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
        $this->promoCodes->remove($promoCodeId);

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
            }
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
     * @param \App\Model\Cart\Transport\CartTransportData $cartTransportData
     */
    public function editCartTransport(CartTransportData $cartTransportData): void
    {
        $this->transport = $cartTransportData->transport;
        $this->transportWatchedPrice = $cartTransportData->watchedPrice;
        $this->pickupPlaceIdentifier = $cartTransportData->pickupPlaceIdentifier;
        $this->setModifiedNow();
    }

    /**
     * @return \App\Model\Transport\Transport|null
     */
    public function getTransport(): ?Transport
    {
        return $this->transport;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money|null
     */
    public function getTransportWatchedPrice(): ?Money
    {
        return $this->transportWatchedPrice;
    }

    /**
     * @return string|null
     */
    public function getPickupPlaceIdentifier(): ?string
    {
        return $this->pickupPlaceIdentifier;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money|null $transportWatchedPrice
     */
    public function setTransportWatchedPrice(?Money $transportWatchedPrice): void
    {
        $this->transportWatchedPrice = $transportWatchedPrice;
    }
}
