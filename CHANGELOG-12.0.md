# Changelog for 12.0.x

All notable changes, that change in some way the behavior of any of our packages that are maintained by monorepo repository.

There is a list of all the repositories maintained by monorepo:

* [shopsys/framework](https://github.com/shopsys/framework)
* [shopsys/project-base](https://github.com/shopsys/project-base)
* [shopsys/shopsys](https://github.com/shopsys/shopsys)
* [shopsys/coding-standards](https://github.com/shopsys/coding-standards)
* [shopsys/form-types-bundle](https://github.com/shopsys/form-types-bundle)
* [shopsys/http-smoke-testing](https://github.com/shopsys/http-smoke-testing)
* [shopsys/migrations](https://github.com/shopsys/migrations)
* [shopsys/monorepo-tools](https://github.com/shopsys/monorepo-tools)
* [shopsys/plugin-interface](https://github.com/shopsys/plugin-interface)
* [shopsys/product-feed-google](https://github.com/shopsys/product-feed-google)
* [shopsys/product-feed-heureka](https://github.com/shopsys/product-feed-heureka)
* [shopsys/product-feed-heureka-delivery](https://github.com/shopsys/product-feed-heureka-delivery)
* [shopsys/product-feed-zbozi](https://github.com/shopsys/product-feed-zbozi)
* [shopsys/google-cloud-bundle](https://github.com/shopsys/google-cloud-bundle)
* [shopsys/s3-bridge](https://github.com/shopsys/s3-bridge)
* [shopsys/read-model](https://github.com/shopsys/read-model)
* [shopsys/frontend-api](https://github.com/shopsys/frontend-api)

Packages are formatted by release version.
You can see all the changes done to package that you carry about with this tree.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/) and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html) as explained in the [Backward Compatibility Promise](https://docs.shopsys.com/en/latest/contributing/backward-compatibility-promise/).

<!-- Add generated changelog below this line -->

## [v12.0.0](https://github.com/shopsys/shopsys/compare/v11.1.0...v12.0.0) (2023-09-22)

### :bug: Bug Fixes
* [framework] fixed variant creation by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2801
* [framework] fixed bestsellers edit in admin by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2799
* [framework] fixed order edit error due to invalid type in vat object by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2800
* [framework] fixed seoRobotsTxtContent null value in settings by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2731

### :hammer: Developer experience and refactoring
* [shopsys] removed deprecations before release 12.0 by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2719
* [framework] upgraded doctrine/orm to latest version by @TomasLudvik in https://github.com/shopsys/shopsys/pull/2774
* [shopsys] updated overblog/graphql-bundle to stable version 1.0.0 with dependencies by @TomasLudvik in https://github.com/shopsys/shopsys/pull/2788
* [shopsys] updated Dockerfile to update installation of NodeJS and Postgres in PHP-FPM Dockerfile by @TomasLudvik in https://github.com/shopsys/shopsys/pull/2792
* [framework] encapsulation of AdditionalImageData by @pk16011990 in https://github.com/shopsys/shopsys/pull/1934
* [shopsys] updated codeception to version 5 by @TomasLudvik https://github.com/shopsys/shopsys/pull/2611
* [coding-standards] updated slevomat coding standards by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2646
* [shopsys] inheritdoc docblock is now unified and fixed automatically by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2612

### :cloud: Infrastructure
* [shopsys] improvements for deployment/running in cluster by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2609
* [shopsys] CDN support by @grossmannmartin https://github.com/shopsys/shopsys/pull/2602
