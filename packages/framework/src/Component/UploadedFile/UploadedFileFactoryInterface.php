<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile;

interface UploadedFileFactoryInterface
{
    /**
     * @param string $temporaryFilename
     * @param string $uploadedFilename
     * @param array<string, string> $namesIndexedByLocale
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile
     */
    public function create(
        string $temporaryFilename,
        string $uploadedFilename,
        array $namesIndexedByLocale = [],
    ): UploadedFile;

    /**
     * @param array $temporaryFilenames
     * @param array $uploadedFilenames
     * @param array<int, array<string, string>> $namesIndexedByFileIdAndLocale
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile[]
     */
    public function createMultiple(
        array $temporaryFilenames,
        array $uploadedFilenames,
        array $namesIndexedByFileIdAndLocale = [],
    ): array;
}
