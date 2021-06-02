# [Upgrade from v9.1.1 to v9.1.2-dev](https://github.com/shopsys/shopsys/compare/v9.1.1...9.1)

This guide contains instructions to upgrade from version v9.1.1 to v9.1.2-dev.

**Before you start, don't forget to take a look at [general instructions](https://github.com/shopsys/shopsys/blob/7.3/UPGRADE.md) about upgrading.**
There you can find links to upgrade notes for other versions too.

- potential **BC break** ([#2300](https://github.com/shopsys/shopsys/pull/2300))
    - method `Shopsys\FrameworkBundle\Form\Admin\Customer\User\CustomerUserFormType::validateUniqueEmail()` has changed its interface
        - first argument `$email` was changed to accept `null` or `string` instead of `string` only

- replace class names defined by FQCN string by `*::class` constant ([#2319](https://github.com/shopsys/shopsys/pull/2300))
    - see #project-base-diff to update your project

- update composer dependency `composer/composer` in your project ([#2313](https://github.com/shopsys/shopsys/pull/2313))
    - versions bellow `1.10.22` has [reported security issue](https://github.com/composer/composer/security/advisories/GHSA-h5h8-pc6h-jvvx)
    - see #project-base-diff to update your project

- drop usage of Doctrine\Common\Cache\PhpFileCache as framework annotation cache ([#2326](https://github.com/shopsys/shopsys/pull/2326))
    - see #project-base-diff to update your project

- update your composer dependencies `symfony/security*`
    - run `composer update symfony/security-guard symfony/security-core`
        - version of both packages must be `4.4.23` or higher
    - using lower version is potential security risk
        - see more information https://github.com/symfony/symfony/security/advisories/GHSA-5pv8-ppvj-4h68

- remove unused multipleProductsInOrder.graphql file ([#2306](https://github.com/shopsys/shopsys/pull/2306))
    - see #project-base-diff to update your project
