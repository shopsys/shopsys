<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\Database\Schema\InvalidModel\UnknownFieldName;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\McpAttributes\Attribute\AsMcpInheritedColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[ORM\Table(name: self::TABLE_NAME)]
#[ORM\Entity]
#[AsMcpTable]
#[AsMcpInheritedColumn(fieldName: 'missingField')]
final class UnknownFieldNameEntity
{
    public const string TABLE_NAME = 'test_mcp_unknown_field_name_entities';

    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id = 0;
}
