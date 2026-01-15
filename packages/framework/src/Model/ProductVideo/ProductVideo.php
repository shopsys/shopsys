<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ProductVideo;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Product\Product;

#[ORM\Table(name: 'product_videos')]
#[ORM\Entity]
class ProductVideo
{
    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product
     */
    #[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'productVideos')]
    protected $product;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string')]
    protected $videoToken;

    /**
     * @return string
     */
    public function getVideoToken()
    {
        return $this->videoToken;
    }

    /**
     * @param string $videoToken
     */
    public function setVideoToken($videoToken)
    {
        $this->videoToken = $videoToken;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     */
    public function setProduct($product): void
    {
        $this->product = $product;
    }
}
