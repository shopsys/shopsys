---
name: create-enum
description: >
  Create a Shopsys-style enum (a class extending AbstractEnum with public typed
  constants, NOT a native PHP enum). Use when you need to define a fixed set of
  values that downstream projects (like project-base) might extend or restrict.
user_invocable: true
version: 1.0.0
---

# Create a Shopsys-style enum

Native PHP enums (`enum Foo: string { case BAR = 'bar'; }`) are implicitly `final` and cannot be extended. Downstream Shopsys projects (notably `project-base`) routinely extend framework classes to add domain-specific cases or hide unused ones, so the project uses a class-based enum pattern instead.

**Always use this pattern for new enumerations in the framework or any package likely to be extended downstream.** Use a native PHP enum only when:

- the enumeration is project-only and there is no plausible reason a downstream consumer would extend it, or
- you are explicitly mirroring an external system's closed set of values.

When in doubt, use the Shopsys pattern — it's the project default.

## The pattern

```php
<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\Enum;

use Shopsys\FrameworkBundle\Component\Enum\AbstractEnum;

class EntityLogActionEnum extends AbstractEnum
{
    public const string CREATE = 'create';
    public const string UPDATE = 'update';
    public const string DELETE = 'delete';
}
```

**Mandatory rules:**

- Class **extends `Shopsys\FrameworkBundle\Component\Enum\AbstractEnum`** (or an abstract subclass of it for shared behaviour).
- Class is **not `final`** — downstream projects must be able to extend it.
- Class name **ends with `Enum`** — required so the auto-discovery glob in `packages/framework/src/Resources/config/services.yaml` and `project-base/app/config/services.yaml` registers it as a service.
- Constants are **`public const string` (or `int`, `bool`)** — typed constants, snake-uppercase names, scalar literals on the right-hand side.
- Values are scalar literals (strings are the most common). Avoid expressions or computed values.
- One file per enum. File name matches class name.

## Where the enum file goes

- **Tied to a model** — colocate next to the model: `packages/framework/src/Model/Order/Item/OrderItemTypeEnum.php`.
- **General-purpose / framework component** — put it under a dedicated `Enum/` subfolder of the component: `packages/framework/src/Component/EntityLog/Enum/EntityLogActionEnum.php`.
- **Project-only enum that extends a framework one** — mirror the framework path under `project-base/app/src/...`.

## How `AbstractEnum` works

`Shopsys\FrameworkBundle\Component\Enum\AbstractEnum` provides two instance methods used by callers:

```php
$enum->getAllCases();              // returns all public-constant *values*
$enum->validateCase('some_value'); // throws InvalidEnumCaseException if not a valid case
```

Both methods reflect on the runtime class, so subclasses automatically include their own constants and inherit framework-defined ones. Nothing about `getAllCases()` is static — that's intentional, because the runtime class can differ between framework and project (see "Extending downstream").

`getUnusedConstants()` is a hook for subclasses that want to **remove** a constant inherited from a parent enum (return an array of values to exclude). Default implementation returns `[]`.

## Using the enum at call sites

Inject the enum as a constructor dependency (it's a regular service):

```php
public function __construct(
    protected readonly OrderItemTypeEnum $orderItemTypeEnum,
) {
}

public function someMethod(string $type): void
{
    $this->orderItemTypeEnum->validateCase($type);  // throws if invalid

    foreach ($this->orderItemTypeEnum->getAllCases() as $value) {
        // ...
    }
}
```

Compare values against the constants directly (string comparisons):

```php
if ($descriptor->run === PostDeployTaskRunEnum::ONE_TIME) {
    // ...
}

match ($run) {
    PostDeployTaskRunEnum::NEVER => /* ... */,
    PostDeployTaskRunEnum::ALWAYS => /* ... */,
    PostDeployTaskRunEnum::ONE_TIME => /* ... */,
};
```

Don't store enum *instances* anywhere — values are plain strings/ints. The enum service exists only to provide validation and listing.

## Extending downstream

A project (or another bundle) extends a framework enum by declaring a subclass and adding constants:

```php
<?php

namespace App\Model\Order\Status;

use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusTypeEnum as FrameworkOrderStatusTypeEnum;

class OrderStatusTypeEnum extends FrameworkOrderStatusTypeEnum
{
    public const string PARTIALLY_PAID = 'partially_paid';
}
```

Then add an alias in `project-base/app/config/services.yaml` so anything injecting the framework type gets the extended one:

```yaml
Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusTypeEnum:
    alias: App\Model\Order\Status\OrderStatusTypeEnum
```

To **remove** a constant inherited from the parent, override `getUnusedConstants()`:

```php
class OrderStatusTypeEnum extends FrameworkOrderStatusTypeEnum
{
    /**
     * @return string[]
     */
    #[Override]
    protected function getUnusedConstants(): array
    {
        return [
            FrameworkOrderStatusTypeEnum::TYPE_LEGACY,
        ];
    }
}
```

`getAllCases()` will then exclude those values without removing the constant from the parent class (so existing references to the constant don't break).

## Abstract base + concrete subclass

When several enums share behaviour but expose different value sets, define an abstract enum that adds the shared API and concrete subclasses that supply the constants:

```php
abstract class AbstractTransportTypeEnum extends AbstractEnum
{
    /**
     * @return array<string, string>
     */
    abstract public function getAllIndexedByTranslations(): array;
}

class TransportTypeEnum extends AbstractTransportTypeEnum
{
    public const string TYPE_COMMON = 'common';
    public const string TYPE_PACKETERY = 'packetery';

    #[Override]
    public function getAllIndexedByTranslations(): array
    {
        return [
            t('Standard') => self::TYPE_COMMON,
            t('Packetery') => self::TYPE_PACKETERY,
        ];
    }
}
```

Reference: `packages/framework/src/Model/Transport/AbstractTransportTypeEnum.php` and `packages/framework/src/Model/Transport/TransportTypeEnum.php`.

## Don'ts

- ❌ `enum FooEnum: string { case BAR = 'bar'; }` — native PHP enum is `final`, breaks extensibility.
- ❌ `final class FooEnum extends AbstractEnum` — same problem.
- ❌ `private const` or non-typed constants — they won't be visible to `ReflectionHelper::getAllPublicClassConstants()`.
- ❌ Constants with computed values (`public const FOO = self::BAR . '_x'`) — values must be literals so they can be embedded in YAML/SQL/config.
- ❌ A class name not ending in `Enum` — it won't be auto-registered as a service.
- ❌ Storing enum instances in entity properties — store the constant *value* (string/int) instead. Persistence layers shouldn't know about the enum class.

## Checklist before shipping

- [ ] File ends with `Enum.php` and the class name matches the file.
- [ ] Class extends `AbstractEnum` (directly or via an abstract intermediate).
- [ ] Constants are `public const string|int|bool` with literal scalar values.
- [ ] Class is not `final`.
- [ ] If the enum replaces an existing native PHP `enum`, all call sites have been updated to use the constants (`Foo::BAR`) and not enum-only methods (`->value`, `tryFrom()`, `cases()`).
- [ ] If extended downstream, the framework class is aliased to the subclass in the project's `services.yaml`.
- [ ] Standards-fix and PHPStan pass on the new file.
