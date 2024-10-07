<?php

declare(strict_types=1);

use Shopsys\Releaser\ReleaseWorker\CheckCorrectReleaseVersionReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\CheckUncommittedChangesReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\ReleaseCandidate\CheckCopyrightYearReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\ReleaseCandidate\CheckLatestVersionOfReleaserReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\ReleaseCandidate\CheckPackagesGithubActionsBuildsAfterSplitReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\ReleaseCandidate\CheckPackagesGithubActionsBuildsReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\ReleaseCandidate\CheckReleaseBlogPostReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\ReleaseCandidate\CheckShopsysInstallReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\ReleaseCandidate\CreateBranchReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\ReleaseCandidate\DumpTranslationsReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\ReleaseCandidate\ForceYourBranchSplitReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\ReleaseCandidate\GenerateApiaryBlueprintReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\ReleaseCandidate\ReleaseNewNodeModulePackageVersion;
use Shopsys\Releaser\ReleaseWorker\ReleaseCandidate\ResolveDocsTodoReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\ReleaseCandidate\SendBranchForReviewAndTestsReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\ReleaseCandidate\SetFrameworkBundleVersionReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\ReleaseCandidate\SetMutualDependenciesToVersionReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\ReleaseCandidate\StopMergingReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\ReleaseCandidate\TestYourBranchLocallyReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\ReleaseCandidate\UpdateChangelogReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\ReleaseCandidate\UpdateLicenseAcknowledgementsReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\ReleaseCandidate\UpdateUpgradeReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\ReleaseCandidate\ValidateConflictsInComposerJsonReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\ReleaseCandidate\ValidateRequireFormatInComposerJsonReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\ReleaseCandidate\VerifyMinorUpgradeReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\VerifyInitialBranchReleaseWorker;

return [
    CheckCorrectReleaseVersionReleaseWorker::class,
    CheckLatestVersionOfReleaserReleaseWorker::class,
    VerifyInitialBranchReleaseWorker::class,
    CheckUncommittedChangesReleaseWorker::class,
    CheckPackagesGithubActionsBuildsReleaseWorker::class,
    CreateBranchReleaseWorker::class,
    CheckReleaseBlogPostReleaseWorker::class,
    StopMergingReleaseWorker::class,
    ValidateRequireFormatInComposerJsonReleaseWorker::class,
    ValidateConflictsInComposerJsonReleaseWorker::class,
    GenerateApiaryBlueprintReleaseWorker::class,
    DumpTranslationsReleaseWorker::class,
    SetFrameworkBundleVersionReleaseWorker::class,
    ResolveDocsTodoReleaseWorker::class,
    UpdateChangelogReleaseWorker::class,
    UpdateUpgradeReleaseWorker::class,
    UpdateLicenseAcknowledgementsReleaseWorker::class,
    CheckCopyrightYearReleaseWorker::class,
    ReleaseNewNodeModulePackageVersion::class,
    SetMutualDependenciesToVersionReleaseWorker::class,
    TestYourBranchLocallyReleaseWorker::class,
    ForceYourBranchSplitReleaseWorker::class,
    CheckShopsysInstallReleaseWorker::class,
    CheckPackagesGithubActionsBuildsAfterSplitReleaseWorker::class,
    VerifyMinorUpgradeReleaseWorker::class,
    SendBranchForReviewAndTestsReleaseWorker::class,
];
