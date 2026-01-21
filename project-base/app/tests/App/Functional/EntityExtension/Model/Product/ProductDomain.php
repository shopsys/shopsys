<?php

declare(strict_types=1);

namespace Tests\App\Functional\EntityExtension\Model\Product;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'product_domains')]
#[ORM\UniqueConstraint(name: 'product_domain', columns: ['product_id', 'domain_id'])]
#[ORM\Entity]
class ProductDomain
{
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected int $id;

    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'domains')]
    #[ORM\JoinColumn(nullable: false, name: 'product_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected Product $product;

    #[ORM\Column(type: 'integer')]
    protected int $domainId;

    #[ORM\Column(type: 'text', nullable: true)]
    protected ?string $description = null;
}
