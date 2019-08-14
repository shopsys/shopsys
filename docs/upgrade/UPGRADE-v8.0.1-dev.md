# [Upgrade from v8.0.0 to v8.0.1-dev](https://github.com/shopsys/shopsys/compare/v8.0.0...8.0)

This guide contains instructions to upgrade from version v8.0.0 to v8.0.1-dev.


## [shopsys/framework]

### Tools
- if you're using YAML standards checker, update to patched version `^4.2.5` and rerun the fixers ([#539](https://github.com/shopsys/shopsys/pull/539))
    - run `composer require --dev sspooky13/yaml-standards:^4.2.5` to update the library
    - run `php phing yaml-standards-fix` to fix all your YAML files

**Before you start, don't forget to take a look at [general instructions](/UPGRADE.md) about upgrading.**
There you can find links to upgrade notes for other versions too.

[shopsys/framework]: https://github.com/shopsys/framework
