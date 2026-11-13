<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\Database\Schema\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[ORM\Table(name: self::TABLE_NAME)]
#[ORM\Entity]
#[AsMcpTable]
final class QueryableRelationEntity
{
    public const string TABLE_NAME = 'test_mcp_queryable_relation_entities';
    public const string EXPOSED_JOIN_TABLE_NAME = 'test_mcp_queryable_relation_links';
    public const string HIDDEN_JOIN_TABLE_NAME = 'test_mcp_hidden_relation_links';

    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id = 0;

    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 255)]
    private string $name = '';

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Tests\App\Functional\Component\Database\Schema\Model\QueryableRelationEntity>
     */
    #[AsMcpTable]
    #[ORM\JoinTable(name: self::EXPOSED_JOIN_TABLE_NAME)]
    #[ORM\JoinColumn(name: 'relation_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'linked_relation_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: self::class)]
    private Collection $linkedRelations;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Tests\App\Functional\Component\Database\Schema\Model\QueryableRelationEntity>
     */
    #[AsMcpTable(exposed: false)]
    #[ORM\JoinTable(name: self::HIDDEN_JOIN_TABLE_NAME)]
    #[ORM\JoinColumn(name: 'relation_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'hidden_relation_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: self::class)]
    private Collection $hiddenLinkedRelations;

    public function __construct()
    {
        $this->linkedRelations = new ArrayCollection();
        $this->hiddenLinkedRelations = new ArrayCollection();
    }
}
