# [Upgrade from v9.0.1 to v9.1.0-dev](https://github.com/shopsys/shopsys/compare/v9.0.1...master)

This guide contains instructions to upgrade from version v9.0.1 to v9.1.0-dev.

**Before you start, don't forget to take a look at [general instructions](https://github.com/shopsys/shopsys/blob/master/UPGRADE.md) about upgrading.**
There you can find links to upgrade notes for other versions too.

## Application
- add `psr/event-dispatcher` to your composer dependencies in order to prevent PHPStan errors in Event classes ([#1894](https://github.com/shopsys/shopsys/pull/1894))
    - add `"psr/event-dispatcher": "^1.0.0",` to `require-dev` section in your `composer.json` file

- add support for changing personal data and password to your Frontend API ([#1891](https://github.com/shopsys/shopsys/pull/1891))
    - see #project-base-diff to update your project

- fix not working upload of files in wysiwyg editor ([#1899](https://github.com/shopsys/shopsys/pull/1899))
    - see #project-base-diff to update your project

- make Frontend API tests more reliable ([#1913](https://github.com/shopsys/shopsys/pull/1913))
    - see #project-base-diff to update your project

- update tests to use tests container to decrease amount of services defined in `services_test.yaml` ([#1957](https://github.com/shopsys/shopsys/pull/1957))
    - see #project-base-diff to update your project

- apply fixers for compliance with [PSR-12](https://www.php-fig.org/psr/psr-12/) ([#1324](https://github.com/shopsys/shopsys/pull/1324))
    - see #project-base-diff to update your project

- add new coding standards for YAML files ([#1552](https://github.com/shopsys/shopsys/pull/1552))
    - see #project-base-diff and update your `yaml-standards.yaml` file
    - run `php phing standards-fix` to fix your `yaml` files

- add phpstan-symfony extension ([#1961](https://github.com/shopsys/shopsys/pull/1961)) and ([#1974](https://github.com/shopsys/shopsys/pull/1974))
    - see #project-base-diff1 and #project-base-diff2 to update your project

- stop using Doctrine default value option ([#1395](https://github.com/shopsys/shopsys/pull/1395))
    - following properties no longer have a database default value, check if you set a default value in the constructor
        - `Shopsys\FrameworkBundle\Component\Cron\CronModule::$suspended`
        - `Shopsys\FrameworkBundle\Component\Cron\CronModule::$enabled`
        - `Shopsys\FrameworkBundle\Model\Newsletter\NewsletterSubscriber::$createdAt`
        - `Shopsys\FrameworkBundle\Model\Product\Product::$recalculateAvailability`
        - `Shopsys\FrameworkBundle\Model\Product\Product::$recalculatePrice`
        - `Shopsys\FrameworkBundle\Model\Product\Product::$recalculateVisibility`

- introduced sniff for strict comparasion ([#1658](https://github.com/shopsys/shopsys/pull/1658))
    - this change is temporary to help you prepare your project for next major version, where this will be required directly in coding-standards package
    - see #project-base-diff to update your project

- moved setting of common entity data to new method ([#1976](https://github.com/shopsys/shopsys/pull/1976))
    - see #project-base-diff to update your project

- required new line at the end of each file ([#1989](https://github.com/shopsys/shopsys/pull/1989))
    - see #project-base-diff
    - to fix errors you may run `php phing standards-fix`

- phpstan analyse increased to level 5 ([#1922](https://github.com/shopsys/shopsys/pull/1922))
    - increasing phpstan level on your project is optional and may be set in `build.xml`
    - see #project-base-diff to update your project

- move npm-global directory to project in order to make it included in Docker volumes ([#2024](https://github.com/shopsys/shopsys/pull/2024))
    - see #project-base-diff to update your project

- update `docker/php-fpm/docker-php-entrypoint` to show all lines from first command output ([#1827](https://github.com/shopsys/shopsys/pull/1827))
    ```diff
        mkfifo $PIPE
        chmod 666 $PIPE
    -   tail -f $PIPE &
    +   tail -n +1 -f $PIPE &
    ```
