<?php

declare(strict_types=1);

namespace Tests\App\Smoke;

use League\Flysystem\FilesystemOperator;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Feed\FeedFacade;
use Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface;
use Tests\App\Test\FunctionalTestCase;

class AllFeedsTest extends FunctionalTestCase
{
    /**
     * @inject
     */
    private FeedFacade $feedFacade;

    /**
     * @inject
     */
    private FilesystemOperator $filesystem;

    public function getAllFeedExportCreationData(): array
    {
        // Method setUp is called only before each test, data providers are called even before that
        $this->setUp();

        $data = [];

        foreach ($this->feedFacade->getFeedsInfo() as $feedInfo) {
            foreach ($this->domain->getAll() as $domainConfig) {
                foreach ($this->feedFacade->getCurrencyCodesForFeed($feedInfo->getName(), $domainConfig) as $currencyCode) {
                    $key = sprintf('feed "%s" on domain "%s" in currency "%s"', $feedInfo->getName(), $domainConfig->getName(), $currencyCode);
                    $data[$key] = [$feedInfo, $domainConfig, $currencyCode];
                }
            }
        }

        return $data;
    }

    public function testFeedIsExportable(): void
    {
        foreach ($this->getAllFeedExportCreationData() as $dataProvider) {
            /** @var \Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface $feedInfo */
            $feedInfo = $dataProvider[0];
            /** @var \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig */
            $domainConfig = $dataProvider[1];
            /** @var string $currencyCode */
            $currencyCode = $dataProvider[2];

            $this->cleanUp($feedInfo, $domainConfig, $currencyCode);

            $this->feedFacade->generateFeed($feedInfo->getName(), $domainConfig, $currencyCode);

            $feedFilepath = $this->feedFacade->getFeedFilepath($feedInfo, $domainConfig, $currencyCode);
            $this->assertTrue($this->filesystem->has($feedFilepath), 'Exported feed file exists.');

            $this->cleanUp($feedInfo, $domainConfig, $currencyCode);
        }
    }

    private function cleanUp(FeedInfoInterface $feedInfo, DomainConfig $domainConfig, string $currencyCode): void
    {
        $feedFilepath = $this->feedFacade->getFeedFilepath($feedInfo, $domainConfig, $currencyCode);

        if ($this->filesystem->has($feedFilepath)) {
            $this->filesystem->delete($feedFilepath);
        }
    }
}
