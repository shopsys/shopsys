# [Upgrade from v7.3.4 to v7.3.5-dev](https://github.com/shopsys/shopsys/compare/v7.3.4...7.3)

This guide contains instructions to upgrade from version v7.3.4 to v7.3.5-dev.

**Before you start, don't forget to take a look at [general instructions](https://github.com/shopsys/shopsys/blob/7.3/UPGRADE.md) about upgrading.**
There you can find links to upgrade notes for other versions too.

## Application
- add `psr/event-dispatcher` to your composer dependencies in order to prevent PHPStan errors in Event classes ([#1894](https://github.com/shopsys/shopsys/pull/1894))
    - add `"psr/event-dispatcher": "0.6.0",` to `require-dev` section in your `composer.json` file

- enable automatic deleting of sessions older than 7 days in Redis ([#1842](https://github.com/shopsys/shopsys/pull/1842))
    - see #project-base-diff to update your project
    - you should consider what to do with current sessions, if you want to keep them, set them TTL or delete them

- add missing elasticsearch host to production docker-compose.yml file ([#1861](https://github.com/shopsys/shopsys/pull/1861))
    - see #project-base-diff to update your project
