<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

/**
 * @param \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
 */
return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('Shopsys\\MonorepoTools\\', __DIR__ . '/../src')
        ->exclude([__DIR__ . '/../src/MutualDependency/MissingMutualDependencies.php']);
};
