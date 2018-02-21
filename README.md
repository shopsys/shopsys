# Monorepo

This document provides basic information about development in monorepo to make the work with packages and project-base repository as easy as possible.

## Problem
Due to the growing number of new repositories there will be more situations where the developer is trying to reflect the same change into several separated packages. In the current situation, a developer had to implement
this change individually in the separated repository of each package.
This approach would be inefficient and at the same time, the repeated process always
brings increased errors rate.

Monorepo approach provides some environment for management of packages from one specific
repository - monorepo repository. It contains every package that is part of Shopsys Framework.
We use [Monorepo tool](./packages/monorepo-tools) that splits code in appropriate repositories
after some changes are made in monorepo repository. This splitting is currently initiated manually,
but it will be automatized in the future.

## Repositories maintained by monorepo

* shopsys/project-base
* shopsys/product-feed-zbozi
* shopsys/product-feed-google
* shopsys/product-feed-heureka
* shopsys/product-feed-heureka-delivery
* shopsys/product-feed-interface
* shopsys/plugin-interface
* shopsys/coding-standards
* shopsys/http-smoke-testing
* shopsys/form-types-bundle
* shopsys/migrations
* shopsys/monorepo-tools

## Infrastructure
Monorepo can be installed and used as standard application. This requires some additional infrastructure:

* **docker/** - templates for cofiguration of docker in monorepo.

* **build.xml** - definitions of targets for use in the monorepo, some already defined targets
have modified behaviour in such a way that their actions are launched over all monorepo packages

* **composer.json** - contains the dependencies required by individual packages and by Shopsys Framework.
It is not generated automatically, so each change made in the `composer.json` of the specific package must be reflected
also in `composer.json` in the root of monorepo. In monorepo, shopsys packages are used directly from the directory
`packages/`, and there is no installation of these packages in `composer.json`. The exception is the coding-standards
package that continues to be installed in the vendor because the current master version of the package in
`packages/` is not supported by Shopsys Framework.

* **parameters_monorepo.yml** - overriding of global variables of Shopsys Framework, which makes it possible to run 
Shopsys Framework from the parent directory

## Development in monorepo
During the development in monorepo, it is necessary to ensure that the changes made in specific package
preserve the functionality of the package even outside the monorepo.
 
Keep in mind that the file structure of Shopsys Framework (standardly located in the root of the project) is in monorepo
located in the directory `project-base/`

Installation of Shopsys Framework is described in [Shopsys Framework installation guide](./project-base/docs/introduction/installation-guide.md)

## Troubleshooting
* Package is functional in monorepo but broken outside of monorepo - ensure that every parameter required by package
is available even outside the monorepo

* Command `cp app/config/domains_urls.yml.dist app/config/domains_urls.yml` results in failure - during the development
in monorepo, Shopsys Framework is placed in the directory `project-base/`. The correct form of this command during the
development in monorepo is `cp project-base/app/config/domains_urls.yml.dist project-base/app/config/domains_urls.yml`
