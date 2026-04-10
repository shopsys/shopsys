<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\Database\Schema\InvalidModel\MissingFieldName;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[ORM\Table(name: self::TABLE_NAME)]
#[ORM\Entity]
#[AsMcpTable]
#[AsMcpColumn]
final class MissingFieldNameEntity
{
    public const string TABLE_NAME = 'test_mcp_missing_field_name_entities';

    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id = 0;
}
