# [Upgrade from v10.0.2 to v10.0.3-dev](https://github.com/shopsys/shopsys/compare/v10.0.2...10.0)

This guide contains instructions to upgrade from version v10.0.2 to v10.0.3-dev.

**Before you start, don't forget to take a look at [general instructions](https://github.com/shopsys/shopsys/blob/master/UPGRADE.md) about upgrading.**
There you can find links to upgrade notes for other versions too.

## Coding standards

- FE API: all article user errors now provide user error code ([#2493](https://github.com/shopsys/shopsys/pull/2493))
     - there is a new sniff enabled (`ForbiddenClassesSniff`) that disallows direct usage of the following classes:
          - `Overblog\GraphQLBundle\Error\UserError`
          - `GraphQL\Error\UserError`
     - instead of these classes, you should use suitable implementations of `UserErrorWithCodeInterface`
