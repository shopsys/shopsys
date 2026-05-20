<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Phpstan\Fixture;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpInheritedColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[ORM\Entity]
#[AsMcpTable(exposed: true)]
class InvalidMcpEntity
{
    #[ORM\Id]
    #[ORM\Column]
    #[AsMcpColumn(exposed: true)]
    public int $id;

    #[ORM\Column]
    public string $title;

    #[ORM\Column]
    public string $missingExposure;
}

#[ORM\Entity]
#[AsMcpTable(exposed: true)]
#[AsMcpInheritedColumn(fieldName: 'unknownField', exposed: true)]
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
#[AsMcpInheritedColumn(fieldName: 'id', exposed: true)]
#[AsMcpInheritedColumn(fieldName: 'id', exposed: false)]
class DuplicateInheritedFieldNameMcpEntity extends DuplicateInheritedFieldNameMcpEntityBase
{
    #[ORM\Column]
    #[AsMcpColumn(exposed: true)]
    public string $title;
}
