# [Upgrade from v9.0.1 to v9.0.2-dev](https://github.com/shopsys/shopsys/compare/v9.0.1...9.0)

This guide contains instructions to upgrade from version v9.0.1 to v9.0.2-dev.

**Before you start, don't forget to take a look at [general instructions](https://github.com/shopsys/shopsys/blob/7.3/UPGRADE.md) about upgrading.**
There you can find links to upgrade notes for other versions too.

- generate images_id_seq in data fixtures automatically ([#1918](https://github.com/shopsys/shopsys/pull/1918))
    - see #project-base-diff to update your project

- remove unused route /contactForm/ ([#1940](https://github.com/shopsys/shopsys/pull/1940))
    - see #project-base-diff] to update your project

- remove unnecessary else conditions ([#1938](https://github.com/shopsys/shopsys/pull/1938))
    - see #project-base-diff to update your project

- use __DIR__ instead of dirname(__FILE__) ([#1939](https://github.com/shopsys/shopsys/pull/1939))
    - see #project-base-diff to update your project

- call static method as static ([#1937](https://github.com/shopsys/shopsys/pull/1937))
    - see #project-base-diff to update your project

- update your project to upload temporary files to abstract filesystem ([#1955](https://github.com/shopsys/shopsys/pull/1955))
    - see #project-base-diff to update your project

- fixed displaying errors in popup window ([#1970](https://github.com/shopsys/shopsys/pull/1970))
    - see #project-base-diff to update your project

- categories in admin are now loaded using admin locale ([#1982](https://github.com/shopsys/shopsys/pull/1982))
    - `CategoryRepository::getTranslatedAllWithoutBranch()` is deprecated, use `CategoryRepository::getAllTranslatedWithoutBranch()` instead
    - `CategoryRepository::getTranslatedAll` is deprecated, use `CategoryRepository::getAllTranslated()` instead
    - `CategoryFacade::getTranslatedAllWithoutBranch()` is deprecated, use `CategoryFacade::getAllTranslatedWithoutBranch()` instead
    - `CategoryFacade::getTranslatedAll` is deprecated, use `CategoryFacade::getAllTranslated()` instead
    - `ProductCategoryFilter::__construct()` has changed its interface and argument Domain will be removed in next major
    ```diff
    -   public function __construct(CategoryFacade $categoryFacade, Domain $domain = null)
    +   public function __construct(CategoryFacade $categoryFacade, ?Domain $domain = null, ?LocalizationAlias $localization = null)
    ```

- remove customer and his addresses when customer user is deleted ([#1977](https://github.com/shopsys/shopsys/pull/1977))
    - there is no need to update your project in any way, we are just noticing you that customer and his addresses are now removed when all his customer users were deleted

- update your redis build-version to include application environment ([#1985](https://github.com/shopsys/shopsys/pull/#1985))
    - see #project-base-diff to update your project
    - run `php phing build-version-generate`

- set timezone for your crons ([#2000](https://github.com/shopsys/shopsys/pull/2000))
    - see #project-base-diff to update your project

- add acceptance test for testing sending order as logged customer ([#2011](https://github.com/shopsys/shopsys/pull/2011))
    - see #project-base-diff to update your project
