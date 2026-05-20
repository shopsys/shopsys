<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\List;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;
use Symfony\Component\Clock\DatePoint;

#[AsMcpTable]
#[ORM\Table(name: 'product_lists')]
#[ORM\Entity]
class ProductList
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
    #[ORM\Column(type: 'guid', unique: true)]
    protected $uuid;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(name: 'customer_user_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: CustomerUser::class)]
    protected $customerUser;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Product\List\ProductListItem>
     */
    #[ORM\OneToMany(targetEntity: ProductListItem::class, mappedBy: 'productList', cascade: ['remove'])]
    #[ORM\OrderBy(['createdAt' => 'DESC', 'id' => 'DESC'])]
    protected $items;

    /**
     * @var \DateTimeImmutable
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'datetime_immutable', nullable: false)]
    protected $createdAt;

    /**
     * @var \DateTimeImmutable
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'datetime_immutable', nullable: false)]
    protected $updatedAt;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 20, nullable: false)]
    protected $type;

    public function __construct(ProductListData $productListData)
    {
        $this->customerUser = $productListData->customerUser;
        $this->uuid = $productListData->uuid ?? Uuid::uuid4()->toString();
        $this->type = $productListData->type;
        $this->items = new ArrayCollection();
        $this->createdAt = new DatePoint();
        $this->updatedAt = new DatePoint();
    }

    public function setUpdatedAtToNow(): void
    {
        $this->updatedAt = new DatePoint();
    }

    public function addItem(ProductListItem $productListItem): void
    {
        $this->setUpdatedAtToNow();
        $this->items->add($productListItem);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     */
    public function setCustomerUser($customerUser): void
    {
        $this->setUpdatedAtToNow();
        $this->customerUser = $customerUser;
    }

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
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    public function findProductListItemByProduct(Product $product): ?ProductListItem
    {
        foreach ($this->items as $productListItem) {
            if ($productListItem->getProduct()->getId() === $product->getId()) {
                return $productListItem;
            }
        }

        return null;
    }

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
