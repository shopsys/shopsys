<?php

declare(strict_types=1);

use Shopsys\Releaser\ReleaseWorker\CheckCorrectReleaseVersionReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\ReleaseCandidate\CheckLatestVersionOfReleaserReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\ReleaseCandidate\CheckPackagesGithubActionsBuildsReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\ReleaseCandidate\CheckProjectBaseBuild;
use Shopsys\Releaser\ReleaseWorker\ReleaseCandidate\CheckReleaseBlogPostReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\ReleaseCandidate\CheckShopsysInstallReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\ReleaseCandidate\CheckUncommittedChangesReleaseWorker;
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
use Shopsys\Releaser\ReleaseWorker\ReleaseCandidate\VerifyInitialBranchReleaseWorker;
use Shopsys\Releaser\ReleaseWorker\ReleaseCandidate\VerifyMinorUpgradeReleaseWorker;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

/**
 * @param \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
 */
return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(CheckCorrectReleaseVersionReleaseWorker::class);
    $services->set(CheckLatestVersionOfReleaserReleaseWorker::class);
    $services->set(VerifyInitialBranchReleaseWorker::class);
    $services->set(CheckUncommittedChangesReleaseWorker::class);
    $services->set(CheckPackagesGithubActionsBuildsReleaseWorker::class);
    $services->set(CreateBranchReleaseWorker::class);
    $services->set(CheckReleaseBlogPostReleaseWorker::class);
    $services->set(StopMergingReleaseWorker::class);
    $services->set(ValidateRequireFormatInComposerJsonReleaseWorker::class);
    $services->set(ValidateConflictsInComposerJsonReleaseWorker::class);
    $services->set(GenerateApiaryBlueprintReleaseWorker::class);
    $services->set(DumpTranslationsReleaseWorker::class);
    $services->set(SetFrameworkBundleVersionReleaseWorker::class);
    $services->set(ResolveDocsTodoReleaseWorker::class);
    $services->set(UpdateChangelogReleaseWorker::class);
    $services->set(UpdateUpgradeReleaseWorker::class);
    $services->set(UpdateLicenseAcknowledgementsReleaseWorker::class);
    $services->set(ReleaseNewNodeModulePackageVersion::class);
    $services->set(SetMutualDependenciesToVersionReleaseWorker::class);
    $services->set(TestYourBranchLocallyReleaseWorker::class);
    $services->set(ForceYourBranchSplitReleaseWorker::class);
    $services->set(CheckShopsysInstallReleaseWorker::class);
    $services->set(CheckProjectBaseBuild::class);
    $services->set(VerifyMinorUpgradeReleaseWorker::class);
    $services->set(SendBranchForReviewAndTestsReleaseWorker::class);
};
