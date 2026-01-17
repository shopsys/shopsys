<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Files;

use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileTypeConfig;

class FileBatchLoadData
{
    protected string $id;

    public function __construct(
        protected readonly int $entityId,
        protected readonly string $entityName,
        protected readonly string $type = UploadedFileTypeConfig::DEFAULT_TYPE_NAME,
    ) {
        $this->id = Uuid::uuid4()->toString();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getEntityId(): int
    {
        return $this->entityId;
    }

    public function getEntityName(): string
    {
        return $this->entityName;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
