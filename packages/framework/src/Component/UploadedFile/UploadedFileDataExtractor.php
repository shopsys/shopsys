<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;

class UploadedFileDataExtractor
{
    public function __construct(
        protected readonly UploadedFileFacade $uploadedFileFacade,
    ) {
    }

    /**
     * @return array{url: string, anchorText: string}
     */
    public function extractUploadedFileData(UploadedFile $file, DomainConfig $domainConfig): array
    {
        $translatedName = $file->getTranslatedName($domainConfig->getLocale());

        return [
            'url' => $this->uploadedFileFacade->getUploadedFileUrl($domainConfig, $file),
            'anchorText' => $translatedName ?? $file->getName(),
        ];
    }
}
