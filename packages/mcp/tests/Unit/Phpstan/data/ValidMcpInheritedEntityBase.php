<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Phpstan\Fixture;

use Doctrine\ORM\Mapping as ORM;

#[ORM\MappedSuperclass]
abstract class ValidMcpInheritedEntityBase
{
    #[ORM\Id]
    #[ORM\Column]
    protected int $id;
}
