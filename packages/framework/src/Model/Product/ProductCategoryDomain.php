<?php

namespace Shopsys\FrameworkBundle\Model\Product;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Category\Category;

/**
 * @ORM\Table(
 *     name="product_category_domains",
 *     indexes={@ORM\Index(columns={"category_id", "domain_id"})}
 * )
 * @ORM\Entity
 */
class ProductCategoryDomain
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Product\Product", inversedBy="productCategoryDomains")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $product;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\Category
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Category\Category")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $category;

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    protected $domainId;

    public function __construct(Product $product, Category $category, int $domainId)
    {
        $this->product = $product;
        $this->category = $category;
        $this->domainId = $domainId;
    }

    public function getCategory(): \Shopsys\FrameworkBundle\Model\Category\Category
    {
        return $this->category;
    }

    public function getDomainId(): int
    {
        return $this->domainId;
    }
}
