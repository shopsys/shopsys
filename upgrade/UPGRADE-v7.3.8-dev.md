# [Upgrade from v7.3.7 to v7.3.8-dev](https://github.com/shopsys/shopsys/compare/v7.3.7...7.3)

This guide contains instructions to upgrade from version v7.3.7 to v7.3.8-dev.

**Before you start, don't forget to take a look at [general instructions](https://github.com/shopsys/shopsys/blob/7.3/UPGRADE.md) about upgrading.**
There you can find links to upgrade notes for other versions too.

- upgrade CA certificates in your Dockerfile
  - see #project-base-diff
- add security check after your composer install/update commands
  - see #project-base-diff
- update Redis client to version 5.2.1 and Redis server to 5.0
    - see #project-base-diff
