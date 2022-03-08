# [Upgrade from v9.1.0 to v9.2.0-dev](https://github.com/shopsys/shopsys/compare/v9.1.0...master)

This guide contains instructions to upgrade from version v9.1.0 to v9.2.0-dev.

**Before you start, don't forget to take a look at [general instructions](https://github.com/shopsys/shopsys/blob/7.3/UPGRADE.md) about upgrading.**
There you can find links to upgrade notes for other versions too.

## Composer dependencies
- remove `vasek-purchart/console-errors-bundle` dependency ([#2408](https://github.com/shopsys/shopsys/pull/2408))
    - see #project-base-diff to update your project

## Application
- use different css classes for javascript and tests ([#2179](https://github.com/shopsys/shopsys/pull/2179))
    - see #project-base-diff to update your project

- class `Shopsys\FrameworkBundle\Component\Csv\CsvReader` is deprecated, use `SplFileObject::fgetcsv()` instead ([#2218](https://github.com/shopsys/shopsys/pull/2218))

- replace html icon tags with new twig tag icon ([#2274](https://github.com/shopsys/shopsys/pull/2274))
    - search for all occurrences of
        ```twig
        <i class="svg svg-<svg-icon-name>"></i>
        ```
        and replace it by
        ```twig
        {{ icon('<svg-icon-name>') }}
        ```
        Full example:
        ```diff
        -   <i class="svg svg-question"></i>
        +   {{ icon('question') }}
        ```
    - for more information read our article [Icon function](https://docs.shopsys.com/en/9.1/frontend/icon-function/)

**\[BC break\]** change entity extension subscriber class ([#2405](https://github.com/shopsys/shopsys/pull/2405))
    - see #project-base-diff to update your project 
    - package joschi127/doctrine-entity-override-bundle is no longer used
    - previously used subscriber `\Joschi127\DoctrineEntityOverrideBundle\EventListener\LoadORMMetadataSubscriber` was replaced with `\Shopsys\FrameworkBundle\Component\EntityExtension\EntityExtensionSubscriber`
        - if you have extended `LoadORMMetadataSubscriber`, you will need to extend `EntityExtensionSubscriber` instead and reimplement your changes on top of the new class

- replace deprecated namespace `Doctrine\Common\Persistence\ObjectManager` with new `Doctrine\Persistence\ObjectManager` ([#2407](https://github.com/shopsys/shopsys/pull/2407))
    - see #project-base-diff to update your project

- replace dependency `fzaninotto/Faker` with `FakerPHP/Faker` ([#2413](https://github.com/shopsys/shopsys/pull/2413))
    - see #project-base-diff to update your project
