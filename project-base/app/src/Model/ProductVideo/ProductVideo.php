<?php

declare(strict_types=1);

namespace App\Model\ProductVideo;

use App\Model\Product\Product;
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
    protected int $id;

    /**
     * @var \App\Model\Product\Product
     * @ORM\ManyToOne(targetEntity="App\Model\Product\Product", inversedBy="productVideos")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private Product $product;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private string $videoToken;

    /**
     * @return string
     */
    public function getVideoToken(): string
    {
        return $this->videoToken;
    }

    /**
     * @param string $videoToken
     */
    public function setVideoToken(string $videoToken): void
    {
        $this->videoToken = $videoToken;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return \App\Model\Product\Product
     */
    public function getProduct(): Product
    {
        return $this->product;
    }

    /**
     * @param \App\Model\Product\Product $product
     */
    public function setProduct(Product $product): void
    {
        $this->product = $product;
    }
}
