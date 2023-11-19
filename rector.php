<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        //__DIR__ . '/project-base/app/src',
        __DIR__ . '/packages/framework/src',
        __DIR__ . '/packages/framework/tests',
        __DIR__ . '/project-base/app/src',
        __DIR__ . '/project-base/app/tests',//*/
        __DIR__ . '/project-base/app/src/Model/Product/Search/FilterQuery.php',
    ]);

    $rectorConfig->rules([
        //\Shopsys\FrameworkBundle\Component\Rector\AddVoidReturnTypeWhereNoReturnRector::class,
        //\Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromReturnNewRector::class,
        //\Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictBoolReturnExprRector::class,
        //\Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNativeCallRector::class,
        //\Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNewArrayRector::class,
        //\Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedPropertyRector::class,
        \Rector\TypeDeclaration\Rector\ClassMethod\AddMethodCallBasedStrictParamTypeRector::class,
        \Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeBasedOnPHPUnitDataProviderRector::class,
        \Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector::class,
        \Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeFromPropertyTypeRector::class,
        \Rector\TypeDeclaration\Rector\ClassMethod\ParamTypeByMethodCallTypeRector::class,
        \Rector\TypeDeclaration\Rector\ClassMethod\ParamTypeByParentCallTypeRector::class,
        //\Shopsys\FrameworkBundle\Component\Rector\ConstructorParamTypeByPropertyType::class,
        //\Shopsys\FrameworkBundle\Component\Rector\ReturnTypeByAnnotation::class,
        //\Shopsys\FrameworkBundle\Component\Rector\GuessReturnTypeByImplementationRector::class,
        \Rector\Php80\Rector\ClassMethod\AddParamBasedOnParentClassMethodRector::class,
        //\Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationBasedOnParentClassMethodRector::class,
        //\Shopsys\FrameworkBundle\Component\Rector\ReturnTypeByParent::class, // same as AddReturnTypeDeclarationBasedOnParentClassMethodRector? :(
        //\Shopsys\FrameworkBundle\Component\Rector\AnnotationArrayToArrayOfMixedRector::class,
    ]);
};
