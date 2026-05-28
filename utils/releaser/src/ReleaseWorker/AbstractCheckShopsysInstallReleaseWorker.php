<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker;

use Override;
use PharIo\Version\Version;

abstract class AbstractCheckShopsysInstallReleaseWorker extends AbstractShopsysReleaseWorker
{
    private const string INSTALL_DIR_BASE = '/tmp/shopsys-project-base-install-test';

    #[Override]
    public function work(
        Version $version,
        string $initialBranchName = AbstractShopsysReleaseWorker::MAIN_BRANCH_NAME,
    ): void {
        $checkoutTarget = $this->getCheckoutTarget($version);

        $this->writeInstallationAndResumeCommand($checkoutTarget, $version, $initialBranchName);

        $this->confirm(
            'Run the command above on your host. It will install Shopsys, run the tests, clean up, and automatically resume the release on the next step.',
        );
    }

    abstract protected function getCheckoutTarget(Version $version): string;

    private function writeInstallationAndResumeCommand(
        string $checkoutTarget,
        Version $version,
        string $initialBranchName,
    ): void {
        $installDirectory = self::INSTALL_DIR_BASE . '-' . $checkoutTarget;
        $escapedInstallDirectory = escapeshellarg($installDirectory);
        $escapedCheckoutTarget = escapeshellarg($checkoutTarget);
        $escapedVersion = escapeshellarg($version->getVersionString());
        $escapedStage = escapeshellarg($this->getAllowedStages()[0]);
        $escapedInitialBranchName = escapeshellarg($initialBranchName);
        $nextStep = $this->currentStep + 1;

        $resumeCommand = sprintf(
            'docker compose exec -T php-fpm php bin/console monorepo:release %s --stage %s --initial-branch %s --github-token "$(gh auth token)" -v --resume-step %d',
            $escapedVersion,
            $escapedStage,
            $escapedInitialBranchName,
            $nextStep,
        );

        $command = sprintf(
            'if [ "$(uname -s)" = "Darwin" ]; then \
        echo -e "\033[33m==> macOS detected: terminating all mutagen sync sessions to prevent stalled sessions from re-binding to new sidecar containers\033[0m"; \
        mutagen sync terminate --all 2>/dev/null || true; \
    fi \
    && echo -e "\033[33m==> Preparing project-base checkout\033[0m" \
    && rm -rf %1$s \
    && git clone https://github.com/shopsys/project-base.git %1$s \
    && ( cd %1$s \
        && git checkout %2$s \
        && echo -e "\033[33m==> Removing all Docker containers\033[0m" \
        && (docker rm --force $(docker ps -a -q) || true) \
        && echo -e "\033[33m==> Removing all Docker images\033[0m" \
        && (docker rmi --force $(docker images -q) || true) \
        && echo -e "\033[33m==> Installing the application (see https://docs.shopsys.com/en/latest/installation/installation-guide/)\033[0m" \
        && ./scripts/install.sh \
        && echo -e "\033[33m==> Running the unit and functional test suites\033[0m" \
        && docker compose exec -T php-fpm php phing tests \
        && echo -e "\033[33m==> Restarting Selenium before acceptance tests so its internal node is fresh\033[0m" \
        && docker compose restart selenium-server \
        && echo -e "\033[33m==> Running the acceptance tests\033[0m" \
        && docker compose exec -T php-fpm php phing tests-acceptance \
        && echo -e "\033[33m==> Running the cypress tests\033[0m" \
        && make run-acceptance-tests-regression \
        && if [ "$(uname -s)" = "Darwin" ]; then \
            echo -e "\033[33m==> macOS detected: terminating all mutagen sync sessions before removing containers and images\033[0m"; \
            mutagen sync terminate --all 2>/dev/null || true; \
        fi \
        && echo -e "\033[33m==> Removing all Docker containers\033[0m" \
        && (docker rm --force $(docker ps -a -q) || true) \
        && echo -e "\033[33m==> Removing all Docker images\033[0m" \
        && (docker rmi --force $(docker images -q) || true) ) \
    && echo -e "\033[33m==> Cleaning up the install directory\033[0m" \
    && rm -rf %1$s \
    && echo -e "\033[33m==> Reinstalling the monorepo\033[0m" \
    && ./project-base/scripts/install.sh \
    && echo -e "\033[33m==> Resuming the release\033[0m" \
    && %3$s',
            $escapedInstallDirectory,
            $escapedCheckoutTarget,
            $resumeCommand,
        );

        $this->symfonyStyle->writeln('Run the following on the host. It will install Shopsys project-base, run the tests, clean up, reinstall the monorepo, and resume the release:');
        $this->symfonyStyle->newLine();
        $this->symfonyStyle->writeln($command);
        $this->symfonyStyle->newLine();
    }
}
