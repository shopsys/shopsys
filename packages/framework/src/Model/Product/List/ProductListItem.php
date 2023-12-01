<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\List;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Model\Product\Product;

/**
 * @ORM\Table(name="product_list_items")
 * @ORM\Entity
 */
class ProductListItem
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
     * @var \DateTimeImmutable
     * @ORM\Column(type="datetime_immutable", nullable=false)
     */
    protected $createdAt;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Product\Product")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    protected $product;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\List\ProductList
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Product\List\ProductList", inversedBy="items", cascade={"persist"})
     * @ORM\JoinColumn(name="product_list_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
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
        $this->createdAt = new DateTimeImmutable();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function getProduct(): Product
    {
        return $this->product;
    }
}
