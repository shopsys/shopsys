<?php

declare(strict_types=1);

namespace App\Model\Product\Package;

use App\Model\Product\Product;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(
 *     name="product_packages",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="product_package", columns={"product_id", "domain_id"})
 *     }
 * )
 *
 * @ORM\Entity
 */
class ProductPackage
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var \App\Model\Product\Product
     *
     * @ORM\ManyToOne(targetEntity="App\Model\Product\Product", inversedBy="domains")
     * @ORM\JoinColumn(nullable=false, name="product_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $product;

    /**
     * @var int|null
     * @ORM\Column(type="integer")
     */
    protected $position;

    /**
     * @var int|null
     * @ORM\Column(type="integer")
     */
    protected $length;

    /**
     * @var int|null
     * @ORM\Column(type="integer")
     */
    protected $width;

    /**
     * @var int|null
     * @ORM\Column(type="integer")
     */
    protected $height;

    /**
     * @var int|null
     * @ORM\Column(type="integer")
     */
    protected $weight;


    /**
     * @param \App\Model\Product\Product $product
     */
    public function __construct(Product $product)
    {
        $this->product = $product;
    }


    /**
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @param int $position
     */
    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    /**
     * @return int
     */
    public function getLength(): int
    {
        return $this->length;
    }

    /**
     * @param int $length
     */
    public function setLength(int $length): void
    {
        $this->length = $length;
    }

    /**
     * @return int
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * @param int $width
     */
    public function setWidth(int $width): void
    {
        $this->width = $width;
    }

    /**
     * @return int
     */
    public function getHeight(): int
    {
        return $this->height;
    }

    /**
     * @param int $height
     */
    public function setHeight(int $height): void
    {
        $this->height = $height;
    }

    /**
     * @return int
     */
    public function getWeight(): int
    {
        return $this->weight;
    }

    /**
     * @param int $weight
     */
    public function setWeight(int $weight): void
    {
        $this->weight = $weight;
    }
}
