<?php

declare(strict_types=1);

namespace App\Model\Wishlist;

use App\Model\Customer\User\CustomerUser;
use App\Model\Product\Product;
use App\Model\Wishlist\Item\WishlistItem;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Table(name="wishlists")
 * @ORM\Entity
 */
class Wishlist
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
     * @ORM\Column(type="guid", unique=true)
     */
    private string $uuid;

    /**
     * @var \App\Model\Customer\User\CustomerUser|null
     * @ORM\ManyToOne(targetEntity="App\Model\Customer\User\CustomerUser")
     * @ORM\JoinColumn(name="customer_user_id", referencedColumnName="id", nullable = true, onDelete="CASCADE")
     */
    private ?CustomerUser $customerUser;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \App\Model\Wishlist\Item\WishlistItem>
     * @ORM\OneToMany(
     *     targetEntity="App\Model\Wishlist\Item\WishlistItem", mappedBy="wishlist"
     * )
     * @ORM\OrderBy({"createdAt" = "DESC"})
     */
    private Collection $items;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=false)
     */
    private DateTime $updatedAt;

    /**
     * @param \App\Model\Customer\User\CustomerUser|null $customerUser
     */
    public function __construct(?CustomerUser $customerUser)
    {
        $this->customerUser = $customerUser;
        $this->uuid = Uuid::uuid4()->toString();
        $this->items = new ArrayCollection();
        $this->setUpdatedAtToNow();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }

    /**
     * @return \App\Model\Customer\User\CustomerUser|null
     */
    public function getCustomerUser(): ?CustomerUser
    {
        return $this->customerUser;
    }

    /**
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     */
    public function setCustomerUser(CustomerUser $customerUser): void
    {
        $this->customerUser = $customerUser;
    }

    /**
     * @return \App\Model\Wishlist\Item\WishlistItem[]
     */
    public function getItems(): array
    {
        return $this->items->getValues();
    }

    /**
     * @return int
     */
    public function getItemsCount(): int
    {
        return $this->items->count();
    }

    /**
     * @param \App\Model\Wishlist\Item\WishlistItem $wishlistItem
     */
    public function addItem(WishlistItem $wishlistItem): void
    {
        $this->setUpdatedAtToNow();
        $this->items->add($wishlistItem);
    }

    /**
     * @param \App\Model\Wishlist\Item\WishlistItem $wishlistItem
     */
    public function removeItem(WishlistItem $wishlistItem): void
    {
        $this->setUpdatedAtToNow();
        $this->items->removeElement($wishlistItem);
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return bool
     */
    public function isProductInWishlist(Product $product): bool
    {
        foreach ($this->getItems() as $wishlistItem) {
            if ($wishlistItem->getProduct()->getId() === $product->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAtToNow(): void
    {
        $this->updatedAt = new DateTime();
    }
}
