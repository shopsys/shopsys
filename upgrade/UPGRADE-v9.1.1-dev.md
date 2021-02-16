# [Upgrade from v9.1.0 to v9.1.1-dev](https://github.com/shopsys/shopsys/compare/v9.1.0...9.1)

This guide contains instructions to upgrade from version v9.1.0 to v9.1.1-dev.

**Before you start, don't forget to take a look at [general instructions](https://github.com/shopsys/shopsys/blob/master/UPGRADE.md) about upgrading.**
There you can find links to upgrade notes for other versions too.

- sanitize non-printable search text ([#2174](https://github.com/shopsys/shopsys/pull/2174))
    - see #project-base-diff to update your project
  
- trim search text from spaces ([#2187](https://github.com/shopsys/shopsys/pull/2187))
    - see #project-base-diff to update your project

- update annotations for EntityExtensionTest.php ([#2197](https://github.com/shopsys/shopsys/pull/2197))
    - see #project-base-diff to update your project

- replace `sensiolabs/security-checker` with `enlightn/security-checker` ([#2211](https://github.com/shopsys/shopsys/pull/2211))
    - you can run `composer remove sensiolabs/security-checker; composer require enlightn/security-checker ^1.3` to avoid manual editing of composer files
    - security checks are now executed automatically only after composer update, you should add the check into your CI pipeline
    - you can run `composer security-check` or `php phing security-check` to perform security checks
    - see #project-base-diff to update your project

- update elfinder installer to be compatible with `helios-ag/fm-elfinder-bundle` v10.1 ([#2217](https://github.com/shopsys/shopsys/pull/2217))
    - if you have updated the `assets` phing target, you should remove `shopsys:elfinder:post-install` call
      and add `--docroot` option for `elfinder:install` command. See PR for inspiration

- all fields defined in GraphQL type `Product` are correctly inherited in `RegularProduct`, `Variant`, `MainVariant` types ([#2195](https://github.com/shopsys/shopsys/pull/2195))
    - if you extended `Product` type, you could remove duplicate definitions in `RegularProduct`, `Variant`, `MainVariant` types

- add test to check if entities are refreshed after order is completed and after recalculation ([#2202](https://github.com/shopsys/shopsys/pull/2202))
    - see #project-base-diff to update your project

- fix smoke test for a new product for first domain on https ([#2214](https://github.com/shopsys/shopsys/pull/2214))
    - see #project-base-diff to update your project

- Frontend API: add test for creating order with no product ([#2221](https://github.com/shopsys/shopsys/pull/2221))
    - see #project-base-diff to update your project

- Frontend API: correctly inherited base type in `AdvertCodeDecorator`, `AdvertImageDecorator`, `ProductPriceDecorator` types ([#2222](https://github.com/shopsys/shopsys/pull/2222))
    - if you extended `Advert` type, you can remove duplicate definitions in `AdvertCode` and `AdvertImage` types
    - if you extended `Price` type, you can remove duplicate definitions in `ProductPrice` type

- update phpstan/phpstan to the latest version ([#2241](https://github.com/shopsys/shopsys/pull/2241))
    - see #project-base-diff to update your project

- improve acceptance test of product filter ([#2226](https://github.com/shopsys/shopsys/pull/2226))
    - see #project-base-diff to update your project
    - we have also improved displaying of price filter in our basic design, decide if such change is suitable for your project

- initialize CKEditor after the click into appropriate field ([#2177](https://github.com/shopsys/shopsys/pull/2177))
    - see #project-base-diff to update your project

- allow multiple elasticsearch hosts ([#2240](https://github.com/shopsys/shopsys/pull/2240))
    - now it's possible to set multiple elasticsearch hosts like `'["elasticsearch:9200", "elasticsearch2:9200"]'`
    - `Elasticsearch\ClientBuilder` is now created with a different factory, you may want to check your overridden service definition (see PR for details)
