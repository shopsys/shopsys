<?php

declare(strict_types=1);

namespace Shopsys\Releaser\Wait;

use Override;
use Shopsys\Releaser\Packagist\PackageProvider;
use Shopsys\Releaser\ReleaseWorker\WaitForExternalConditionInterface;

final class AllPackagistVersionsAvailable implements WaitForExternalConditionInterface
{
    private const int POLL_INTERVAL_SECONDS = 60;

    private readonly int $initialMissingCount;

    /**
     * @var string[]
     */
    private array $missingPackages;

    /**
     * @param string[] $packages
     */
    public function __construct(
        private readonly PackageProvider $packageProvider,
        array $packages,
        private readonly string $version,
    ) {
        $this->missingPackages = array_values($packages);
        $this->initialMissingCount = count($this->missingPackages);
    }

    #[Override]
    public function describe(): string
    {
        return sprintf(
            'all %d packages indexed on Packagist with version %s',
            $this->initialMissingCount,
            $this->version,
        );
    }

    #[Override]
    public function check(): bool
    {
        $stillMissing = [];

        foreach ($this->missingPackages as $package) {
            if ($this->packageProvider->hasVersion($package, $this->version)) {
                continue;
            }

            $stillMissing[] = $package;
        }

        $this->missingPackages = $stillMissing;

        return $this->missingPackages === [];
    }

    #[Override]
    public function pollIntervalSeconds(): int
    {
        return self::POLL_INTERVAL_SECONDS;
    }

    #[Override]
    public function progressDescription(): string
    {
        if ($this->missingPackages === []) {
            return sprintf('all %d packages have %s on Packagist', $this->initialMissingCount, $this->version);
        }

        return sprintf(
            '%d/%d packages still missing %s: %s',
            count($this->missingPackages),
            $this->initialMissingCount,
            $this->version,
            implode(', ', $this->missingPackages),
        );
    }
}
