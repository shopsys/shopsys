<?php

declare(strict_types=1);

namespace Shopsys\Releaser\Wait;

use Override;
use Shopsys\Releaser\Guzzle\ApiCaller;
use Shopsys\Releaser\ReleaseWorker\WaitForExternalConditionInterface;

final class DockerHubTagAvailable implements WaitForExternalConditionInterface
{
    private const int POLL_INTERVAL_SECONDS = 300;

    public function __construct(
        private readonly ApiCaller $apiCaller,
        private readonly string $organization,
        private readonly string $repository,
        private readonly string $tag,
    ) {
    }

    #[Override]
    public function describe(): string
    {
        return sprintf('%s/%s:%s available on Docker Hub', $this->organization, $this->repository, $this->tag);
    }

    #[Override]
    public function check(): bool
    {
        $url = sprintf(
            'https://hub.docker.com/v2/repositories/%s/%s/tags/%s/',
            $this->organization,
            $this->repository,
            $this->tag,
        );

        return $this->apiCaller->urlReturnsOk($url);
    }

    #[Override]
    public function pollIntervalSeconds(): int
    {
        return self::POLL_INTERVAL_SECONDS;
    }

    #[Override]
    public function progressDescription(): string
    {
        return sprintf('%s/%s:%s not yet published', $this->organization, $this->repository, $this->tag);
    }
}
