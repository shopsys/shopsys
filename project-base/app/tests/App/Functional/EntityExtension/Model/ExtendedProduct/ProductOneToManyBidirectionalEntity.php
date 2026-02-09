<?php

declare(strict_types=1);

namespace Tests\App\Functional\EntityExtension\Model\ExtendedProduct;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class ProductOneToManyBidirectionalEntity
{
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected int $id;

    #[ORM\ManyToOne(targetEntity: ExtendedProduct::class, inversedBy: 'oneToManyBidirectionalEntities')]
    #[ORM\JoinColumn(nullable: false, name: 'product_id', referencedColumnName: 'id')]
    protected ExtendedProduct $product;

    #[ORM\Column(type: 'string')]
    protected string $name;

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
