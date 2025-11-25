<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Shopsys\Releaser\FilesProvider\ComposerJsonFilesProvider;
use Shopsys\Releaser\ReleaseWorker\ReleaseWorkerProvider;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

/**
 * @param \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
 */
return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('Shopsys\\Releaser\\', __DIR__ . '/../src')
        ->exclude([__DIR__ . '/../src/Exception', __DIR__ . '/../src/ReleaseWorker']);

    $services->load('Shopsys\\Releaser\\ReleaseWorker\\', __DIR__ . '/../src/ReleaseWorker')
        ->tag('monorepo.release_worker');

    $services->set(ReleaseWorkerProvider::class)
        ->args([tagged_iterator('monorepo.release_worker')]);

    $services->set(ComposerJsonFilesProvider::class)
        ->args([['packages', 'project-base/app']]);

    $services->set(ClientInterface::class, Client::class);
};
