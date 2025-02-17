<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

/**
 * The configuration is passed to Symfony maker via MAKER_PHP_CS_FIXER_CONFIG_PATH environment variable.
 *
 * @see https://symfony.com/bundles/SymfonyMakerBundle/current/index.html#linting-generated-code
 *
 * We are not able to disable the default linting itself, but we provide an empty configuration and exclude all php files here to ensure no changes are made to the generated classes.
 * Our maker classes take care of linting the generated code themselves using our easy-coding-standard configuration.
 *
 * @see \Symfony\Bundle\MakerBundle\Command\MakerCommand::execute()
 * @see \Shopsys\FrameworkBundle\Maker\BaseMaker::fixStandards()
 */
return (new Config())
    ->setRules([])
    ->setFinder(
        Finder::create()->notName('*.php'), // Exclude all PHP files
    );
