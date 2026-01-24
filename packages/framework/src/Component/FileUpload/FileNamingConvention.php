<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\FileUpload;

use Shopsys\FrameworkBundle\Component\FileUpload\Exception\UnresolvedNamingConventionException;

class FileNamingConvention
{
    public const TYPE_ID = 1;
    protected const TYPE_ORIGINAL_NAME = 2;

    public function getFilenameByNamingConvention(
        int $namingConventionType,
        string $originalFilename,
        ?int $entityId = null,
    ): string {
        if ($namingConventionType === self::TYPE_ID && is_int($entityId)) {
            return $entityId . '.' . pathinfo($originalFilename, PATHINFO_EXTENSION);
        }

        if ($namingConventionType === static::TYPE_ORIGINAL_NAME) {
            return $originalFilename;
        }
        $message = 'Naming convention ' . $namingConventionType . ' cannot by resolved to filename';

        throw new UnresolvedNamingConventionException($message);
    }
}
