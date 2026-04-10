<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\Database\Schema\Model;

use Doctrine\ORM\Mapping as ORM;

#[ORM\MappedSuperclass]
abstract class InheritedQueryableEntityBase
{
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected int $id = 0;

    #[ORM\Column(type: 'string', length: 10)]
    protected string $locale = 'en';
}
