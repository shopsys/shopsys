<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\Database\Schema\InvalidModel\PropertyLevelFieldName;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[ORM\Table(name: self::TABLE_NAME)]
#[ORM\Entity]
#[AsMcpTable]
final class PropertyLevelFieldNameEntity
{
    public const string TABLE_NAME = 'test_mcp_property_level_field_name_entities';

    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id = 0;

    #[AsMcpColumn(fieldName: 'name')]
    #[ORM\Column(type: 'string', length: 255)]
    private string $name = '';
}
