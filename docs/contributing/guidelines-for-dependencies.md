# Guidelines for Dependencies

We care about `composer.json` unity across all repositories, and we try to avoid hidden dependencies.
So, we created a few rules we follow.

## External dependencies

We use asterisk `*` notation for PHP extensions.

We use caret `^` notation for external dependencies, and if there are more major versions possible, we use single pipe `|` notation without spaces.
For example `^6.2.0`, `^7.0`, `^5.0|^6.0|^7.0.4`.

If there is a problem, you can stabilize the dependency in a patch version, for example, `6.4.2`.
Please do it in a separate commit with an explanation in the commit message.

## Working with dependencies

After you change a dependency in a package or project-base, you must obviously reflect the change in the `composer.json` of a given package.

Packages' `composer.json` files are not used automatically during development in the monorepo.
Monorepo uses root `composer.json` to contain all packages' dependencies.
Monorepo dependencies are managed manually.
If you add or change any dependency in a package or project-base, reapply the change into monorepo `composer.json`.

## How to deal with Shopsys dependencies

If the package or project-base depends on a shopsys package, we declare `dev-master` dependency.
We will change `dev-master` dependency to a released tag like `v7.0.0-alpha1` during the release.

!!! note

    During composer installation there will be installed 3-rd party software as dependencies of Shopsys Platform with licenses that are described in the document [Open Source License Acknowledgements and Third-Party Copyrights](https://github.com/shopsys/shopsys/blob/master/open-source-license-acknowledgements-and-third-party-copyrights.md)
