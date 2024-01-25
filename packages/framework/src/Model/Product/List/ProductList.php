<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\List;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Product;

/**
 * @ORM\Table(name="product_lists")
 * @ORM\Entity
 */
class ProductList
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
    protected $uuid;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser")
     * @ORM\JoinColumn(name="customer_user_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    protected $customerUser;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Product\List\ProductListItem>
     * @ORM\OneToMany(
     *     targetEntity="Shopsys\FrameworkBundle\Model\Product\List\ProductListItem", mappedBy="productList", cascade={"remove"}
     * )
     * @ORM\OrderBy({"createdAt" = "DESC", "id" = "DESC"})
     */
    protected $items;

    /**
     * @var \DateTimeImmutable
     * @ORM\Column(type="datetime_immutable", nullable=false)
     */
    protected $createdAt;

    /**
     * @var \DateTimeImmutable
     * @ORM\Column(type="datetime_immutable", nullable=false)
     */
    protected $updatedAt;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\List\ProductListTypeEnum
     * @ORM\Column(type="string", length=20, nullable=false, enumType="Shopsys\FrameworkBundle\Model\Product\List\ProductListTypeEnum")
     */
    protected $type;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\List\ProductListData $productListData
     */
    public function __construct(ProductListData $productListData)
    {
        $this->customerUser = $productListData->customerUser;
        $this->uuid = $productListData->uuid ?? Uuid::uuid4()->toString();
        $this->type = $productListData->type;
        $this->items = new ArrayCollection();
        $this->createdAt = new DateTimeImmutable();
        $this->setUpdatedAtToNow();
    }

    public function setUpdatedAtToNow(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\List\ProductListItem $productListItem
     */
    public function addItem(ProductListItem $productListItem): void
    {
        $this->setUpdatedAtToNow();
        $this->items->add($productListItem);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     */
    public function setCustomerUser(CustomerUser $customerUser): void
    {
        $this->setUpdatedAtToNow();
        $this->customerUser = $customerUser;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\List\ProductListItem $productListItem
     */
    public function removeItem(ProductListItem $productListItem): void
    {
        $this->setUpdatedAtToNow();
        $this->items->removeElement($productListItem);
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\List\ProductListTypeEnum
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return \Shopsys\FrameworkBundle\Model\Product\List\ProductListItem|null
     */
    public function findProductListItemByProduct(Product $product): ?ProductListItem
    {
        foreach ($this->items as $productListItem) {
            if ($productListItem->getProduct()->getId() === $product->getId()) {
                return $productListItem;
            }
        }

        return null;
    }

    /**
     * @return int
     */
    public function getItemsCount(): int
    {
        return $this->items->count();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\List\ProductListItem[]
     */
    public function getItems()
    {
        return $this->items->getValues();
    }
}
