<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\Database\Schema\Model;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[ORM\Table(name: self::TABLE_NAME)]
#[ORM\Entity]
#[AsMcpTable]
final class QueryableEntity
{
    public const string TABLE_NAME = 'test_mcp_queryable_entities';

    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    private int $id = 1;

    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    private int $domainId = 1;

    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 255)]
    private string $visibleValue = '';

    #[AsMcpColumn(exposed: false)]
    #[ORM\Column(type: 'string', length: 255)]
    private string $hiddenValue = '';

    #[AsMcpColumn]
    #[ORM\JoinColumn(name: 'queryable_relation_id', referencedColumnName: 'id', nullable: true)]
    #[ORM\ManyToOne(targetEntity: QueryableRelationEntity::class)]
    private ?QueryableRelationEntity $queryableRelation = null;

    #[AsMcpColumn(exposed: false)]
    #[ORM\JoinColumn(name: 'blacklisted_queryable_relation_id', referencedColumnName: 'id', nullable: true)]
    #[ORM\ManyToOne(targetEntity: QueryableRelationEntity::class)]
    private ?QueryableRelationEntity $blacklistedQueryableRelation = null;

    #[AsMcpColumn]
    #[ORM\JoinColumn(name: 'non_queryable_relation_id', referencedColumnName: 'id', nullable: true)]
    #[ORM\ManyToOne(targetEntity: NonQueryableRelationEntity::class)]
    private ?NonQueryableRelationEntity $nonQueryableRelation = null;

    #[AsMcpColumn]
    #[ORM\Embedded(class: QueryableEmbeddable::class, columnPrefix: 'embedded_')]
    private QueryableEmbeddable $embedded;

    #[AsMcpColumn(exposed: false)]
    #[ORM\Embedded(class: QueryableEmbeddable::class, columnPrefix: 'hidden_embedded_')]
    private QueryableEmbeddable $hiddenEmbedded;

    public function __construct()
    {
        $this->embedded = new QueryableEmbeddable();
        $this->hiddenEmbedded = new QueryableEmbeddable();
    }
}
