<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Feed;

use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\MountManager;
use Psr\Log\LoggerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;
use Shopsys\FrameworkBundle\DependencyInjection\ServicesResetter;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrentCurrencyProvider;
use Symfony\Component\Filesystem\Filesystem;

class FeedExport
{
    protected const string TEMPORARY_FILENAME_SUFFIX = '.tmp';
    protected const int BATCH_SIZE = 1000;

    protected bool $finished = false;

    public function __construct(
        protected readonly FeedInterface $feed,
        protected readonly DomainConfig $domainConfig,
        protected readonly FeedRenderer $feedRenderer,
        protected readonly FilesystemOperator $filesystem,
        protected readonly Filesystem $localFilesystem,
        protected readonly MountManager $mountManager,
        protected readonly EntityManagerInterface $em,
        protected readonly TransformStringHelper $transformStringHelper,
        protected readonly string $feedFilepath,
        protected readonly string $feedLocalFilepath,
        protected readonly ServicesResetter $servicesResetter,
        protected readonly LoggerInterface $logger,
        protected readonly CurrentCurrencyProvider $currentCurrencyProvider,
        protected readonly string $currencyCode,
        protected ?int $lastSeekId = null,
    ) {
    }

    public function getCurrencyCode(): string
    {
        return $this->currencyCode;
    }

    public function wakeUp(): void
    {
        if ($this->filesystem->has($this->getTemporaryFilepath())) {
            try {
                $this->mountManager->move(
                    'main://' . $this->getTemporaryFilepath(),
                    'local://' . $this->transformStringHelper->removeDriveLetterFromPath($this->getTemporaryLocalFilepath()),
                );

                return;
            } catch (FilesystemException $exception) {
                $this->handleWakeUpFailure($exception);
            }
        }

        $this->localFilesystem->touch($this->getTemporaryLocalFilepath());
    }

    protected function handleWakeUpFailure(FilesystemException $exception): void
    {
        $this->logger->error(
            'Failed to download temporary feed file from storage, restarting feed generation from the beginning.',
            [
                'feedName' => $this->feed->getInfo()->getName(),
                'domainId' => $this->domainConfig->getId(),
                'temporaryFilepath' => $this->getTemporaryFilepath(),
                'exception' => $exception,
            ],
        );

        $this->tryDeleteRemoteTemporaryFile();
        $this->tryDeleteLocalTemporaryFile();
        $this->lastSeekId = null;
    }

    protected function tryDeleteRemoteTemporaryFile(): void
    {
        try {
            $this->filesystem->delete($this->getTemporaryFilepath());
        } catch (FilesystemException $deleteException) {
            $this->logger->warning(
                'Failed to delete stuck temporary feed file from remote storage. Manual cleanup may be required.',
                [
                    'temporaryFilepath' => $this->getTemporaryFilepath(),
                    'exception' => $deleteException,
                ],
            );
        }
    }

    protected function tryDeleteLocalTemporaryFile(): void
    {
        if ($this->localFilesystem->exists($this->getTemporaryLocalFilepath())) {
            $this->localFilesystem->remove($this->getTemporaryLocalFilepath());
        }
    }

    public function sleep(): void
    {
        $this->mountManager->move(
            'local://' . $this->transformStringHelper->removeDriveLetterFromPath($this->getTemporaryLocalFilepath()),
            'main://' . $this->getTemporaryFilepath(),
        );
    }

    public function generateBatch(): void
    {
        if ($this->finished) {
            return;
        }

        // the currency scope must be set in every batch as the services (including the provider) are reset between batches
        $this->currentCurrencyProvider->setCurrentCurrencyCode($this->currencyCode);

        try {
            $itemsInBatch = $this->feed->getItems($this->domainConfig, $this->lastSeekId, static::BATCH_SIZE);

            if ($this->lastSeekId === null) {
                $this->clearTemporaryFile();
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
        } finally {
            $this->currentCurrencyProvider->setCurrentCurrencyCode(null);
            $this->servicesResetter->reset();
            $this->em->clear();
            gc_collect_cycles();
        }
    }

    public function getFeedInfo(): FeedInfoInterface
    {
        return $this->feed->getInfo();
    }

    public function getDomainConfig(): DomainConfig
    {
        return $this->domainConfig;
    }

    public function getLastSeekId(): ?int
    {
        return $this->lastSeekId;
    }

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
            'local://' . $this->transformStringHelper->removeDriveLetterFromPath($this->getTemporaryLocalFilepath()),
            'main://' . $this->feedFilepath,
        );

        $this->finished = true;
    }

    protected function writeToFeed(string $content): void
    {
        $this->localFilesystem->appendToFile($this->getTemporaryLocalFilepath(), $content);
    }

    protected function getTemporaryFilepath(): string
    {
        return $this->feedFilepath . static::TEMPORARY_FILENAME_SUFFIX;
    }

    protected function getTemporaryLocalFilepath(): string
    {
        return $this->feedLocalFilepath . '_local' . static::TEMPORARY_FILENAME_SUFFIX;
    }

    protected function clearTemporaryFile(): void
    {
        if ($this->localFilesystem->exists($this->getTemporaryLocalFilepath())) {
            $this->localFilesystem->remove($this->getTemporaryLocalFilepath());
            $this->localFilesystem->touch($this->getTemporaryLocalFilepath());
        }
    }
}
