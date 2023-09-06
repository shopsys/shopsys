<?php

declare(strict_types=1);

use Shopsys\Releaser\ReleaseWorker\CheckCorrectReleaseVersionReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\Release\CheckChangelogForTodaysDateReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\Release\CheckoutToReleaseCandidateBranchReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\Release\CheckReleaseDraftAndReleaseItReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\Release\CheckUncommittedChangesReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\Release\CreateAndCommitLockFilesReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\Release\CreateAndPushGitTagReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\Release\CreateAndPushGitTagsExceptProjectBaseReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\Release\MergeReleaseCandidateBranchReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\Release\TagPhpImageReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\Release\VerifyInitialBranchReleaseWorker;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

/**
 * @param \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
 */
return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(CheckCorrectReleaseVersionReleaseWorker::class);
    $services->set(VerifyInitialBranchReleaseWorker::class);
    $services->set(CheckUncommittedChangesReleaseWorker::class);
    $services->set(CheckoutToReleaseCandidateBranchReleaseWorker::class);
    $services->set(CheckChangelogForTodaysDateReleaseWorker::class);
    $services->set(MergeReleaseCandidateBranchReleaseWorker::class);
    $services->set(CreateAndPushGitTagsExceptProjectBaseReleaseWorker::class);
    $services->set(CreateAndCommitLockFilesReleaseWorker::class);
    $services->set(TagPhpImageReleaseWorker::class);
    $services->set(CreateAndPushGitTagReleaseWorker::class);
    $services->set(CheckReleaseDraftAndReleaseItReleaseWorker::class);
};
