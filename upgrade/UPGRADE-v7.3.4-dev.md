# [Upgrade from v7.3.3 to v7.3.4-dev](https://github.com/shopsys/shopsys/compare/v7.3.3...7.3)

This guide contains instructions to upgrade from version v7.3.3 to v7.3.4-dev.

**Before you start, don't forget to take a look at [general instructions](https://github.com/shopsys/shopsys/blob/7.3/UPGRADE.md) about upgrading.**
There you can find links to upgrade notes for other versions too.

## [shopsys/framework]

### Application

- pin version of `fp/jsformvalidator-bundle` to `~1.5.1` as next minor is not meant to be used with Symfony 3 ([#1790](https://github.com/shopsys/shopsys/pull/1790))
    ```diff
    # composer.json
    -   "fp/jsformvalidator-bundle": "^1.5.1",
    +   "fp/jsformvalidator-bundle": "~1.5.1",
    ```
