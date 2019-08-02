# [Upgrade from v8.0.0 to Unreleased](https://github.com/shopsys/shopsys/compare/v8.0.0...HEAD)

This guide contains instructions to upgrade from version v8.0.0 to Unreleased.

**Before you start, don't forget to take a look at [general instructions](/UPGRADE.md) about upgrading.**
There you can find links to upgrade notes for other versions too.


## [shopsys/framework]

### Infrastructure
- remove the unnecessary volume mount of `php.ini` from your `docker/conf/docker-compose-win.yml.dist` and possibly `docker-compose.yml` ([#1276](https://github.com/shopsys/shopsys/pull/1276))
    ```diff
      volumes:
          -   shopsys-framework-sync:/var/www/html
          -   shopsys-framework-vendor-sync:/var/www/html/vendor
          -   shopsys-framework-web-sync:/var/www/html/web
    -     -   ./docker/php-fpm/php-ini-overrides.ini:/usr/local/etc/php/php.ini
      ports:
    ```

[shopsys/framework]: https://github.com/shopsys/framework
