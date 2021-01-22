<?php

declare(strict_types=1);

namespace Tests\App\Functional\EntityExtension\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class ProductOneToManyBidirectionalEntity
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected int $id;

    /**
     * @ORM\ManyToOne(targetEntity="ExtendedProduct", inversedBy="oneToManyBidirectionalEntity")
     * @ORM\JoinColumn(nullable=false, name="product_id", referencedColumnName="id")
     */
    protected ?ExtendedProduct $product = null;

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
     * @return \Tests\App\Functional\EntityExtension\Model\ExtendedProduct
     */
    public function getProduct(): ExtendedProduct
    {
        return $this->product;
    }

    /**
     * @param \Tests\App\Functional\EntityExtension\Model\ExtendedProduct $product
     */
    public function setProduct(ExtendedProduct $product): void
    {
        $this->product = $product;
    }
}
