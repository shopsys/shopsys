<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\List;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Symfony\Component\Clock\DatePoint;

#[ORM\Table(name: 'product_list_items')]
#[ORM\Entity]
class ProductListItem
{
    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * @var string
     */
    #[ORM\Column(type: 'guid', unique: true)]
    protected $uuid;

    /**
     * @var \DateTimeImmutable
     */
    #[ORM\Column(type: 'datetime_immutable', nullable: false)]
    protected $createdAt;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product
     */
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Product::class)]
    protected $product;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\List\ProductList
     */
    #[ORM\JoinColumn(name: 'product_list_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: ProductList::class, inversedBy: 'items', cascade: ['persist'])]
    protected $productList;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\List\ProductList $productList
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     */
    public function __construct(ProductList $productList, Product $product)
    {
        $this->uuid = Uuid::uuid4()->toString();
        $this->productList = $productList;
        $this->product = $product;
        $this->createdAt = new DatePoint();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function getProduct()
    {
        return $this->product;
    }
}
