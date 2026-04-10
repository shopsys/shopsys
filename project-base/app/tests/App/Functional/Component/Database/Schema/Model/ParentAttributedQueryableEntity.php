<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\Database\Schema\Model;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[ORM\Table(name: self::TABLE_NAME)]
#[ORM\Entity]
#[AsMcpTable]
final class ParentAttributedQueryableEntity extends ParentAttributedQueryableEntityBase
{
    public const string TABLE_NAME = 'test_mcp_parent_attributed_queryable_entities';

    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 255)]
    private string $name = '';
}
