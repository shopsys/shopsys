<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Files;

use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileTypeConfig;

class FileBatchLoadData
{
    /**
     * @var string
     */
    protected string $id;

    /**
     * @param int $entityId
     * @param string $entityName
     * @param string $type
     */
    public function __construct(
        protected readonly int $entityId,
        protected readonly string $entityName,
        protected readonly string $type = UploadedFileTypeConfig::DEFAULT_TYPE_NAME,
    ) {
        $this->id = Uuid::uuid4()->toString();
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getEntityId(): int
    {
        return $this->entityId;
    }

    /**
     * @return string
     */
    public function getEntityName(): string
    {
        return $this->entityName;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }
}
