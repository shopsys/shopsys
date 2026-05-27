<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\ReleaseCandidate;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use Nette\Utils\Json;
use Override;
use PharIo\Version\Version;
use Shopsys\Releaser\GithubActions\GithubActionsStatusReporter;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\Stage;
use Shopsys\Releaser\Wait\GithubActionsWorkflowSucceeded;

final class ForceYourBranchSplitReleaseWorker extends AbstractShopsysReleaseWorker
{
    private const string WORKFLOW_FILE = 'monorepo-force-split-branch.yaml';

    public function __construct(
        private readonly ClientInterface $httpClient,
        private readonly GithubActionsStatusReporter $githubActionsStatusReporter,
    ) {
    }

    #[Override]
    public function getDescription(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): string {
        return 'Force-split the branch via GitHub Actions';
    }

    #[Override]
    public function work(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): void {
        $githubToken = $this->resolveGithubToken();

        putenv('GITHUB_TOKEN=' . $githubToken);

        $this->processRunner->run(sprintf(
            'git -c url.https://x-access-token:$GITHUB_TOKEN@github.com/.insteadOf=https://github.com/ push --set-upstream origin %s',
            escapeshellarg($this->currentBranchName),
        ));

        $this->dispatchWorkflow($githubToken);

        $this->waitFor(new GithubActionsWorkflowSucceeded(
            $this->githubActionsStatusReporter,
            AbstractShopsysReleaseWorker::ORGANIZATION,
            AbstractShopsysReleaseWorker::MONOREPO_REPOSITORY,
            $this->currentBranchName,
            self::WORKFLOW_FILE,
            $githubToken,
        ));

        $this->success();
    }

    /**
     * @return string[]
     */
    #[Override]
    protected function getAllowedStages(): array
    {
        return [Stage::RELEASE_CANDIDATE];
    }

    private function dispatchWorkflow(string $githubToken): void
    {
        $this->symfonyStyle->note(sprintf(
            'Dispatching %s on branch %s via GitHub REST API',
            self::WORKFLOW_FILE,
            $this->currentBranchName,
        ));

        $url = sprintf(
            'https://api.github.com/repos/%s/%s/actions/workflows/%s/dispatches',
            AbstractShopsysReleaseWorker::ORGANIZATION,
            AbstractShopsysReleaseWorker::MONOREPO_REPOSITORY,
            self::WORKFLOW_FILE,
        );

        $request = new Request(
            'POST',
            $url,
            [
                'Authorization' => sprintf('token %s', $githubToken),
                'Accept' => 'application/vnd.github+json',
                'Content-Type' => 'application/json',
            ],
            Json::encode([
                'ref' => $this->currentBranchName,
                'inputs' => ['branch_name' => $this->currentBranchName],
            ]),
        );

        $this->httpClient->send($request);
    }
}
