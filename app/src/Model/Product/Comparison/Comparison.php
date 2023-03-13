<?php

declare(strict_types=1);

namespace App\Model\Product\Comparison;

use App\Model\Customer\User\CustomerUser;
use App\Model\Product\Comparison\Exception\ComparedItemNotFoundException;
use App\Model\Product\Comparison\Exception\HandlingWithOtherLoggedCustomerComparisonException;
use App\Model\Product\Comparison\Item\ComparedItem;
use App\Model\Product\Product;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Table(name="comparisons")
 * @ORM\Entity
 */
class Comparison
{
    public const DEFAULT_COMPARISON_LIFETIME_DAYS = 31;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected int $id;

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
     * @var \Doctrine\Common\Collections\Collection<int, \App\Model\Product\Comparison\Item\ComparedItem>
     * @ORM\OneToMany(
     *     targetEntity="App\Model\Product\Comparison\Item\ComparedItem", mappedBy="comparison"
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
        if ($this->customerUser !== null) {
            throw new HandlingWithOtherLoggedCustomerComparisonException();
        }

        $this->customerUser = $customerUser;
    }

    /**
     * @return \App\Model\Product\Comparison\Item\ComparedItem[]
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
     * @param \App\Model\Product\Comparison\Item\ComparedItem $comparedItem
     */
    public function addItem(ComparedItem $comparedItem): void
    {
        $this->setUpdatedAtToNow();
        $this->items->add($comparedItem);
    }

    /**
     * @param \App\Model\Product\Comparison\Item\ComparedItem $comparedItem
     */
    public function removeItem(ComparedItem $comparedItem): void
    {
        $this->setUpdatedAtToNow();
        $this->items->removeElement($comparedItem);
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return \App\Model\Product\Comparison\Item\ComparedItem
     */
    public function getComparedItemByProduct(Product $product): ComparedItem
    {
        foreach ($this->getItems() as $comparedItem) {
            if ($comparedItem->getProduct()->getId() === $product->getId()) {
                return $comparedItem;
            }
        }

        throw new ComparedItemNotFoundException(sprintf('Product %s not found in the comparison.', $product->getName()));
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return bool
     */
    public function isProductInComparison(Product $product): bool
    {
        foreach ($this->getItems() as $comparedItem) {
            if ($comparedItem->getProduct()->getId() === $product->getId()) {
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
