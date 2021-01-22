<?php

declare(strict_types=1);

namespace Tests\App\Functional\EntityExtension\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class ProductManyToManyBidirectionalEntity
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected int $id;

    /**
     * @var \Doctrine\Common\Collections\Collection|\Tests\App\Functional\EntityExtension\Model\ExtendedProduct[]
     * @ORM\ManyToMany(targetEntity="ExtendedProduct", mappedBy="manyToManyBidirectionalEntities")
     */
    protected Collection $products;

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
        $this->products = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return \Tests\App\Functional\EntityExtension\Model\ExtendedProduct[]
     */
    public function getProducts(): array
    {
        return $this->products->getValues();
    }

    /**
     * @param \Tests\App\Functional\EntityExtension\Model\ExtendedProduct $product
     */
    public function addProduct(ExtendedProduct $product): void
    {
        $this->products->add($product);
    }
}
