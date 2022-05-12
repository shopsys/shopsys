<?php

namespace Shopsys\FrameworkBundle\Model\Sitemap;

use League\Flysystem\FilesystemInterface;
use League\Flysystem\MountManager;
use Presta\SitemapBundle\DependencyInjection\Configuration;
use Presta\SitemapBundle\Service\Dumper;
use Shopsys\FrameworkBundle\Component\String\TransformString;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SitemapDumper extends Dumper
{
    /**
     * @var \League\Flysystem\FilesystemInterface
     */
    protected $abstractFilesystem;

    /**
     * @var \League\Flysystem\MountManager
     */
    protected $mountManager;

    /**
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
     * @param \Symfony\Component\Filesystem\Filesystem $filesystem
     * @param \League\Flysystem\FilesystemInterface $abstractFilesystem
     * @param \League\Flysystem\MountManager $mountManager
     * @param \Symfony\Component\Routing\Generator\UrlGeneratorInterface $urlGenerator
     * @param string $sitemapFilePrefix
     * @param int|null $itemsBySet
     */
    public function __construct(
        EventDispatcherInterface $dispatcher,
        Filesystem $filesystem,
        FilesystemInterface $abstractFilesystem,
        MountManager $mountManager,
        UrlGeneratorInterface $urlGenerator,
        string $sitemapFilePrefix = Configuration::DEFAULT_FILENAME,
        ?int $itemsBySet = null
    ) {
        parent::__construct($dispatcher, $filesystem, $sitemapFilePrefix, $itemsBySet, $urlGenerator);

        $this->abstractFilesystem = $abstractFilesystem;
        $this->mountManager = $mountManager;
    }

    /**
     * Moves sitemaps created in a temporary folder to their real location
     *
     * @param string $targetDir Directory to move created sitemaps to
     * @throws \RuntimeException
     */
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
                'local://' . TransformString::removeDriveLetterFromPath($file->getPathname()),
                'main://' . $targetDir . '/' . $file->getBasename()
            );
        }

        parent::cleanup();
    }

    /**
     * Deletes sitemap files matching filename patterns of newly generated files
     *
     * @param string $targetDir
     */
    protected function deleteExistingSitemaps(string $targetDir): void
    {
        $files = array_filter($this->abstractFilesystem->listContents($targetDir), function ($file) {
            return strpos($file['filename'], $this->sitemapFilePrefix) === 0;
        });
        foreach ($files as $file) {
            $this->abstractFilesystem->delete($file['path']);
        }
    }
}
