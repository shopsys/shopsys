<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ProductVideo;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="product_videos")
 * @ORM\Entity
 */
class ProductVideo
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
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Product\Product", inversedBy="productVideos")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $product;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
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
    public function setVideoToken($videoToken): void
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
    public function setId($id): void
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
