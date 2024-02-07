<?php

declare(strict_types=1);

namespace Tests\App\Functional\EntityExtension\Model\ExtendedProduct;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class ProductOneToOneBidirectionalEntity
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected int $id;

    /**
     * @ORM\OneToOne(targetEntity="ExtendedProduct", inversedBy="oneToOneBidirectionalEntity")
     * @ORM\JoinColumn(nullable=false, name="product_id", referencedColumnName="id")
     */
    protected ExtendedProduct $product;

    /**
     * @ORM\Column(type="string")
     */
    protected string $name;

    /**
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return \Tests\App\Functional\EntityExtension\Model\ExtendedProduct\ExtendedProduct
     */
    public function getProduct(): ExtendedProduct
    {
        return $this->product;
    }

    /**
     * @param \Tests\App\Functional\EntityExtension\Model\ExtendedProduct\ExtendedProduct $product
     */
    public function setProduct(ExtendedProduct $product): void
    {
        $this->product = $product;
    }
}
