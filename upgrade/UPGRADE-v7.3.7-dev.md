# [Upgrade from v7.3.6 to v7.3.7-dev](https://github.com/shopsys/shopsys/compare/v7.3.6...7.3)

This guide contains instructions to upgrade from version v7.3.6 to v7.3.7-dev.

**Before you start, don't forget to take a look at [general instructions](https://github.com/shopsys/shopsys/blob/7.3/UPGRADE.md) about upgrading.**
There you can find links to upgrade notes for other versions too.

- update composer dependency `composer/composer` in your project ([#2314](https://github.com/shopsys/shopsys/pull/2314))
    - versions bellow `1.10.22` has [reported security issue](https://github.com/composer/composer/security/advisories/GHSA-h5h8-pc6h-jvvx)
    - see #project-base-diff to update your project

- update your depedency on `symfony/symfony` ([#2315](https://github.com/shopsys/shopsys/pull/2315))
    - define the lowest version to `3.4.48` in your `composer.json`
        - see #project-base-diff
    - run `composer update symfony/symfony`
