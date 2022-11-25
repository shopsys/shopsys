<?php

namespace Shopsys\FrameworkBundle\Model\Product\BestsellingProduct;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Product\Product;

/**
 * @ORM\Table(
 *     name="products_manual_bestselling",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(columns={"product_id", "category_id", "domain_id"}),
 *         @ORM\UniqueConstraint(columns={"position", "category_id", "domain_id"})
 *     }
 * )
 * @ORM\Entity
 */
class ManualBestsellingProduct
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Product\Product")
     * @ORM\JoinColumn(nullable=false, name="product_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $product;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\Category
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Category\Category", inversedBy="domains")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    protected $category;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $domainId;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $position;

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param int $position
     */
    public function __construct($domainId, Category $category, Product $product, $position)
    {
        $this->product = $product;
        $this->category = $category;
        $this->domainId = $domainId;
        $this->position = $position;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function getProduct(): Product
    {
        return $this->product;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\Category
     */
    public function getCategory(): Category
    {
        return $this->category;
    }

    /**
     * @return int
     */
    public function getDomainId(): int
    {
        return $this->domainId;
    }

    /**
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }
}
