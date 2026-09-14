<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
final class DummyProductDomain
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    public int $id;

    #[ORM\ManyToOne(targetEntity: DummyProduct::class, inversedBy: 'domains')]
    #[ORM\JoinColumn(nullable: false)]
    public DummyProduct $product;

    #[ORM\Column(type: 'integer')]
    public int $domainId;

    #[ORM\Column(type: 'integer')]
    public int $priority;
}
