<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Sitemap;

use League\Flysystem\FilesystemOperator;
use League\Flysystem\MountManager;
use Override;
use Presta\SitemapBundle\DependencyInjection\Configuration;
use Presta\SitemapBundle\Service\Dumper;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SitemapDumper extends Dumper
{
    public function __construct(
        EventDispatcherInterface $dispatcher,
        Filesystem $filesystem,
        protected readonly FilesystemOperator $abstractFilesystem,
        protected readonly MountManager $mountManager,
        protected readonly TransformStringHelper $transformStringHelper,
        UrlGeneratorInterface $urlGenerator,
        string $sitemapFilePrefix = Configuration::DEFAULT_FILENAME,
        ?int $itemsBySet = null,
    ) {
        parent::__construct($dispatcher, $filesystem, $urlGenerator, $sitemapFilePrefix, $itemsBySet);
    }

    /**
     * Moves sitemaps created in a temporary folder to their real location
     *
     * @param string $targetDir Directory to move created sitemaps to
     * @throws \RuntimeException
     */
    #[Override]
    protected function activate(string $targetDir): void
    {
        $this->deleteExistingSitemaps($targetDir);

        $finder = new Finder();
        $sitemapFileFinder = $finder
            ->files()
            ->name(sprintf('%s*.xml', $this->sitemapFilePrefix))
            ->in($this->tmpFolder);

        foreach ($sitemapFileFinder->getIterator() as $file) {
            $this->mountManager->move(
                'local://' . $this->transformStringHelper->removeDriveLetterFromPath($file->getPathname()),
                'main://' . $targetDir . '/' . $file->getBasename(),
            );
        }

        parent::cleanup();
    }

    /**
     * Deletes sitemap files matching filename patterns of newly generated files
     */
    #[Override]
    protected function deleteExistingSitemaps(string $targetDir): void
    {
        $files = $this->abstractFilesystem->listContents($targetDir)->filter(function ($file) {
            return str_contains($file['path'], $this->sitemapFilePrefix);
        });

        foreach ($files as $file) {
            $this->abstractFilesystem->delete($file['path']);
        }
    }
}
