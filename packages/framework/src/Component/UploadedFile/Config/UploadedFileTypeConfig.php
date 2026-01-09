<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile\Config;

class UploadedFileTypeConfig
{
    public const DEFAULT_TYPE_NAME = 'default';

    /**
     * @param string $name
     * @param bool $multiple
     * @param bool $requireFriendlyName
     */
    public function __construct(
        protected readonly string $name,
        protected readonly bool $multiple,
        protected readonly bool $requireFriendlyName,
    ) {
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isMultiple(): bool
    {
        return $this->multiple;
    }

    /**
     * @return bool
     */
    public function isRequiredFriendlyName(): bool
    {
        return $this->requireFriendlyName;
    }
}
