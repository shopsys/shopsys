<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile\Config;

class UploadedFileTypeConfig
{
    public const DEFAULT_TYPE_NAME = 'default';

    public function __construct(
        protected readonly string $name,
        protected readonly bool $multiple,
        protected readonly bool $requireFriendlyName,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isMultiple(): bool
    {
        return $this->multiple;
    }

    public function isRequiredFriendlyName(): bool
    {
        return $this->requireFriendlyName;
    }
}
