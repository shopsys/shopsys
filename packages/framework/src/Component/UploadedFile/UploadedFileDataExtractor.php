<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;

class UploadedFileDataExtractor
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade
     */
    public function __construct(
        protected readonly UploadedFileFacade $uploadedFileFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile $file
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
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
