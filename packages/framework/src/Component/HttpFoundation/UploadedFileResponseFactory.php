<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\HttpFoundation;

use League\Flysystem\FilesystemOperator;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UploadedFileResponseFactory
{
    public function __construct(
        protected readonly FilesystemOperator $filesystem,
    ) {
    }

    public function createDownloadResponse(string $filePath, string $filename): DownloadFileResponse
    {
        return new DownloadFileResponse(
            $filename,
            $this->filesystem->read($filePath),
            $this->filesystem->mimeType($filePath),
        );
    }

    public function createInlineViewResponse(string $filePath, string $filename, string $mimeType): StreamedResponse
    {
        return new StreamedResponse(function () use ($filePath): void {
            $stream = $this->filesystem->readStream($filePath);
            fpassthru($stream);
            fclose($stream);
        }, 200, [
            'Content-Type' => $mimeType,
            'Content-Length' => $this->filesystem->fileSize($filePath),
            'Content-Disposition' => HeaderUtils::makeDisposition(HeaderUtils::DISPOSITION_INLINE, $filename),
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
