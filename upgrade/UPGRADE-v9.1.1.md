# [Upgrade from v9.1.0 to v9.1.1](https://github.com/shopsys/shopsys/compare/v9.1.0...v9.1.1)

This guide contains instructions to upgrade from version v9.1.0 to v9.1.1.

**Before you start, don't forget to take a look at [general instructions](https://github.com/shopsys/shopsys/blob/master/UPGRADE.md) about upgrading.**
There you can find links to upgrade notes for other versions too.

- sanitize non-printable search text ([#2174](https://github.com/shopsys/shopsys/pull/2174))
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/2bdd245c6598c12ef1e18981f42effa04cc26c92) to update your project

- trim search text from spaces ([#2187](https://github.com/shopsys/shopsys/pull/2187))
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/74b105b00cd4ebcbf5287f195de63fbc0cbac6c3) to update your project

- update annotations for EntityExtensionTest.php ([#2197](https://github.com/shopsys/shopsys/pull/2197))
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/2f55a9d88420aeeb671f1fd2e8f22e7f4ef3a54b) to update your project

- replace `sensiolabs/security-checker` with `enlightn/security-checker` ([#2211](https://github.com/shopsys/shopsys/pull/2211))
    - you can run `composer remove sensiolabs/security-checker; composer require enlightn/security-checker ^1.3` to avoid manual editing of composer files
    - security checks are now executed automatically only after composer update, you should add the check into your CI pipeline
    - you can run `composer security-check` or `php phing security-check` to perform security checks
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/f4e4138f03c476786933daaeda5a37d55321d176) to update your project

- update elfinder installer to be compatible with `helios-ag/fm-elfinder-bundle` v10.1 ([#2217](https://github.com/shopsys/shopsys/pull/2217))
    - if you have updated the `assets` phing target, you should remove `shopsys:elfinder:post-install` call
      and add `--docroot` option for `elfinder:install` command. See PR for inspiration

- all fields defined in GraphQL type `Product` are correctly inherited in `RegularProduct`, `Variant`, `MainVariant` types ([#2195](https://github.com/shopsys/shopsys/pull/2195))
    - if you extended `Product` type, you could remove duplicate definitions in `RegularProduct`, `Variant`, `MainVariant` types

- add test to check if entities are refreshed after order is completed and after recalculation ([#2202](https://github.com/shopsys/shopsys/pull/2202))
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/41c4b98c381ae1cd4f902375a312a1ee01cff59e) to update your project

- fix smoke test for a new product for first domain on https ([#2214](https://github.com/shopsys/shopsys/pull/2214))
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/df0035078548cc7c4da341d13ea747ee78baab13) to update your project

- Frontend API: add test for creating order with no product ([#2221](https://github.com/shopsys/shopsys/pull/2221))
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/b90cfee7e5e2b6c57e0d16d546c44adee5d66c93) to update your project

- Frontend API: correctly inherited base type in `AdvertCodeDecorator`, `AdvertImageDecorator`, `ProductPriceDecorator` types ([#2222](https://github.com/shopsys/shopsys/pull/2222))
    - if you extended `Advert` type, you can remove duplicate definitions in `AdvertCode` and `AdvertImage` types
    - if you extended `Price` type, you can remove duplicate definitions in `ProductPrice` type

- update phpstan/phpstan to the latest version ([#2241](https://github.com/shopsys/shopsys/pull/2241))
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/c2c322a6f641acbb3157a81c039cc4cd86c8a34d) to update your project

- improve acceptance test of product filter ([#2226](https://github.com/shopsys/shopsys/pull/2226))
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/87d6200b36eaf6495e81a56fa386f54d43aa323d) to update your project
    - we have also improved displaying of price filter in our basic design, decide if such change is suitable for your project

- initialize CKEditor after the click into appropriate field ([#2177](https://github.com/shopsys/shopsys/pull/2177))
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/c5a32cfdb554ceb29885a6b8837ce9f4f02f5a10) to update your project

- allow multiple elasticsearch hosts ([#2240](https://github.com/shopsys/shopsys/pull/2240))
    - now it's possible to set multiple elasticsearch hosts like `'["elasticsearch:9200", "elasticsearch2:9200"]'`
    - `Elasticsearch\ClientBuilder` is now created with a different factory, you may want to check your overridden service definition (see PR for details)

- unify logo rendering on homepage and subpages ([#2048](https://github.com/shopsys/shopsys/pull/2048))
    - on homepage is no longer H1 element, consider adding it in your custom design
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/5cf451c1a17d8f9e65d9e9c0f53909593f402a59) to update your project

- replace deprecated functionality ([#2233](https://github.com/shopsys/shopsys/pull/2233))
    - replace method by throwing specific exception
        - `Shopsys\FrameworkBundle\Component\Elasticsearch\Exception\ElasticsearchIndexException::indexAlreadyExists()`
            - you should throw `Shopsys\FrameworkBundle\Component\Elasticsearch\Exception\ElasticsearchIndexAlreadyExistsException` directly
    - replace usage of console command and phing targets
        - usage of command `shopsys:elasticsearch:indexes-create` must be replaced by `shopsys:elasticsearch:indexes-migrate`
        - usage of phing target `elasticsearch-index-create` must be replaced by `elasticsearch-index-migrate`
        - usage of phing target `test-elasticsearch-index-create` must be replaced by `test-elasticsearch-index-migrate`
    - replace using of deprecated method
        - calling the method `Shopsys\FrameworkBundle\Component\Elasticsearch\IndexFacade::create()` should be replaced by `Shopsys\FrameworkBundle\Component\Elasticsearch\IndexFacade::migrate()`
            - method will change its visibility from `public` to `protected` 
    - avoid using class `Shopsys\FrameworkBundle\Command\Elasticsearch\ElasticsearchIndexesCreateCommand`
        - class was deprecated and will me removed in next major version

- improve docker-sync reliability on MacOS ([#2264](https://github.com/shopsys/shopsys/pull/2264))
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/b9801f7ebcbf1bfaeabb0961201403709ad3e911) to update your project

- use WSL 2 instead of docker-sync on Windows ([#2272](https://github.com/shopsys/shopsys/pull/2272))
    - if you are developing on Windows machine, we recommend you to update your installation to our new one using WSL 2. 
      Read [Installation Using Docker on Windows 10](https://github.com/shopsys/shopsys/blob/9.1/docs/installation/installation-using-docker-windows-10.md) article for more information.
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/9c02fd65bdd6f27cf3ca7b5eef7b1da7f264a68d) to update your project

- update your composer.json ([#2285](https://github.com/shopsys/shopsys/pull/2285))
    - add conflict for `codeception/codeception` versions 4.1.19 and higher
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/3c92876110323e27a97f3565e402ee893f058b60) to update your project

- improve image lazy loading ([#2268](https://github.com/shopsys/shopsys/pull/2268))
    - supported browsers now use native lazy loading
    - see [project-base-diff](https://github.com/shopsys/project-base/commit/43941d32d8efb299602522d6178c387d4e11c189) to update your project
