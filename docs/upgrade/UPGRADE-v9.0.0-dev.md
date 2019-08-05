# [Upgrade from v8.0.0 to v9.0.0-dev](https://github.com/shopsys/shopsys/compare/v8.0.0...HEAD)

This guide contains instructions to upgrade from version v8.0.0 to v9.0.0-dev.

**Before you start, don't forget to take a look at [general instructions](/UPGRADE.md) about upgrading.**
There you can find links to upgrade notes for other versions too.

## [shopsys/framework]

### Infrastructure
- follow infrastructure instructions in the [separate article](/docs/upgrade/upgrade-instructions-for-calculation-of-product-visibility-asynchronously.md#infrastructure) to add RabbitMQ ([#1228](https://github.com/shopsys/shopsys/pull/1228))

### Application
- follow application instructions in the [separate article](/docs/upgrade/upgrade-instructions-for-calculation-of-product-visibility-asynchronously.md#application) to use RabbitMQ to calculate visibility of products ([#1228](https://github.com/shopsys/shopsys/pull/1228))

[shopsys/framework]: https://github.com/shopsys/framework
