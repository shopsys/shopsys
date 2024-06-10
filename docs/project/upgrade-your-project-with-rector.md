# Upgrade your project with Rector

Rector is a tool that can help you with upgrading to the new versions of Symfony, Doctrine, PHP.
It can automatically refactor your code to use new features, remove deprecated code, or fix bugs.
Rector can also help you with other tasks, such as code style fixes or code quality improvements.

## Installation

To install Rector, run:

```bash
composer require rector/rector --dev
```

## Configuration

Rector needs a configuration file to know what changes to apply.
After a first launch, Rector will generate a `rector.php` file in the root of your project.

You can also create the configuration file manually.

```php
<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/app',
    ])
    ->withRules([
        // add rules here
    ])
    ->withSets([
        // add sets here
    ]);
```

See the [Rector documentation](https://getrector.com/documentation) for more information.

## Usage

Rector has a predefined set of rules that can be used to refactor your code.

For example, to make your application compatible with Symfony 6, you can use the `SymfonySetList::SYMFONY_60` set.

```php
    \Rector\Symfony\Set\SymfonySetList::SYMFONY_60
```

When you have the config file ready, you can run Rector with the following command:

```bash
php vendor/bin/rector
```

## Tips

-   Always run Rector on a clean branch to avoid conflicts with other changes.
-   Run Rector with a small number of rules at a time to make the changes in the code understandable and reviewable.
-   Thoroughly review the changes made by Rector to ensure that they are correct.
-   Run `php phing standards-fix` to make changes made by Rector adhere your code style.
-   Run your tests after running Rector to ensure that the changes did not introduce any regressions.

## Conclusion

Rector is a powerful tool that can help you automate the tedious repetitive tasks.
It can save you a lot of time and effort.
