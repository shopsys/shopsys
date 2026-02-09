<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\CustomerUploadedFile;

use Ramsey\Uuid\Uuid;

class CustomerUploadedFileBatchLoadData
{
    protected string $id;

    public function __construct(
        protected readonly int $entityId,
        protected readonly string $entityName,
        protected readonly ?string $type,
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

    public function getType(): ?string
    {
        return $this->type;
    }
}
