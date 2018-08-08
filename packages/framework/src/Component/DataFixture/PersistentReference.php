<?php

namespace Shopsys\FrameworkBundle\Component\DataFixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * Product
 *
 * @ORM\Table(name="persistent_references")
 * @ORM\Entity
 */
class PersistentReference
{
    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     * @ORM\Id
     */
    protected $referenceName;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    protected $entityName;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $entityId;
    
    public function __construct(string $referenceName, string $entityName, int $entityId)
    {
        $this->referenceName = $referenceName;
        $this->entityName = $entityName;
        $this->entityId = $entityId;
    }
    
    public function replace(string $entityName, int $entityId): void
    {
        $this->entityName = $entityName;
        $this->entityId = $entityId;
    }

    public function getReferenceName(): string
    {
        return $this->referenceName;
    }

    public function getEntityName(): string
    {
        return $this->entityName;
    }

    public function getEntityId(): int
    {
        return $this->entityId;
    }
}
