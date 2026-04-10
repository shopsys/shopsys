<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Phpstan\Fixture;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[ORM\Entity]
#[AsMcpTable(exposed: true)]
#[AsMcpColumn(exposed: true)]
class InvalidMcpEntity
{
    #[ORM\Id]
    #[ORM\Column]
    #[AsMcpColumn(exposed: true)]
    public int $id;

    #[ORM\Column]
    #[AsMcpColumn(fieldName: 'title', exposed: true)]
    public string $title;

    #[ORM\Column]
    public string $missingExposure;
}

#[ORM\Entity]
#[AsMcpTable(exposed: true)]
#[AsMcpColumn(fieldName: 'unknownField', exposed: true)]
class InvalidUnknownFieldMcpEntity
{
    #[ORM\Id]
    #[ORM\Column]
    #[AsMcpColumn(exposed: true)]
    public int $id;

    #[ORM\Column]
    #[AsMcpColumn(exposed: true)]
    public string $title;
}

#[ORM\Entity]
class MissingTableExposureEntity
{
    #[ORM\Id]
    #[ORM\Column]
    public int $id;
}

#[ORM\Entity]
#[AsMcpTable(exposed: true)]
#[AsMcpColumn(fieldName: 'title', exposed: true)]
#[AsMcpColumn(fieldName: 'title', exposed: false)]
class DuplicateFieldNameMcpEntity
{
    #[ORM\Id]
    #[ORM\Column]
    #[AsMcpColumn(exposed: true)]
    public int $id;

    #[ORM\Column]
    #[AsMcpColumn(exposed: true)]
    public string $title;
}
