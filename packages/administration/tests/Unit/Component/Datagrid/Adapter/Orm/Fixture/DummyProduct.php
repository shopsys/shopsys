<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture;

use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
final class DummyProduct
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    public int $id;

    #[ORM\Column(type: 'string')]
    public string $name;

    #[ORM\Column(type: 'datetime_immutable')]
    public DateTimeImmutable $createdAt;

    #[ORM\ManyToOne(targetEntity: DummyCurrency::class)]
    #[ORM\JoinColumn(nullable: false)]
    public DummyCurrency $currency;

    #[ORM\ManyToOne(targetEntity: DummyCurrency::class)]
    #[ORM\JoinColumn(nullable: false)]
    public DummyCurrency $secondaryCurrency;

    #[ORM\ManyToOne(targetEntity: DummyWarehouse::class)]
    #[ORM\JoinColumn(nullable: false)]
    public ?DummyWarehouse $warehouse = null;

    #[ORM\OneToOne(targetEntity: DummyProductDetail::class, mappedBy: 'product')]
    public ?DummyProductDetail $detail = null;

    #[ORM\ManyToOne(targetEntity: DummyShelf::class)]
    #[ORM\JoinColumn(name: 'shelf_warehouse_code', referencedColumnName: 'warehouseCode', nullable: true)]
    #[ORM\JoinColumn(name: 'shelf_number', referencedColumnName: 'number', nullable: true)]
    public ?DummyShelf $shelf = null;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyProductDomain>
     */
    #[ORM\OneToMany(targetEntity: DummyProductDomain::class, mappedBy: 'product')]
    public Collection $domains;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyTag>
     */
    #[ORM\ManyToMany(targetEntity: DummyTag::class)]
    public Collection $tags;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyLabel>
     */
    #[ORM\ManyToMany(targetEntity: DummyLabel::class)]
    public Collection $labels;
}
