<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Image;

use Ramsey\Uuid\Uuid;

class ImageBatchLoadData
{
    /**
     * @var string
     */
    private string $id;

    /**
     * @param int $entityId
     * @param string $entityName
     * @param \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig[] $sizeConfigs
     * @param string|null $type
     */
    public function __construct(
        private int $entityId,
        private string $entityName,
        private array $sizeConfigs,
        private ?string $type,
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

    /**
     * @return \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig[]
     */
    public function getSizeConfigs(): array
    {
        return $this->sizeConfigs;
    }
}
