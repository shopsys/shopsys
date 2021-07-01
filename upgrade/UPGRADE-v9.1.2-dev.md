# [Upgrade from v9.1.1 to v9.1.2-dev](https://github.com/shopsys/shopsys/compare/v9.1.1...9.1)

This guide contains instructions to upgrade from version v9.1.1 to v9.1.2-dev.

**Before you start, don't forget to take a look at [general instructions](https://github.com/shopsys/shopsys/blob/7.3/UPGRADE.md) about upgrading.**
There you can find links to upgrade notes for other versions too.

- potential **BC break** ([#2300](https://github.com/shopsys/shopsys/pull/2300))
    - method `Shopsys\FrameworkBundle\Form\Admin\Customer\User\CustomerUserFormType::validateUniqueEmail()` has changed its interface
        - first argument `$email` was changed to accept `null` or `string` instead of `string` only

- replace class names defined by FQCN string by `*::class` constant ([#2319](https://github.com/shopsys/shopsys/pull/2300))
    - see #project-base-diff to update your project

- update composer dependency `composer/composer` in your project ([#2313](https://github.com/shopsys/shopsys/pull/2313))
    - versions bellow `1.10.22` has [reported security issue](https://github.com/composer/composer/security/advisories/GHSA-h5h8-pc6h-jvvx)
    - see #project-base-diff to update your project

- drop usage of Doctrine\Common\Cache\PhpFileCache as framework annotation cache ([#2326](https://github.com/shopsys/shopsys/pull/2326))
    - see #project-base-diff to update your project

- update your composer dependencies `symfony/security*`
    - run `composer update symfony/security-guard symfony/security-core`
        - version of both packages must be `4.4.23` or higher
    - using lower version is potential security risk
        - see more information https://github.com/symfony/symfony/security/advisories/GHSA-5pv8-ppvj-4h68

- remove unused multipleProductsInOrder.graphql file ([#2306](https://github.com/shopsys/shopsys/pull/2306))
    - see #project-base-diff to update your project

- update `ProductFilterPage` so it is resistant to case changes ([#2330](https://github.com/shopsys/shopsys/pull/2330))
    - see #project-base-diff to update your project

- extend and implement methods which are marked to be abstract ([#2337](https://github.com/shopsys/shopsys/pull/2337))
    - following methods will become abstract:
        - `Shopsys\FrameworkBundle\Model\Breadcrumb\ErrorPageBreadcrumbGenerator::getTranslatedBreadcrumbForNotFoundPage()`
        - `Shopsys\FrameworkBundle\Model\Breadcrumb\ErrorPageBreadcrumbGenerator::getTranslatedBreadcrumbForErrorPage()`
        - `Shopsys\FrameworkBundle\Model\Breadcrumb\SimpleBreadcrumbGenerator::getTranslatedBreadcrumbsByRouteNames()`
        - `Shopsys\FrameworkBundle\Model\Cart\Watcher\CartWatcherFacade::getMessageForNoLongerAvailableExistingProduct()`
        - `Shopsys\FrameworkBundle\Model\Cart\Watcher\CartWatcherFacade::getMessageForNoLongerAvailableProduct()`
        - `Shopsys\FrameworkBundle\Model\Cart\Watcher\CartWatcherFacade::getMessageForChangedProduct()`
        - `Shopsys\FrameworkBundle\Model\LegalConditions\LegalConditionsFacade::getTermsAndConditionsDownloadFilename()`
        - `Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingModeForBrandFacade::getSupportedOrderingModesNamesById()`
        - `Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingModeForListFacade::getSupportedOrderingModesNamesById()`
        - `Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingModeForSearchFacade::getSupportedOrderingModesNamesById()`
    - run `php phing translations-dump` to extract translations and translate it
    - see #project-base-diff to update your project
  
- improve performance of category sorting ([#2328](https://github.com/shopsys/shopsys/pull/2328))
    - you may want to check [article about category sorting](https://docs.shopsys.com/en/9.1/model/how-to-sort-categories/) to introduce performance improvements to your project
    - `CategoryTreeSorting.js` now calls different action to sort categories. You should check your custom implementation of category sorting **\[possible BC-Break\]**

- fix converting price for Transport and Payment in data fixtures ([#2354](https://github.com/shopsys/shopsys/pull/2354))
    - see #project-base-diff to update your project

- remove unnecessary setter injection in `CartController` ([#2349](https://github.com/shopsys/shopsys/pull/2349))
    - see #project-base-diff to update your project

- fix your data fixtures and tests to set the proper exchange rate for the demo currencies (if you use the default demo setup) ([#2332](https://github.com/shopsys/shopsys/pull/2332))
    - `PriceConverter::convertPriceWithoutVatToPriceInDomainDefaultCurrency` is deprecated, use `convertPriceWithoutVatToPriceInDomainDefaultCurrency` instead (the new method requires `$priceCurrency` argument)
    - `PriceConverter::convertPriceWithVatToPriceInDomainDefaultCurrency` is deprecated, use `convertPriceWithVatToPriceInDomainDefaultCurrency` instead (the new method requires `$priceCurrency` argument)
    - see #project-base-diff to update your project

- update your data fixtures to always generate data with same prices ([#2356](https://github.com/shopsys/shopsys/pull/2356))
    - see #project-base-diff to update your project

- increase minimal version of `symfony/proxy-manager-bridge` package ([#2359](https://github.com/shopsys/shopsys/pull/2359))
    - see #project-base-diff to update your project
    - don't forget to update dependency with `composer update symfony/proxy-manager-bridge` 

- **\[BC break\]** update node (to version 16) and npm packages ([#2336](https://github.com/shopsys/shopsys/pull/2336))
    - remove your `package-lock.yaml` and run `npm i` to create the new version of the file
    - paths to assets in `*.less` files now require path from the root of the app, you may want to update your custom styles
      ```diff
      -  background-image: url("/public/styleguide/images/icon_large_up.svg")
      +  background-image: url("/web/public/styleguide/images/icon_large_up.svg")
      ```
    - see #project-base-diff to update your project

- increase minimal version of `doctrine/dbal` package ([#2360](https://github.com/shopsys/shopsys/pull/2360))
    - see #project-base-diff to update your project
    - don't forget to update dependency with `composer update doctrine/dbal` 

- increase minimal version of `league/flysystem` package ([#2365](https://github.com/shopsys/shopsys/pull/2365))
    - see #project-base-diff to update your project
    - don't forget to update dependency with `composer update league/flysystem`

- update form setting for quantity in cart ([#2367](https://github.com/shopsys/shopsys/pull/2367))
    - see #project-base-diff to update your project
