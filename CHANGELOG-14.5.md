# Changelog for 14.5.0 (Technology Update Release)

All notable changes that change in some way the behavior of any of our packages are maintained by the monorepo repository.

There is a list of all the repositories maintained by the monorepo:

-   [shopsys/framework](https://github.com/shopsys/framework)
-   [shopsys/project-base](https://github.com/shopsys/project-base)
-   [shopsys/shopsys](https://github.com/shopsys/shopsys)
-   [shopsys/coding-standards](https://github.com/shopsys/coding-standards)
-   [shopsys/form-types-bundle](https://github.com/shopsys/form-types-bundle)
-   [shopsys/http-smoke-testing](https://github.com/shopsys/http-smoke-testing)
-   [shopsys/migrations](https://github.com/shopsys/migrations)
-   [shopsys/monorepo-tools](https://github.com/shopsys/monorepo-tools)
-   [shopsys/plugin-interface](https://github.com/shopsys/plugin-interface)
-   [shopsys/brand-feed-luigis-box](https://github.com/shopsys/category-feed-luigis-box)
-   [shopsys/category-feed-luigis-box](https://github.com/shopsys/category-feed-luigis-box)
-   [shopsys/product-feed-google](https://github.com/shopsys/product-feed-google)
-   [shopsys/product-feed-heureka](https://github.com/shopsys/product-feed-heureka)
-   [shopsys/product-feed-heureka-delivery](https://github.com/shopsys/product-feed-heureka-delivery)
-   [shopsys/product-feed-zbozi](https://github.com/shopsys/product-feed-zbozi)
-   [shopsys/product-feed-luigis-box](https://github.com/shopsys/product-feed-luigis-box)
-   [shopsys/article-feed-luigis-box](https://github.com/shopsys/article-feed-luigis-box)
-   [shopsys/google-cloud-bundle](https://github.com/shopsys/google-cloud-bundle)
-   [shopsys/s3-bridge](https://github.com/shopsys/s3-bridge)
-   [shopsys/frontend-api](https://github.com/shopsys/frontend-api)
-   [shopsys/php-image](https://github.com/shopsys/php-image)
-   [shopsys/luigis-box](https://github.com/shopsys/luigis-box)

Packages are formatted by release version.
You can see all the changes done to the package that you carry about with this tree.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/) and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html) as explained in the [Backward Compatibility Promise](https://docs.shopsys.com/en/latest/contributing/backward-compatibility-promise/).

<!-- Add generated changelog below this line -->

## [v14.5.1](https://github.com/shopsys/shopsys/compare/v14.5.0...v14.5.1) (2026-02-08)

## What's Changed

### :sparkles: Enhancements and features

-   Images are no longer processed by PHP to avoid quality decrease by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4360
-   Added styles for grapesjs on localhost by @chlebektomas in https://github.com/shopsys/shopsys/pull/4385

### :bug: Bug Fixes

-   Pinned jquery version to 3.x major by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4428
-   Fixed tests on single domain by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4429

### :hammer: Developer experience and refactoring

-   Replaced mutagen-compose with plain mutagen by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4343

### :placard: Upgraded Dependencies

-   Updated to Symfony 6 by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4394
-   Updated dependencies by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4357
-   Updated gopay/payments-sdk-php by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4366
-   Updated sspooky13/yaml-standards by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4361
-   Updated symplify/easy-coding-standard package to 12.2 by @machacjan in https://github.com/shopsys/shopsys/pull/4367

**Full Changelog**: https://github.com/shopsys/shopsys/compare/v14.5.0...v14.5.1

## [v14.5.0](https://github.com/shopsys/shopsys/compare/v14.0.1...v14.5.0) (2025-11-27)

<!-- Release notes generated using configuration in .github/release.yml at 14.5 -->

## What's Changed

### :hammer: Developer experience and refactoring

-   removed docker deprecations by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4277

### :cloud: Infrastructure

-   upgraded PostgreSQL to version 17.4 by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4278
-   upgraded Nginx to version 1.29 by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4276
-   upgraded Redis by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4280
-   upgraded RabbitMQ by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4279
-   upgraded Elasticsearch and Kibana by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4283
-   upgraded shopsys/deployment package by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4285

### :placard: Upgraded Dependencies

-   bump doctrine/persistence and twig/twig by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4275
-   Update BE Node version from 16 to 24 by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4282

**Full Changelog**: https://github.com/shopsys/shopsys/compare/v14.0.1...v14.5.0
