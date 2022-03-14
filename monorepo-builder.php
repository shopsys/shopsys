<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

/**
 * @param \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
 */
return static function (ContainerConfigurator $containerConfigurator): void {
    // release workers for "release" command, run:
    // e.g. "vendor/bin/monorepo-builder release vX.Y --dry-run -v"
    $containerConfigurator->import(__DIR__ . '/utils/releaser/config/services.php');

    $parameters = $containerConfigurator->parameters();

    $parameters->set('enable_default_release_workers', false);

    // require "--stage <name>" when release command is run
    $parameters->set('is_stage_required', true);
};
