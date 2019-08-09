# [Upgrade from v8.0.1-dev to v8.1.0-dev](https://github.com/shopsys/shopsys/compare/8.0...HEAD)

This guide contains instructions to upgrade from version v8.0.1-dev to v8.1.0-dev.

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
 
### Tools
- let Phing properties `is-multidomain` and `translations.dump.locales` be auto-detected ([#1308](https://github.com/shopsys/shopsys/pull/1308))
    - stop overriding the Phing properties `is-multidomain` and `translations.dump.locales` in your `build.xml`, these properties should not be used anymore
        ```diff
          <property name="path.framework" value="${path.vendor}/shopsys/framework"/>

        - <property name="is-multidomain" value="true"/>
        - <property name="translations.dump.locales" value="cs en xx"/>
          <property name="phpstan.level" value="1"/>
        ```
    - if you use the deprecated properties in your `build.xml` yourself, make the particular Phing target dependent on `domains-info-load` and use new auto-detected properties `domains-info.is-multidomain` and `domains-info.locales` instead
        ```diff
        - <target name="my-custom-localization-target">
        + <target name="my-custom-localization-target" depends="domains-info-load">
              <exec executable="${path.custom-localization.executable}" passthru="true" checkreturn="true">
        -         <arg line="${translations.dump.locales}"/>
        +         <arg line="${domains-info.locales}"/>
              </exec>
          </target>
        ```

[shopsys/framework]: https://github.com/shopsys/framework
