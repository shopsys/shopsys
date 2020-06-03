# [Upgrade from v8.1.1 to v8.1.2](https://github.com/shopsys/shopsys/compare/v8.1.1...v8.1.2)

This guide contains instructions to upgrade from version v8.1.1 to v8.1.2.

**Before you start, don't forget to take a look at [general instructions](/UPGRADE.md) about upgrading.**
There you can find links to upgrade notes for other versions too.

## [shopsys/framework]

[shopsys/framework]: https://github.com/shopsys/framework

### Application

- update selling to in `ProductDataFixture.php` in order to fix failing tests (#1677)
    ```diff
    -   $this->setSellingFrom($productData, '11.2.2020');
    +   $this->setSellingFrom($productData, '11.2.2320');
    ```

- pin version of `fp/jsformvalidator-bundle` to `~1.5.1` in `composer.json` as next minor is not meant to be used with Symfony 3 ([#1790](https://github.com/shopsys/shopsys/pull/1790))
    ```diff
    -   "fp/jsformvalidator-bundle": "^1.5.1",
    +   "fp/jsformvalidator-bundle": "~1.5.1",
    ```

- pin version of `jms/translation-bundle` to `1.4.4` in `composer.json` as it prevents problem described in ([JMSTranslationBundle/#486](https://github.com/schmittjoh/JMSTranslationBundle/issues/486))
    ```diff
    -   "jms/translation-bundle": "^1.4.1",
    +   "jms/translation-bundle": "1.4.4",
    ```

- add protection before double submit forms ([#1864](https://github.com/shopsys/shopsys/pull/1864))
    - remove `src/Shopsys/ShopBundle/Resources/scripts/frontend/form.js` from you project

- update `snc/redis-bundle` to version 3.2.2 in order to fix problems with redis ([#1865](https://github.com/shopsys/shopsys/pull/1865))
    - change version of `snc/redis-bundle` in your `composer.json` to `^3.2.2`
    - change minimum required version of PHP and overriding of PHP in `platform` section in `composer.json` to `7.1.3`
