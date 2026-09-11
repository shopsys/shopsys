# Static Service Access

Some framework code has no access to dependency injection.
The typical case is an [entity property hook](../model/entities.md#property-hooks): a hook must normalize the value on every write, but an entity cannot receive a service.
Such code has to call a static method.

A static method called by its framework class name is a barrier for projects.
Your project may extend the service and override the method, but framework code still calls `Shopsys\...\TransformStringHelper::createFriendlyUrlSlug()`, so your override is never reached, even when the service is aliased in `services.yaml`.
[`ExtendedClassNameResolver`]({{github.link}}/packages/framework/src/Component/ClassExtension/ExtendedClassNameResolver.php) removes this barrier: framework code resolves the class name first and calls the static method on the class the container really uses, which is your class whenever you extend the service.

## How it works

1. The [`RegisterExtendedClassNamesCompilerPass`]({{github.link}}/packages/framework/src/DependencyInjection/Compiler/RegisterExtendedClassNamesCompilerPass.php) maps every service registered under its class name to the class the container resolves it to, whenever that is a different class extending it.
   Nothing has to opt in, the map follows the same aliases and definitions that dependency injection follows.
   The [entity extension map](entity-extension.md) is included as well, so a static call on an extended entity resolves the same way.
2. `ShopsysFrameworkBundle::boot()` hands the map to `ExtendedClassNameResolver`.
3. Framework code calls the static method on `ExtendedClassNameResolver::resolve(TransformStringHelper::class)` instead of on the class name directly.

```php
// FrameworkBundle/Component/AbstractUploadedFile/AbstractUploadedFile.php

protected $slug {
    set {
        $this->slug = ExtendedClassNameResolver::resolve(TransformStringHelper::class)::createFriendlyUrlSlug($value);
    }
}
```

The resolution covers the whole class.
Every static method of the resolved class is dispatched to your project class, and `static::` calls inside those methods stay in your class as well.
The same applies to entities, `ExtendedClassNameResolver::resolve(Product::class)` returns your `App\Model\Product\Product`.

## Overriding the behavior in your project

Extend the service the same way as any other framework service and override the static method:

```php
// src/Component/String/TransformStringHelper.php

namespace App\Component\String;

use Shopsys\FrameworkBundle\Component\String\TransformStringHelper as BaseTransformStringHelper;

class TransformStringHelper extends BaseTransformStringHelper
{
    public static function createFriendlyUrlSlug(string $string): string
    {
        return str_replace('-', '_', parent::createFriendlyUrlSlug($string));
    }
}
```

```yaml
# config/services.yaml

Shopsys\FrameworkBundle\Component\String\TransformStringHelper:
    alias: App\Component\String\TransformStringHelper
```

Every uploaded file slug, whether written by the framework hook or by your own code, now uses underscores.

## Rules for framework code

The coding standards fixer `Shopsys/extended_class_name_resolver` from [shopsys/coding-standards]({{github.link}}/packages/coding-standards/README.md) rewrites static calls on services into the resolved form and adds the import, so `php phing standards-fix` keeps the codebase consistent.

- a static method of a service is never called by the framework class name from code that projects cannot reach through dependency injection, always through `ExtendedClassNameResolver::resolve()`
- use the pattern only where dependency injection is not available, everywhere else inject the service

## Unit tests

Before the kernel is booted, no map is registered and `ExtendedClassNameResolver::resolve()` returns the framework class itself, so unit tests need no setup.
Functional tests use the map of the booted kernel.
