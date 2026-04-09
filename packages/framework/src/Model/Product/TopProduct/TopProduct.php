<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\TopProduct;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[AsMcpTable]
#[ORM\Table(name: 'products_top')]
#[ORM\Entity]
class TopProduct
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(nullable: false, name: 'product_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Product::class)]
    #[ORM\Id]
    protected $product;

    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    protected $domainId;

    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    protected $position;

    /**
     * @param int $domainId
     * @param int $position
     */
    public function __construct(Product $product, $domainId, $position)
    {
        $this->product = $product;
        $this->domainId = $domainId;
        $this->position = $position;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }
}
