# [Upgrade from v10.0.4 to v10.0.5](https://github.com/shopsys/shopsys/compare/v10.0.4...v10.0.5)

This guide contains instructions to upgrade from version v10.0.4 to v10.0.5.

**Before you start, don't forget to take a look at [general instructions](https://github.com/shopsys/shopsys/blob/master/UPGRADE.md) about upgrading.**
There you can find links to upgrade notes for other versions too.

## Packages

- update to latest version heureka/overeno-zakazniky package ([#2535](https://github.com/shopsys/shopsys/pull/2535))
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/ec64245927844d1a1780c9bc149572c14d33c73c) to update your project
- update twig/twig to v2.15.4 in order to fix CVE-2022-39261 ([#2527](https://github.com/shopsys/shopsys/pull/2527))
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/b3e25b17c5ddcf64a50bd284a84152ddd3ab008a) to update your project
