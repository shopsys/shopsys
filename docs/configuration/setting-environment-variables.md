# Setting environment variables

This article describes how to set configure environment variables in different environments and even on some commonly used webservers.

## Global configuration of environment variables

Shopsys Platform as Symfony based application is using the same environment configuration as Symfony.

We recommend using [`.env` files](https://symfony.com/doc/5.4/configuration.html#configuring-environment-variables-in-env-files) for configuring environment variables as it can be easily maintained.
There is also advantage of different configuration through different application environments (such as dev, test and prod).

!!! note

    You may also use [`.env.local` file](https://symfony.com/doc/5.4/configuration.html#overriding-environment-values-via-env-local) for your personal configuration.
    The `*.local` files should not be commited so they are ingored with `.gitignore` by default.

For better understanding [visit the whole documentation here](https://symfony.com/doc/5.4/configuration.html#configuration-environments) where you can find more info about priority, [variables encrypting](https://symfony.com/doc/5.4/configuration.html#encrypting-environment-variables-secrets), [syntax](https://symfony.com/doc/5.4/configuration.html#env-file-syntax) and much more.

## Overriding by real environment variable of webserver

When `.env` files are not enough for your purpose you may override environment variables directly by webserver setting.
These settings override any variables configured in `.env` files.

!!! note

    Bellow is described the easiest way.
    For better understanding, we recommend finding out the documentation of the specific platform.

### Docker

When using docker containers, add the environment variable to the `docker-compose.yml` file to `php-fpm` definition like in the example below

```diff
    php-fpm:
        build:
            context: .
            dockerfile: docker/php-fpm/Dockerfile
            target: development
            args:
                www_data_uid: 1000
                www_data_gid: 1000
        container_name: shopsys-framework-php-fpm
        volumes:
            - shopsys-framework-sync:/var/www/html
            - shopsys-framework-vendor-sync:/var/www/html/vendor
            - shopsys-framework-web-sync:/var/www/html/web
        ports:
            - "35729:35729"
+       environment:
+           - REDIS_PREFIX='my_awesome_app'
```

### Native installation

Without containers, you must set environment variable on the host machine, typically in unix like OS by executing

```sh
export REDIS_PREFIX='my_awesome_app'
```
