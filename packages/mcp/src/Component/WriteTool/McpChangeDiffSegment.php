<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Component\WriteTool;

final class McpChangeDiffSegment
{
    public const string TYPE_EQUAL = 'equal';
    public const string TYPE_DELETED = 'deleted';
    public const string TYPE_INSERTED = 'inserted';

    public function __construct(
        public readonly string $type,
        public readonly string $text,
    ) {
    }

    public function isEqual(): bool
    {
        return $this->type === self::TYPE_EQUAL;
    }

    public function isDeleted(): bool
    {
        return $this->type === self::TYPE_DELETED;
    }

    public function isInserted(): bool
    {
        return $this->type === self::TYPE_INSERTED;
    }
}
