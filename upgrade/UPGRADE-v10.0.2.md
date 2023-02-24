# [Upgrade from v10.0.1 to v10.0.2](https://github.com/shopsys/shopsys/compare/v10.0.1...v10.0.2)

This guide contains instructions to upgrade from version v10.0.1 to v10.0.2.

**Before you start, don't forget to take a look at [general instructions](https://github.com/shopsys/shopsys/blob/master/UPGRADE.md) about upgrading.**
There you can find links to upgrade notes for other versions too.

## Application

- use logger methods as they're specified in PSR-3 ([#2483](https://github.com/shopsys/shopsys/pull/2483))
    - replace any usages of `Logger::add<Notice|Debug|Error|Info>` with corresponding call of `notice|debug|error|info` method
