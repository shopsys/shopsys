<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\FunctionNotation\PhpdocToPropertyTypeFixer;
use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use Shopsys\CodingStandards\CsFixer\ForbiddenPrivateVisibilityFixer;
use Shopsys\CodingStandards\Sniffs\ForceLateStaticBindingForProtectedConstantsSniff;
use Shopsys\CodingStandards\Sniffs\ObjectIsCreatedByFactorySniff;
use SlevomatCodingStandard\Sniffs\Functions\FunctionLengthSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\PropertyTypeHintSniff;
use Symplify\EasyCodingStandard\Config\ECSConfig;

/**
 * @param Symplify\EasyCodingStandard\Config\ECSConfig $ecsConfig
 */
return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->import(__DIR__ . '/vendor/shopsys/coding-standards/ecs.php', null, true);

    $ecsConfig->skip(
        [
            ObjectIsCreatedByFactorySniff::class => [
                __DIR__ . '/packages/administration/src/Component/FieldDescription/FieldDescriptionFactory.php',
            ],
            FunctionLengthSniff::class => [
                __DIR__ . '/packages/administration/src/Controller/CRUDController.php',
                __DIR__ . '/packages/administration/src/**/*Admin.php',
            ],
        ],
    );
};
