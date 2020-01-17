# [Upgrade from v8.1.0 to v8.1.1](https://github.com/shopsys/shopsys/compare/v8.1.0...v8.1.1)

This guide contains instructions to upgrade from version v8.0.0 to v8.1.0.

**Before you start, don't forget to take a look at [general instructions](/UPGRADE.md) about upgrading.**
There you can find links to upgrade notes for other versions too.

## [shopsys/framework]

### Infrastructure

- lock NPM to version 6.13.2 in your php-fpm Dockerfile `docker/php-fpm/Dockerfile`
    ```diff
    +   # hotfix for https://github.com/npm/cli/issues/613
    +   RUN npm install -g npm@6.13.2
        
        # install grunt cli used by frontend developers for continuous generating of css files
        RUN npm install -g grunt-cli
    ```

[shopsys/framework]: https://github.com/shopsys/framework
