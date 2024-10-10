<?php

declare(strict_types=1);

use Shopsys\Releaser\ReleaseWorker\CheckUncommittedChangesReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\Release\CheckChangelogForTodaysDateReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\Release\CheckoutToReleaseCandidateBranchReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\Release\CheckReleaseDraftAndReleaseItReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\Release\CreateAndCommitLockFilesReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\Release\CreateAndPushGitTagReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\Release\CreateAndPushGitTagsExceptProjectBaseReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\Release\MergeReleaseCandidateBranchReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\Release\SetMutualDependenciesToVersionReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\Release\TagPhpImageReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\CheckCorrectReleaseVersionReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\VerifyInitialBranchReleaseWorker;

return [
    CheckCorrectReleaseVersionReleaseWorker::class,
    VerifyInitialBranchReleaseWorker::class,
    CheckUncommittedChangesReleaseWorker::class,
    CheckoutToReleaseCandidateBranchReleaseWorker::class,
    CheckChangelogForTodaysDateReleaseWorker::class,
    SetMutualDependenciesToVersionReleaseWorker::class,
    MergeReleaseCandidateBranchReleaseWorker::class,
    CreateAndPushGitTagsExceptProjectBaseReleaseWorker::class,
    CreateAndCommitLockFilesReleaseWorker::class,
    TagPhpImageReleaseWorker::class,
    CreateAndPushGitTagReleaseWorker::class,
    CheckReleaseDraftAndReleaseItReleaseWorker::class,
];
