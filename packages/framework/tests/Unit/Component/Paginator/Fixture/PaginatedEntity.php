<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Paginator\Fixture;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Component\Money\Money;

#[ORM\Entity]
final class PaginatedEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    public int $id;

    #[ORM\Column(type: 'money', precision: 20, scale: 6)]
    public Money $price;

    #[ORM\Column(type: 'string')]
    public string $name;
}
