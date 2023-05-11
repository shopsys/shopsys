<?php

declare(strict_types=1);

namespace Shopsys\ReadModelBundle\Image;

class ImageView
{
    protected int $id;

    protected string $extension;

    protected string $entityName;

    protected ?string $type = null;

    protected ?string $name = null;

    /**
     * @param int $id
     * @param string $extension
     * @param string $entityName
     * @param string|null $type
     * @param string|null $name
     */
    public function __construct(int $id, string $extension, string $entityName, ?string $type, ?string $name = null)
    {
        $this->id = $id;
        $this->extension = $extension;
        $this->entityName = $entityName;
        $this->type = $type;
        $this->name = $name;
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
