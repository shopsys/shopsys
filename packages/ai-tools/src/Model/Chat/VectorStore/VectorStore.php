<?php

declare(strict_types=1);

namespace Shopsys\AiToolsBundle\Model\Chat\VectorStore;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Table(name="chat_vector_stores")
 * @ORM\Entity
 */
class VectorStore
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="guid", unique=true)
     */
    protected $uuid;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    protected $externalId;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * @var string[]
     * @ORM\Column(type="json")
     */
    protected $dataStructure;

    /**
     * @param \Shopsys\AiToolsBundle\Model\Chat\VectorStore\VectorStoreData $vectorStoreData
     */
    public function __construct(VectorStoreData $vectorStoreData)
    {
        $this->uuid = $vectorStoreData->uuid ?? Uuid::uuid4()->toString();
        $this->createdAt = new DateTime();
        $this->setData($vectorStoreData);
    }

    /**
     * @param \Shopsys\AiToolsBundle\Model\Chat\VectorStore\VectorStoreData $vectorStoreData
     */
    protected function setData(VectorStoreData $vectorStoreData): void
    {
        $this->name = $vectorStoreData->name;
        $this->description = $vectorStoreData->description;
        $this->externalId = $vectorStoreData->externalId;
        $this->dataStructure = $vectorStoreData->dataStructure;
    }

    /**
     * @param \Shopsys\AiToolsBundle\Model\Chat\VectorStore\VectorStoreData $vectorStoreData
     */
    public function edit(VectorStoreData $vectorStoreData): void
    {
        $this->setData($vectorStoreData);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getUuid()
    {
        return $this->uuid;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    public function getExternalId()
    {
        return $this->externalId;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return string[]
     */
    public function getDataStructure()
    {
        return $this->dataStructure;
    }
}
