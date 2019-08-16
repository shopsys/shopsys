# [Upgrade from v7.3.1 to v7.3.2-dev](https://github.com/shopsys/shopsys/compare/v7.3.1...7.3)

This guide contains instructions to upgrade from version v7.3.1 to v7.3.2-dev.

**Before you start, don't forget to take a look at [general instructions](/UPGRADE.md) about upgrading.**
There you can find links to upgrade notes for other versions too.

## [shopsys/framework]

### Infrastructure
- update your `docker/php-fpm/Dockerfile` like this ([#1282](https://github.com/shopsys/shopsys/pull/1282)):
    ```diff
        # libicu-dev for intl extension
            # libpg-dev for connection to postgres database
            # autoconf needed by "redis" extension
    -       RUN apt-get install -y \
    +       RUN apt-get update && \
    +           apt-get install -y \
                libpng-dev \
                libjpeg-dev \
                libfreetype6-dev \
    ```

### Application
- fix the typos in translation messages and demo data ([#1335](https://github.com/shopsys/shopsys/pull/1335))
    - replace "then" by "than" when in context of making comparisons, eg.:
        ```diff
        - new Constraints\Length(['max' => 100, 'maxMessage' => 'First name cannot be longer then {{ limit }} characters']),
        + new Constraints\Length(['max' => 100, 'maxMessage' => 'First name cannot be longer than {{ limit }} characters']),
        ```
    - replace "e-mail" by "email" in English texts
        - even though both spellings are valid, "email" is much more common
    - make these replacements in your application code, test code, data fixtures, and translation messages

### Configuration
- update your `app/config/packages/doctrine.yml` ([#1273](https://github.com/shopsys/shopsys/pull/1273))
    ```diff
       ShopsysShopBundle:
           type: annotation
           dir: '%shopsys.root_dir%/src/Shopsys/ShopBundle/Model'
           alias: ShopsysShopBundle
           prefix: Shopsys\ShopBundle\Model
           is_bundle: false
    +  ShopsysShopBundleComponent:
    +      type: annotation
    +      dir: '%shopsys.root_dir%/src/Shopsys/ShopBundle/Component'
    +      alias: ShopsysShopBundleComponent
    +      prefix: Shopsys\ShopBundle\Component
    +      is_bundle: false
    ```

[shopsys/framework]: https://github.com/shopsys/framework