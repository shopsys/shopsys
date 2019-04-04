# [Upgrade from v7.1.0 to Unreleased]

This guide contains instructions to upgrade from version v7.1.0 to Unreleased.

**Before you start, don't forget to take a look at [general instructions](/UPGRADE.md) about upgrading.**
There you can find links to upgrade notes for other versions too.

[Upgrade from v7.1.0 to Unreleased]: https://github.com/shopsys/shopsys/compare/v7.1.0...HEAD

## [shopsys/framework]
### Configuration
 - *(low priority)* Use standard format for redis prefixes (#928)
    - change prefixes in `app/config/packages/snc_redis.yml` and `app/config/packages/test/snc_redis.yml`. Please find inspiration in https://github.com/shopsys/shopsys/pull/928/files

    **Be careful, this upgrade will remove sessions**

