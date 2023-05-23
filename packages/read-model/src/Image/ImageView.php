<?php

declare(strict_types=1);

namespace Shopsys\ReadModelBundle\Image;

class ImageView
{
    /**
     * @param int $id
     * @param string $extension
     * @param string $entityName
     * @param string|null $type
     * @param string|null $name
     */
    public function __construct(
        protected readonly int $id,
        protected readonly string $extension,
        protected readonly string $entityName,
        protected readonly ?string $type = null,
        protected readonly ?string $name = null,
    ) {
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getExtension(): string
    {
        return $this->extension;
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
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }
}
