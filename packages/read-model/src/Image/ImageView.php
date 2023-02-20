<?php

declare(strict_types=1);

namespace Shopsys\ReadModelBundle\Image;

class ImageView
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $extension;

    /**
     * @var string
     */
    protected $entityName;

    /**
     * @var string|null
     */
    protected $type;

    /**
     * @var string|null
     */
    protected $name;

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
