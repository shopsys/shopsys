<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Shopsys\Releaser\Command\ReleaseCommand as ShopsysReleaseCommand;
use Shopsys\Releaser\FileManipulator\MonorepoUpgradeFileManipulator;
use Shopsys\Releaser\FilesProvider\ComposerJsonFilesProvider;
use Shopsys\Releaser\ReleaseWorker\ReleaseWorkerProvider as ShopsysReleaseWorkerProvider;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\MonorepoBuilder\Release\Command\ReleaseCommand;
use Symplify\MonorepoBuilder\Release\ReleaseWorkerProvider;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Loader\LoaderInterface;

/**
 * @param \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
 */
return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $services = $containerConfigurator->services();

    // name of the monorepo package, for UPGRADE.md and CHANGELOG.md links
    $parameters->set('monorepo_package_name', 'shopsys/shopsys');

    // these stages can use already existing tag; newer version than the last one is required in all other stages by default
    // this can be used to skip the tag validation completely
    // we need this as we are supporting multiple versions and we need to release tags that are lower than the newest one
    // e.g. we want to release "v7.3.2" even if "v8.0.0" is already released

    $parameters->set('stages_to_allow_existing_tag', ['release-candidate', 'release', 'after-release']);

    $containerConfigurator->import(__DIR__ . '/release-candidate.php');
    $containerConfigurator->import(__DIR__ . '/release.php');
    $containerConfigurator->import(__DIR__ . '/after-release.php');

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('Shopsys\\Releaser\\', __DIR__ . '/../src')
        ->exclude([__DIR__ . '/../src/Exception']);

    $services->set(MonorepoUpgradeFileManipulator::class)
        ->args(['shopsys/shopsys']);

    $services->set(ClientInterface::class, Client::class);

    $services->set(QuestionHelper::class);

    $services->set(ComposerJsonFilesProvider::class)
        ->args([['packages', 'project-base']]);

    $services->set(Environment::class);

    $services->set(LoaderInterface::class, FilesystemLoader::class)
        ->args([['upgrade/template']]);

    $services->set(ReleaseCommand::class, ShopsysReleaseCommand::class);

    $services->set(ReleaseWorkerProvider::class, ShopsysReleaseWorkerProvider::class);
};
