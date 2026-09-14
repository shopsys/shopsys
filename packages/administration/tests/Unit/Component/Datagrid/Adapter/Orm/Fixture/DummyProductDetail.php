<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * Owns the one-to-one association with the product, so the product holds its inverse side.
 */
#[ORM\Entity]
final class DummyProductDetail
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    public int $id;

    #[ORM\OneToOne(targetEntity: DummyProduct::class, inversedBy: 'detail')]
    #[ORM\JoinColumn(nullable: false)]
    public DummyProduct $product;

    #[ORM\Column(type: 'string')]
    public string $note;
}
