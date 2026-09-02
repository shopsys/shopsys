<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\Model;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;
use Symfony\Component\Clock\DatePoint;

#[AsMcpTable]
#[ORM\Table(name: 'entity_log')]
#[ORM\Index(columns: ['entity_name'])]
#[ORM\Index(columns: ['entity_id'])]
#[ORM\Index(columns: ['parent_entity_name'])]
#[ORM\Index(columns: ['parent_entity_id'])]
#[ORM\Entity]
class EntityLog
{
    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 255)]
    protected $action;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 255)]
    protected $userIdentifier;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 255)]
    protected $entityName;

    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    protected $entityId;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 255)]
    protected $entityIdentifier;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 255)]
    protected $source;

    /**
     * @var string[]
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'json')]
    protected $changeSet;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $parentEntityName;

    /**
     * @var int|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer', nullable: true)]
    protected $parentEntityId;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 50)]
    protected $logCollectionNumber;

    /**
     * @var \DateTimeImmutable
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'datetime_immutable')]
    protected $createdAt;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'text', nullable: true)]
    protected $note;

    public function __construct(
        EntityLogData $entityLogData,
    ) {
        $this->setData($entityLogData);
        $this->createdAt = new DatePoint();
        $this->logCollectionNumber = '';
    }

    protected function setData(EntityLogData $entityLogData): void
    {
        $this->action = $entityLogData->action;
        $this->userIdentifier = $entityLogData->userIdentifier;
        $this->entityName = $entityLogData->entityName;
        $this->entityId = $entityLogData->entityId;
        $this->entityIdentifier = $entityLogData->entityIdentifier;
        $this->source = $entityLogData->source;
        $this->changeSet = $this->getSerializedChangeSet($entityLogData->changeSet);
        $this->parentEntityName = $entityLogData->parentEntityName;
        $this->parentEntityId = $entityLogData->parentEntityId;
        $this->note = $entityLogData->note;
    }

    protected function getSerializedChangeSet(array $changeSet): array
    {
        return json_decode(json_encode($changeSet), true);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return string
     */
    public function getUserIdentifier()
    {
        return $this->userIdentifier;
    }

    /**
     * @return string
     */
    public function getEntityName()
    {
        return $this->entityName;
    }

    /**
     * @return int
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * @return string
     */
    public function getEntityIdentifier()
    {
        return $this->entityIdentifier;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @return array
     */
    public function getChangeSet()
    {
        return $this->changeSet;
    }

    /**
     * @return string|null
     */
    public function getParentEntityName()
    {
        return $this->parentEntityName;
    }

    /**
     * @return int|null
     */
    public function getParentEntityId()
    {
        return $this->parentEntityId;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return string|null
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * @return string
     */
    public function getLogCollectionNumber()
    {
        return $this->logCollectionNumber;
    }

    /**
     * @param string $logCollectionNumber
     */
    public function setLogCollectionNumber($logCollectionNumber): void
    {
        $this->logCollectionNumber = $logCollectionNumber;
    }
}
