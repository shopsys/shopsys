<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Image;

use Ramsey\Uuid\Uuid;

class ImageBatchLoadData
{
    /**
     * @var string
     */
    protected string $id;

    /**
     * @param int $entityId
     * @param string $entityName
     * @param string|null $type
     */
    public function __construct(
        protected readonly int $entityId,
        protected readonly string $entityName,
        protected readonly ?string $type,
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
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }
}
