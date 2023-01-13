<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/packages',
        __DIR__ . '/project-base',
    ]);

    $rectorConfig->rules([
        /*\Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector::class,
        \Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromReturnNewRector::class,
        \Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictBoolReturnExprRector::class,
        \Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNativeCallRector::class,
        \Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNewArrayRector::class,
        \Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedPropertyRector::class,
        \Rector\TypeDeclaration\Rector\ClassMethod\AddArrayParamDocTypeRector::class, // comment line - $this->paramTagRemover->removeParamTagsIfUseless($phpDocInfo, $node);
        \Rector\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector::class, // comment line - $hasChanged = $this->returnTagRemover->removeReturnTagIfUseless($phpDocInfo, $node);
        \Rector\TypeDeclaration\Rector\ClassMethod\AddMethodCallBasedStrictParamTypeRector::class,

        \Shopsys\FrameworkBundle\Component\Rector\ParamTypeDeclarationByPhpDocRector::class,
        \Rector\TypeDeclaration\Rector\ClassMethod\ParamTypeByMethodCallTypeRector::class,

        /**
         * Apply following diff in Rector\PHPStanStaticTypeMapper\TypeMapper\UnionTypeMapper
         * - $phpParserNode = $unionedType instanceof NullType && $typeKind === TypeKind::PROPERTY ? new Name('null') : $this->phpStanStaticTypeMapper->mapToPhpParserNode($unionedType, $typeKind);
         * + $phpParserNode = $unionedType instanceof NullType && in_array($typeKind, [TypeKind::PROPERTY, TypeKind::RETURN, TypeKind::PARAM], true) ? new Name('null') : $this->phpStanStaticTypeMapper->mapToPhpParserNode($unionedType, $typeKind);
         */
        //\Shopsys\FrameworkBundle\Component\Rector\ReturnTypeDeclarationByPhpDocRector::class,
        //\Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationBasedOnParentClassMethodRector::class,

        \Shopsys\FrameworkBundle\Component\Rector\PhpDocParamTypeByTypeHintRector::class,
    ]);

    // register a single rule
    //$rectorConfig->rule(InlineConstructorDefaultToPropertyRector::class);

    // define sets of rules
    //    $rectorConfig->sets([
    //        LevelSetList::UP_TO_PHP_81
    //    ]);
};
