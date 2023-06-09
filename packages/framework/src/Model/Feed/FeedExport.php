<?php

namespace Shopsys\FrameworkBundle\Model\Feed;

use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\MountManager;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\String\TransformString;
use Symfony\Component\Filesystem\Filesystem;

class FeedExport
{
    protected const TEMPORARY_FILENAME_SUFFIX = '.tmp';
    protected const BATCH_SIZE = 1000;

    protected FeedInterface $feed;

    protected DomainConfig $domainConfig;

    protected FeedRenderer $feedRenderer;

    protected FilesystemOperator $filesystem;

    protected Filesystem $localFilesystem;

    protected MountManager $mountManager;

    protected EntityManagerInterface $em;

    protected string $feedFilepath;

    protected string $feedLocalFilepath;

    protected ?int $lastSeekId = null;

    protected bool $finished = false;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Feed\FeedInterface $feed
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param \Shopsys\FrameworkBundle\Model\Feed\FeedRenderer $feedRenderer
     * @param \League\Flysystem\FilesystemOperator $filesystem
     * @param \Symfony\Component\Filesystem\Filesystem $localFilesystem
     * @param \League\Flysystem\MountManager $mountManager
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param string $feedFilepath
     * @param string $feedLocalFilepath
     * @param int|null $lastSeekId
     */
    public function __construct(
        FeedInterface $feed,
        DomainConfig $domainConfig,
        FeedRenderer $feedRenderer,
        FilesystemOperator $filesystem,
        Filesystem $localFilesystem,
        MountManager $mountManager,
        EntityManagerInterface $em,
        string $feedFilepath,
        string $feedLocalFilepath,
        ?int $lastSeekId
    ) {
        $this->feed = $feed;
        $this->domainConfig = $domainConfig;
        $this->feedRenderer = $feedRenderer;
        $this->filesystem = $filesystem;
        $this->localFilesystem = $localFilesystem;
        $this->mountManager = $mountManager;
        $this->em = $em;
        $this->feedFilepath = $feedFilepath;
        $this->feedLocalFilepath = $feedLocalFilepath;
        $this->lastSeekId = $lastSeekId;
    }

    public function wakeUp(): void
    {
        if ($this->filesystem->has($this->getTemporaryFilepath())) {
            $this->mountManager->move(
                'main://' . $this->getTemporaryFilepath(),
                'local://' . TransformString::removeDriveLetterFromPath($this->getTemporaryLocalFilepath())
            );
        } else {
            $this->localFilesystem->touch($this->getTemporaryLocalFilepath());
        }
    }

    public function sleep(): void
    {
        $this->mountManager->move(
            'local://' . TransformString::removeDriveLetterFromPath($this->getTemporaryLocalFilepath()),
            'main://' . $this->getTemporaryFilepath()
        );
    }

    public function generateBatch(): void
    {
        if ($this->finished) {
            return;
        }

        $itemsInBatch = $this->feed->getItems($this->domainConfig, $this->lastSeekId, static::BATCH_SIZE);

        if ($this->lastSeekId === null) {
            $this->writeToFeed($this->feedRenderer->renderBegin($this->domainConfig));
        }

        $countInBatch = 0;
        foreach ($itemsInBatch as $item) {
            $this->writeToFeed($this->feedRenderer->renderItem($this->domainConfig, $item));
            $this->lastSeekId = $item->getSeekId();
            $countInBatch++;
        }

        if ($countInBatch < static::BATCH_SIZE) {
            $this->writeToFeed($this->feedRenderer->renderEnd($this->domainConfig));
            $this->finishFile();
        }

        $this->em->clear();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface
     */
    public function getFeedInfo(): FeedInfoInterface
    {
        return $this->feed->getInfo();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig
     */
    public function getDomainConfig(): DomainConfig
    {
        return $this->domainConfig;
    }

    /**
     * @return int|null
     */
    public function getLastSeekId(): ?int
    {
        return $this->lastSeekId;
    }

    /**
     * @return bool
     */
    public function isFinished(): bool
    {
        return $this->finished;
    }

    protected function finishFile(): void
    {
        if ($this->filesystem->has($this->feedFilepath)) {
            $this->filesystem->delete($this->feedFilepath);
        }

        $this->mountManager->move(
            'local://' . TransformString::removeDriveLetterFromPath($this->getTemporaryLocalFilepath()),
            'main://' . $this->feedFilepath
        );

        $this->finished = true;
    }

    /**
     * @param string $content
     */
    protected function writeToFeed(string $content): void
    {
        $this->localFilesystem->appendToFile($this->getTemporaryLocalFilepath(), $content);
    }

    /**
     * @return string
     */
    protected function getTemporaryFilepath(): string
    {
        return $this->feedFilepath . static::TEMPORARY_FILENAME_SUFFIX;
    }

    /**
     * @return string
     */
    protected function getTemporaryLocalFilepath(): string
    {
        return $this->feedLocalFilepath . '_local' . static::TEMPORARY_FILENAME_SUFFIX;
    }
}
