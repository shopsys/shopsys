<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile\Config;

class UploadedFileTypeConfig
{
    public const DEFAULT_TYPE_NAME = 'default';

    /**
     * @var string
     */
    protected $name;

    /**
     * @var bool
     */
    protected $multiple;

    /**
     * @param string $name
     * @param bool $multiple
     */
    public function __construct(string $name, bool $multiple)
    {
        $this->name = $name;
        $this->multiple = $multiple;
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
}
