<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * Identified by two fields, so that the identifier of an association leading to it is never read as a single column.
 */
#[ORM\Entity]
final class DummyShelf
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    public int $warehouseCode;

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    public int $number;

    #[ORM\Column(type: 'string')]
    public string $label;
}
