<?php

declare(strict_types=1);

use Shopsys\Releaser\ReleaseWorker\AfterRelease\BeHappyReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\AfterRelease\CheckDocsReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\AfterRelease\CheckPackagesGithubActionsBuildsReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\AfterRelease\CheckPackagesOnPackagistReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\AfterRelease\CheckShopsysInstallReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\AfterRelease\EnableMergingReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\AfterRelease\EnsureReleaseHighlightsPostIsReleasedReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\AfterRelease\EnsureRoadmapIsUpdatedReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\AfterRelease\PostInfoToSlackReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\AfterRelease\RemoveLockFilesReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\AfterRelease\SetFrameworkBundleVersionToDevReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\AfterRelease\SetMutualDependenciesToDevelopmentVersionReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\AfterRelease\SetPhpImageVersionInDockerfileReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\CheckCorrectReleaseVersionReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\CheckUncommittedChangesReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\VerifyInitialBranchReleaseWorker;

return [
    CheckCorrectReleaseVersionReleaseWorker::class,
    VerifyInitialBranchReleaseWorker::class,
    CheckUncommittedChangesReleaseWorker::class,
    CheckPackagesOnPackagistReleaseWorker::class,
    CheckPackagesGithubActionsBuildsReleaseWorker::class,
    RemoveLockFilesReleaseWorker::class,
    SetFrameworkBundleVersionToDevReleaseWorker::class,
    SetMutualDependenciesToDevelopmentVersionReleaseWorker::class,
    SetPhpImageVersionInDockerfileReleaseWorker::class,
    EnableMergingReleaseWorker::class,
    CheckShopsysInstallReleaseWorker::class,
    EnsureReleaseHighlightsPostIsReleasedReleaseWorker::class,
    PostInfoToSlackReleaseWorker::class,
    CheckDocsReleaseWorker::class,
    EnsureRoadmapIsUpdatedReleaseWorker::class,
    BeHappyReleaseWorker::class,
];
