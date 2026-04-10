<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Phpstan\Fixture;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[ORM\Entity]
#[AsMcpTable(exposed: true)]
class ValidMcpEntity
{
    #[ORM\Id]
    #[ORM\Column]
    #[AsMcpColumn(exposed: true)]
    public int $id;

    #[ORM\Column]
    #[AsMcpColumn(exposed: true)]
    public string $title;
}
