<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
final class DummyAddress
{
    #[ORM\Column(type: 'string')]
    public string $city;

    #[ORM\Column(type: 'string')]
    public string $country;
}
