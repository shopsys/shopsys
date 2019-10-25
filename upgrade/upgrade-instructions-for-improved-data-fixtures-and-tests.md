# Upgrade Instructions for Improved Data Fixtures and Tests

In [#1425](https://github.com/shopsys/shopsys/pull/1425), we changed the way our data fixtures are created so they are able to adapt to any domains and locales configuration.
Along with that, we also modified tests to make them resistant against such changes.

Most of the changes were done in `project-base` so if you want them in your project after the upgrade, you need to do that manually.
If any of the following instructions is not clear enough for you or you need more information, we recommend to take a look at the actual changes in [#1425](https://github.com/shopsys/shopsys/pull/1425).

- Change the way the data fixtures are created:
    - Remove all `Multidomain*DataFixture` classes and move the logic to regular `DataFixtures` classes.
    - When setting multilanguage or multidomain attributes, use translator (`t` or `tc` function with "dataFixtures" domain) and [`Domain`](https://github.com/shopsys/shopsys/blob/v8.1.0/packages/framework/src/Component/Domain/Domain.php) service to fill the values for all existing domains and locales, e.g.:
        ```php
            foreach ($this->domain as $domainConfig) {
                $locale = $domainConfig->getLocale();
                $domainId = $domainConfig->getId();
                $productData->name[$locale] = t('Name', [], 'dataFixtures', $locale);
                $productData->description[$domainId] = t('Name', [], 'dataFixtures', $locale);
            }
        ```
    - Remove `demo-data-products.csv` and `demo-data-customers.csv`, along with all the classes responsible for loading data from CSV files and move the data directly to [`ProductDataFixture`](https://github.com/shopsys/shopsys/blob/v8.1.0/project-base/src/Shopsys/ShopBundle/DataFixtures/Demo/ProductDataFixture.php) (you need to modify [`Performance/ProductDataFixture`](https://github.com/shopsys/shopsys/blob/v8.1.0/project-base/src/Shopsys/ShopBundle/DataFixtures/Performance/ProductDataFixture.php) as well) and [`UserDataFixture`](https://github.com/shopsys/shopsys/blob/v8.1.0/project-base/src/Shopsys/ShopBundle/DataFixtures/Demo/UserDataFixture.php).
    - update your [`phpstan.neon`](https://github.com/shopsys/shopsys/blob/v8.1.0/project-base/phpstan.neon):
        ```diff
        -    # A helper methods returning an array of persistent references using $this->getReference()
        -    message: '#^Method Shopsys\\ShopBundle\\DataFixtures\\ProductDataFixtureReferenceInjector::.+\(\) should return array<.+> but returns array<string, object>\.$#'
        -    path: %currentWorkingDirectory%/src/Shopsys/ShopBundle/DataFixtures/ProductDataFixtureReferenceInjector.php
        +    # Actually, we are setting an array item using "$array[] = $this->getReference()"
        +    message: '#^Array \(array<.+>\) does not accept object\.$#'
        +    path: %currentWorkingDirectory%/src/Shopsys/ShopBundle/DataFixtures/Demo/ProductDataFixture.php
        ```
    - Use [`PriceConverter`](https://github.com/shopsys/shopsys/blob/v8.1.0/packages/framework/src/Model/Pricing/PriceConverter.php) class for converting prices into domain currencies, e.g.:
        ```php
          foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
              $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domain->getId());
              $price = $this->priceConverter->convertPriceWithoutVatToPriceInDomainDefaultCurrency($price, $domain->getId());
  
              $transportData->pricesByCurrencyId[$currency->getId()] = $price;
          }
        ``` 
- Modify your functional tests:
    - Add `skipTestIfFirstDomainIsNotInEnglish`, `getReferenceForDomain`, and `getFirstDomainLocale` methods to your [`FunctionalTestCase`](https://github.com/shopsys/shopsys/blob/v8.1.0/project-base/tests/ShopBundle/Test/FunctionalTestCase.php).
    - When accessing a translated attribute on first domain, use `FunctionalTestCase::getFirstDomainLocale` instead of hard-coding the locale.
    - In case you need to load some entity saved in references, use `FunctionalTestCase::getReferenceDomain` method
    - In tests that make sense only for the English locale (e.g. tests for searching a particular phrase), you can skip their execution for different settings using `FunctionalTestCase::skipTestIfFirstDomainIsNotInEnglish`.
    - When asserting prices, use [`PriceConverter`](https://github.com/shopsys/shopsys/blob/v8.1.0/packages/framework/src/Model/Pricing/PriceConverter.php) class to convert the prices to proper currency.
    - Use `ParameterFacade::getParameterValueByValueTextAndLocale()` to get the proper entity in tests where the `ParameterValue` ids are used.
    - Modify your `app/config/packages/test/prezent_doctrine_translatable.yml` to use `%locale%` value for setting `fallback_locale` instead of hard-coding it.
- Modify your acceptance tests:
    - Add two new modules - [`LocalizationHelper`](https://github.com/shopsys/shopsys/blob/v8.1.0/project-base/tests/ShopBundle/Test/Codeception/Helper/LocalizationHelper.php) and [`NumberFormatHelper`](https://github.com/shopsys/shopsys/blob/v8.1.0/project-base/tests/ShopBundle/Test/Codeception/Helper/NumberFormatHelper.php)
        - The modules add new methods to acceptance tester that are useful in the tests, e.g.:
            - `amOnLocalizedRoute()`
            - `seeTranslationAdmin()`
            - `seeTranslationFrontend()`
            - `getFormattedPriceWithCurrencySymbolOnFrontend()`
        - Rewrite your tests to make them resistant against domains and locales settings using these methods.
        - Register the new modules in your `acceptance.suite.yml` configuration in `modules.enabled` section.
    - Update your [`Cart/index.html.twig`](https://github.com/shopsys/shopsys/blob/v8.1.0/project-base/src/Shopsys/ShopBundle/Resources/views/Front/Content/Cart/index.html.twig) - add JS class `js-cart-item-price` for item price column. It is used for identification of item price in cart in acceptance tests.
- Do not forget to update your [`services.yml`](https://github.com/shopsys/shopsys/blob/v8.1.0/project-base/src/Shopsys/ShopBundle/Resources/config/services.yml) and [`services_test.yml`](https://github.com/shopsys/shopsys/blob/v8.1.0/project-base/src/Shopsys/ShopBundle/Resources/config/services_test.yml).
- Run `php phing translations-dump` and fill in new translation for your locale in `dataFixtures.xx.po` (`xx` stands for your locale).
- Update your [`easy-coding-standard.yml`](https://github.com/shopsys/shopsys/blob/v8.1.0/project-base/easy-coding-standard.yml) (some of the data fixture classes need to be excluded from some checks).
- If you have extended any of the following classes, you need to update the constructor in your child as there are new dependencies:
    - [`PriceExtension`](https://github.com/shopsys/shopsys/blob/v8.1.0/packages/framework/src/Twig/PriceExtension.php) now depends on [`CurrencyFormatterFactory`](https://github.com/shopsys/shopsys/blob/v8.1.0/packages/framework/src/Component/CurrencyFormatter/CurrencyFormatterFactory.php)
    - [`LocalizationListener`](https://github.com/shopsys/shopsys/blob/v8.1.0/packages/framework/src/Model/Localization/LocalizationListener.php) now depends on [`AdministrationFacade`](https://github.com/shopsys/shopsys/blob/v8.1.0/packages/framework/src/Model/Administration/AdministrationFacade.php)
    - [`NumberFormatterExtension`](https://github.com/shopsys/shopsys/blob/v8.1.0/packages/framework/src/Twig/NumberFormatterExtension.php) now depends on [`AdministrationFacade`](https://github.com/shopsys/shopsys/blob/v8.1.0/packages/framework/src/Model/Administration/AdministrationFacade.php)
- Stop using `Domain::getAllIdsExcludingFirstDomain()` - the method is deprecated and will be removed in the next major release.
- fix your acceptance tests for single domain: ([#1477](https://github.com/shopsys/shopsys/pull/1477))
    - create second currency only for multidomain project ([diff](https://github.com/shopsys/shopsys/pull/1477/files))
    - fix used translation message ID in `PaymentImageUploadCest`
    ```diff
    -    $me->seeTranslationAdmin('Payment <strong><a href="{{ url }}">%name%</a></strong> modified', 'messages', [
    +    $me->seeTranslationAdmin('Payment <strong><a href="{{ url }}">{{ name }}</a></strong> modified', 'messages', [
             '{{ url }}' => '',
    -        '%name%' => t('Credit card', [], 'dataFixtures', $me->getAdminLocale()),
    +        '{{ name }}' => t('Credit card', [], 'dataFixtures', $me->getAdminLocale()),
         ]);
    ```
- fix your functional tests for single domain: ([#1479](https://github.com/shopsys/shopsys/pull/1479))
    - fix your `InputPriceRecalculationSchedulerTest` and `PriceExtensionTest` using this [diff](https://github.com/shopsys/shopsys/pull/1479/files)
