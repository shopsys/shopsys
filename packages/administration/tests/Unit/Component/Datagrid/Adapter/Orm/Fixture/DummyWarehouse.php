<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * The identifier is deliberately not named "id", so that joining an association never assumes it is.
 */
#[ORM\Entity]
final class DummyWarehouse
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    public int $code;

    #[ORM\Column(type: 'string')]
    public string $name;
}
