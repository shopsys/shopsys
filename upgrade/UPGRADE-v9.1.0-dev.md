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
