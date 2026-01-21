<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Category\Category;

#[ORM\Table(name: 'product_category_domains')]
#[ORM\Index(columns: ['category_id', 'domain_id'])]
#[ORM\Entity]
class ProductCategoryDomain
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product
     */
    #[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'productCategoryDomains')]
    protected $product;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\Category
     */
    #[ORM\JoinColumn(name: 'category_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Category::class)]
    protected $category;

    /**
     * @var int
     */
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    protected $domainId;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @param int $domainId
     */
    public function __construct(Product $product, Category $category, $domainId)
    {
        $this->product = $product;
        $this->category = $category;
        $this->domainId = $domainId;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @return int
     */
    public function getDomainId()
    {
        return $this->domainId;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     */
    public function setProduct($product): void
    {
        $this->product = $product;
    }
}
