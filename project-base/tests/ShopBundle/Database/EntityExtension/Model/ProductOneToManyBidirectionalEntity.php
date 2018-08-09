<?php

namespace Tests\ShopBundle\Database\EntityExtension\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class ProductOneToManyBidirectionalEntity
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
     * @var \Tests\ShopBundle\Database\EntityExtension\Model\ExtendedProduct
     *
     * @ORM\ManyToOne(targetEntity="ExtendedProduct", inversedBy="oneToManyBidirectionalEntity")
     * @ORM\JoinColumn(nullable=false, name="product_id", referencedColumnName="id")
     */
    protected $product;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getProduct(): ExtendedProduct
    {
        return $this->product;
    }

    public function setProduct(ExtendedProduct $product): void
    {
        $this->product = $product;
    }
}
