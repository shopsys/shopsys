<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Migration;

use League\Flysystem\FilesystemOperator;
use League\Flysystem\MountManager;
use Shopsys\FrameworkBundle\Component\Cdn\CdnFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;
use Shopsys\FrameworkBundle\Model\Media\MediaDataFactory;
use Shopsys\FrameworkBundle\Model\Media\MediaFacade;
use Throwable;

class LegacyImageImporter
{
    /**
     * @var array<string, int|null>
     */
    protected array $mediaIdsByUrl = [];

    public function __construct(
        protected readonly string $contentDirName,
        protected readonly Domain $domain,
        protected readonly CdnFacade $cdnFacade,
        protected readonly FileUpload $fileUpload,
        protected readonly MountManager $mountManager,
        protected readonly FilesystemOperator $filesystem,
        protected readonly MediaFacade $mediaFacade,
        protected readonly MediaDataFactory $mediaDataFactory,
    ) {
    }

    /**
     * Copies an image stored in the web directory (wysiwyg uploads, entity images) to the media library
     *
     * @return int|null id of the created media, null when the file is not an image stored in the content directory (e.g. /content/wysiwyg/a.jpg)
     */
    public function import(string $url): ?int
    {
        if (!array_key_exists($url, $this->mediaIdsByUrl)) {
            $this->mediaIdsByUrl[$url] = $this->createMedia($url);
        }

        return $this->mediaIdsByUrl[$url];
    }

    protected function createMedia(string $url): ?int
    {
        $path = $this->getContentPath($url);

        if ($path === null || !$this->filesystem->fileExists('web' . $path)) {
            return null;
        }

        $filename = basename($path);
        $temporaryFilename = $this->fileUpload->getTemporaryFilename($filename);

        try {
            $this->mountManager->copy(
                'main://web' . $path,
                'main://' . $this->fileUpload->getTemporaryDirectory() . '/' . $temporaryFilename,
            );

            $mediaData = $this->mediaDataFactory->create();
            $mediaData->title = pathinfo($filename, PATHINFO_FILENAME);
            $mediaData->image->uploadedFiles = [$temporaryFilename];

            return $this->mediaFacade->create($mediaData)->getId();
        } catch (Throwable) {
            // not an image or an unreadable file, the converter keeps the original url
            $this->fileUpload->tryDeleteTemporaryFile($temporaryFilename);

            return null;
        }
    }

    protected function getContentPath(string $url): ?string
    {
        $path = $url;

        foreach ($this->domain->getAll() as $domainConfig) {
            $path = str_replace($domainConfig->getUrl(), '', $path);
        }

        $cdnDomain = $this->cdnFacade->getCdnDomain();

        if ($cdnDomain !== null) {
            $path = str_replace($cdnDomain, '', $path);
        }

        if (!str_starts_with($path, '/' . $this->contentDirName . '/')) {
            return null;
        }

        $path = rawurldecode((string)parse_url($path, PHP_URL_PATH));

        return str_contains($path, '..') ? null : $path;
    }
}
