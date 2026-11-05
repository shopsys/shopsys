<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
final class DummyCurrency
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    public int $id;

    #[ORM\Column(type: 'string')]
    public string $code;

    #[ORM\ManyToOne(targetEntity: DummyCurrencyFormat::class)]
    #[ORM\JoinColumn(nullable: false)]
    public DummyCurrencyFormat $format;
}
