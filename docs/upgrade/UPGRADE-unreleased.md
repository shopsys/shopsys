# [Upgrade from v7.3.1 to Unreleased](https://github.com/shopsys/shopsys/compare/v7.3.1...HEAD)

This guide contains instructions to upgrade from version v7.3.1 to Unreleased.

**Before you start, don't forget to take a look at [general instructions](/UPGRADE.md) about upgrading.**
There you can find links to upgrade notes for other versions too.

## [shopsys/framework]

### Application
- constructors of `FrameworkBundle\Model\Mail\Mailer` and `FrameworkBundle\Component\Cron\CronFacade` classes were changed so if you extend them change them accordingly: ([#875](https://github.com/shopsys/shopsys/pull/875)).
    - `CronFacade::__construct(Logger $logger, CronConfig $cronConfig, CronModuleFacade $cronModuleFacade, Mailer $mailer)`
    - `Mailer::__construct(Swift_Mailer $swiftMailer, Swift_Transport $realSwiftTransport)`
    - find all usages of the constructors and fix them
- `EntityNameResolver` was added into constructor of these classes: ([#918](https://github.com/shopsys/shopsys/pull/918))
    - CronModuleFactory
    - PersistentReferenceFactory
    - ImageFactory
    - FriendlyUrlFactory
    - SettingValueFactory
    - UploadedFileFactory
    - AdministratorGridLimitFactory
    - EnabledModuleFactory
    - ProductCategoryDomainFactory
    - ProductVisibilityFactory
    - ScriptFactory
    - SliderItemFactory

    In case of extending one of these classes, you should add an `EntityNameResolver` to a constructor and use it in a `create()` method to resolve correct class to return.
- run `php phing standards-fix` so all nullable values will be now defined using nullability (?) symbol ([#1010](https://github.com/shopsys/shopsys/pull/1010))
- replace `IvoryCKEditorBundle` with `FOSCKEditorBundle` ([#1072](https://github.com/shopsys/shopsys/pull/1072))
    - replace the registration of the bundle in `app/AppKernel`
        ```diff
        - new Ivory\CKEditorBundle\IvoryCKEditorBundle(), // has to be loaded after FrameworkBundle and TwigBundle
        + new FOS\CKEditorBundle\FOSCKEditorBundle(), // has to be loaded after FrameworkBundle and TwigBundle
        ```
    - rename `app/config/packages/ivory_ck_editor.yml` to `app/config/packages/fos_ck_editor.yml` and change the root key in its content
        ```diff
        - ivory_ck_editor:
        + fos_ck_editor:
        ```
    - change the package in `composer.json`
        ```diff
        - "egeloen/ckeditor-bundle": "^4.0.6",
        + "friendsofsymfony/ckeditor-bundle": "^2.1",
        ```
    - update all usages of the old bundle in
        - extended twig templates like
            ```diff
            - {% use '@IvoryCKEditor/Form/ckeditor_widget.html.twig' with ckeditor_widget as base_ckeditor_widget %}
            + {% use '@FOSCKEditor/Form/ckeditor_widget.html.twig' with ckeditor_widget as base_ckeditor_widget %}
            ```
        - javascripts like
            ```diff
            - if (element.type === Shopsys.constant('\\Ivory\\CKEditorBundle\\Form\\Type\\CKEditorType::class')) {
            + if (element.type === Shopsys.constant('\\FOS\\CKEditorBundle\\Form\\Type\\CKEditorType::class')) {
            ```
        - configuration files like
            ```diff
            Shopsys\FrameworkBundle\Form\WysiwygTypeExtension:
                tags:
            -       - { name: form.type_extension, extended_type: Ivory\CKEditorBundle\Form\Type\CKEditorType }
            +       - { name: form.type_extension, extended_type: FOS\CKEditorBundle\Form\Type\CKEditorType }
            ```
        - php code like
            ```diff
            namespace Shopsys\FrameworkBundle\Form\Admin\Transport;

            - use Ivory\CKEditorBundle\Form\Type\CKEditorType;
            + use FOS\CKEditorBundle\Form\Type\CKEditorType;
            ```
- update the minimal PHP version in your `composer.json` in `require` and `config.platform` section to `7.2` because version `7.1` is no longer supported in Shopsys Framework ([#1066](https://github.com/shopsys/shopsys/pull/1066))
- run [db-create](/docs/introduction/console-commands-for-application-management-phing-targets.md#db-create) (this one even on production) and `test-db-create` phing targets to install extension for UUID
- if you want to use our experimental API read [introduction to backend API](/docs/backend-api/introduction-to-backend-api.md)
- update your application and tests to correctly handle availabilities and stock ([#1115](https://github.com/shopsys/shopsys/pull/1115))
    - copy and replace the functional test [AvailabilityFacadeTest.php](https://github.com/shopsys/project-base/blob/master/tests/ShopBundle/Functional/Model/Product/Availability/AvailabilityFacadeTest.php) in `tests/ShopBundle/Functional/Model/Product/Availability/` to test deletion and replacement of availabilities properly
    - if you have made any custom changes to the test you should merge your changes with the ones described in the pull request linked above
    - add a test service definition for `AvailabilityDataFactory` in your `src/Shopsys/ShopBundle/Resources/config/services_test.yml` configuration:
        ```diff
            Shopsys\FrameworkBundle\Model\Transport\TransportDataFactoryInterface: '@Shopsys\ShopBundle\Model\Transport\TransportDataFactory'

        +   Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityDataFactoryInterface: '@Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityDataFactory'
        +
            Shopsys\FrameworkBundle\Model\Payment\PaymentDataFactoryInterface: '@Shopsys\ShopBundle\Model\Payment\PaymentDataFactory'
        ```
    - check and fix your other tests, they might start failing if they assumed `Product::$availability` is not null when the product is using stock, or that stock quantity is not null when it's not using stock
- follow upgrade instructions for entities simplification in the [separate article](./upgrade-instructions-for-entities-simplification.md) ([#1123](https://github.com/shopsys/shopsys/pull/1123))
- JS functionality connected to `#js-close-without-saving` has been removed, implement your own if you relied on this ([#1168](https://github.com/shopsys/shopsys/pull/1168))
- update your way of registration of `FriendlyUrlDataProviders` ([#1140](https://github.com/shopsys/shopsys/pull/1140))
    - the namespace of `FriendlyUrlDataProviderInterface` and `FriendlyUrlDataProviderRegistry` has changed from `Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\CompilerPass` to `Shopsys\FrameworkBundle\Component\Router\FriendlyUrl` so change all your usages accordingly
    - you no longer need to tag your `FriendlyUrlDataProviders` with `shopsys.friendly_url_provider` because it is now done automatically
    - remove the usages of `RegisterFriendlyUrlDataProviderCompilerPass` class and `FriendlyUrlDataProviderRegistry::registerFriendlyUrlDataProvider` method because they have been removed
- update your way of registration of `BreadcrumbGenerator` classes ([#1141](https://github.com/shopsys/shopsys/pull/1140))
    - remove the usages of `FrontBreadcrumbResolverFactory` class as it has been removed.
    - remove the usages of `BreadcrumbResolver::registerGenerator` method as it has been removed
    - update your usages of `BreadcrumbResolver::__contruct()` as it now requires a new parameter
- run `php phing phpstan` in order to check, that you are not using any private, protected or removed constant from Shopsys packages ([#1181](https://github.com/shopsys/shopsys/pull/1181))
- update your code due to collection entities encapsulation change ([#1047](https://github.com/shopsys/shopsys/pull/1047))
    - when you use values from an entity getter that has returned an `ArrayCollection` before, use the value as an array instead of an object, for example:
        - `src/Shopsys/ShopBundle/Form/Front/Order/TransportAndPaymentFormType.php`
            ```diff
            if ($payment instanceof Payment && $transport instanceof Transport) {
            -   if ($payment->getTransports()->contains($transport)) {
            +   if (in_array($transport, $payment->getTransports(), true)) {
                    $relationExists = true;
                }
            ```
        - `tests/ShopBundle/Functional/Model/Payment/PaymentTest.php`
            ```diff
                $transportFacade->deleteById($transport->getId());
            -   $this->assertFalse($payment->getTransports()->contains($transport));
            +   $this->assertNotContains($transport, $payment->getTransports());
            }
            ```
    - fix annotations or your extended code to apply new return value of entities where `ArrayCollection` was removed and replaced by an array, eg. in:
        - `Shopsys\FrameworkBundle\Model\Order\Order::getItems`
        - `Shopsys\FrameworkBundle\Model\Payment\Payment::getPrices`
        - `Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory:getCategories`
    - we recommend encapsulating collections similarly in your own custom entities as well
- update your `OrderDataFixture` and `UserDataFixture` to create new users and orders in last two weeks instead of one ([#1147](https://github.com/shopsys/shopsys/pull/1147))
    - this is done by changing all occurrences of `$this->faker->dateTimeBetween('-1 week', 'now');` by `$this->faker->dateTimeBetween('-2 week', 'now');`
- upgrade `commerceguys/intl` to `^1.0.0` version ([#1192](https://github.com/shopsys/shopsys/pull/1192))
    - in your `composer.json`, change the dependency:
        ```diff
        - "commerceguys/intl": "0.7.4",
        + "commerceguys/intl": "^1.0.0",
        ```
    - `IntlCurrencyRepository::get()` and `::getAll()` methods no longer accept `$fallbackLocale` as the a parameter
        - you can set the parameter using the class constructor if necessary
    - `IntlCurrencyRepository::isSupportedCurrency()` is now strictly type hinted
    - protected `PriceExtension::getNumberFormatter()` is renamed to `getCurrencyFormatter()` and returns an instance of `CommerceGuys\Intl\Formatter\CurrencyFormatter` now
        - you need to change your usages accordingly
- get rid of not needed deprecations and BC-promise implementation from 7.x version ([#1193](https://github.com/shopsys/shopsys/pull/1193))
    - remove registration of `productCategoryFilter` filter from `Shopsys\ShopBundle\Model\AdvancedSearch\ProductAdvancedSearchConfig`, `services.yml` and `services_test.yml`
        - in the case, the class contains custom filters, move the filter into the `parent::__construct` as the last parameter
    - check whether all deprecated methods from multiple cron commands implementation are replaced with the new ones based on ([#817](https://github.com/shopsys/shopsys/pull/817))
    - check whether all deprecated methods and configurations for redis clients and redis commands are removed and modified to use new way based on ([#1161](https://github.com/shopsys/shopsys/pull/1161)) and ([#886](https://github.com/shopsys/shopsys/pull/886))
        - remove registration of `Shopsys\FrameworkBundle\Command\RedisCleanCacheOldCommand: ~` from `commands.yml`
    - check whether you extended classes that constructor signature was changed and fix them
        - `Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade`
        - `Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory`
        - `Shopsys\FrameworkBundle\Model\Product\Search\Export\ProductSearchExporter`
        - `Shopsys\FrameworkBundle\Model\Product\Search\Export\ProductSearchExportStructureFacade`
        - `Shopsys\FrameworkBundle\Model\Product\ProductFacade`
        - `Shopsys\FrameworkBundle\Model\Product\ProductVariantFacade`
        - `Shopsys\FrameworkBundle\Component\Elasticsearch\ElasticsearchStructureManager`
    - check wether you have overwritten methods of `Shopsys\FrameworkBundle\Model\Product\Search\Export\ProductSearchExportWithFilterRepository` class and fix their signatures
        - `createQueryBuilder` has less parameters
        - `getProductTotalCountForDomainAndLocale` was renamed to `getProductTotalCountForDomain` and has less parameters
    - rename `ProductSearchExportRepositoryTest` into `ProductSearchExportWithFilterRepositoryTest` and [fix or replace the code](https://github.com/shopsys/shopsys/blob/v8.0.0/project-base/tests/ShopBundle/Functional/Model/Product/Search/ProductSearchExportWithFilterRepositoryTest.php)
    - check whether removed deprecated methods are not used or overriden anymore
        - `Shopsys\FrameworkBundle\Form\Admin\Country\CountryFormType::validateUniqueCode`
        - `Shopsys\FrameworkBundle\Model\Pricing\PriceCalculation::getVatCoefficientByPercent`
        - `Shopsys\FrameworkBundle\Component\Elasticsearch\ElasticsearchStructureManager::getIndexName`
        - `Shopsys\FrameworkBundle\Component\Elasticsearch\ElasticsearchStructureManager::deleteIndex`
        - `Shopsys\FrameworkBundle\Component\Elasticsearch\ElasticsearchStructureManager::getConfig`
        - `Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchRepository::getIndexName`
    - update `tests/ShopBundle/Functional/Model/Cart/CartMigrationFacadeTest.php`
        ```diff
        -use Shopsys\FrameworkBundle\Model\Cart\Item\CartItemFactory;
        +use Shopsys\FrameworkBundle\Model\Cart\Item\CartItemFactoryInterface;
        -        $cartItemFactory = $this->getContainer()->get(CartItemFactory::class);
        +        $cartItemFactory = $this->getContainer()->get(CartItemFactoryInterface::class);
        ```
    - replace the use of method `createFromData` with `createFromIdAndName` method of `Shopsys\FrameworkBundle\Component\Router\FriendlyUrl` class or its descendants
        - remove extended internal method `createFriendlyUrlData` from descendent classes of
            - `Shopsys\FrameworkBundle\Model\Article\ArticleDetailFriendlyUrlDataProvider`
            - `Shopsys\FrameworkBundle\Model\Product\Brand\BrandDetailFriendlyUrlDataProvider`
            - `Shopsys\FrameworkBundle\Model\Product\ProductDetailFriendlyUrlDataProvider`
            - `Shopsys\FrameworkBundle\Model\Product\ProductListFriendlyUrlDataProvider`
    - replace the use of `Advert::POSITION_*` constants with their values, for instance in `Shopsys\ShopBundle\DataFixtures\Demo\AdvertDataFixture`
        ```php
        POSITION_HEADER => 'header'
        POSITION_FOOTER => 'footer'
        POSITION_PRODUCT_LIST => 'productList'
        POSITION_LEFT_SIDEBAR => 'leftSidebar'
        ```
    - remove the use of `is_plugin_data_group` attribute in form extensions or customized twig templates, the functionality was also removed from `form_row` block in `@FrameworkBundle/src/Resources/views/Admin/Form/theme.html.twig`
- follow instructions in [the separate article](/docs/upgrade/upgrade-instructions-for-read-model-for-product-lists-from-elasticsearch.md) to use Elasticsearch to get data into read model ([#1096](https://github.com/shopsys/shopsys/pull/1096))
- fix `shopsys.domain_images_url_prefix` parameter in your `paths.yml` file to properly load domain icons in images data fixtures ([#1183](https://github.com/shopsys/shopsys/pull/1183))
    ```diff
    -   shopsys.domain_images_url_prefix: '/%shopsys.content_dir_name%/admin/images/domain'
    +   shopsys.domain_images_url_prefix: '/%shopsys.content_dir_name%/admin/images/domain/'
    ```
- update your usage of `OrderItemsType` and Twig macros from `@ShopsysFramework/Admin/Content/Order/orderItem.html.twig` ([#1229](https://github.com/shopsys/shopsys/pull/1229))
    - if you haven't customized Twig templates for editing orders in admin or used `OrderItemsType` directly you don't have to do anything
    - change your usage after the macro signatures were modified (unused parameters were removed):
        ```diff
        - {% macro orderItem(orderItemForm, orderItemId, orderItemTotalPricesById, currency, productItem) %}
        + {% macro orderItem(orderItemForm, orderItemId, productItem) %}
        ```
        ```diff
        - {% macro orderTransport(orderTransportForm, order, transportPricesWithVatByTransportId, transportVatPercentsByTransportId) %}
        + {% macro orderTransport(orderTransportForm, transportPricesWithVatByTransportId, transportVatPercentsByTransportId) %}
        ```
        ```diff
        - {% macro orderPayment(orderPaymentForm, order, paymentPricesWithVatByPaymentId, paymentVatPercentsByPaymentId) %}
        + {% macro orderPayment(orderPaymentForm, paymentPricesWithVatByPaymentId, paymentVatPercentsByPaymentId) %}
        ```
        ```diff
        - {% macro priceWithVatWidget(priceWithVatForm, currencySymbol) %}
        + {% macro priceWithVatWidget(priceWithVatForm) %}
        ```
        ```diff
        - {% macro calculablePriceWidget(calculablePriceForm, currencySymbol) %}
        + {% macro calculablePriceWidget(calculablePriceForm) %}
        ```
    - constructor of `OrderItemsType` no longer accepts `OrderItemPriceCalculation` as third parameter
        - please change your usage accordingly if you extended this class or call the constructor directly
    - if you have overridden `{% block order_items_widget %}` you don't have the variable `orderItemTotalPricesById` defined in the block anymore
        - you can use the totals defined in `OrderItemData::$totalPriceWithVat` and `OrderItemData::$totalPriceWithoutVat` instead
- clean your project repository after administration image assets were moved from `shopsys/project-base` into `shopsys/framework` repository ([#1243](https://github.com/shopsys/shopsys/pull/1243))
    - remove `bg/`, `flags/`, `logo.svg`, and `preloader.gif` from `web/assets/admin/images/`
    - if you need to use these assets yourself, you can find them in `web/assets/bundles/shopsysframework/` (they are copied using `php phing assets` which is a part of standard build targets)
- use new administration selectboxes, checkboxes and radiobuttons ([#1241](https://github.com/shopsys/shopsys/pull/1241))
    - if you have added javascripts in administration relying on `selectize.js` rewrite it to use `Select2` instead
        - the new library has a wider range of functionality, it's now used across all `select` elements, and it's easier to use than `selectize.js`
        - see [official documentation of Select2](https://select2.org/)
    - if you render checkboxes or radiobuttons in your own admin templates add a CSS class and a `span` and wrap it in `label` for it to be displayed nicely
        ```diff
        - <input type="checkbox" class="my-class" />
        + <label>
        +     <input type="checkbox" class="my-class css-checkbox" />
        +     <span class="css-checkbox__image"></span>
        + </label>
        ```
        ```diff
        - <input type="radio" class="my-class" />
        + <label>
        +     <input type="radio" class="my-class css-radio" />
        +     <span class="css-radio__image"></span>
        + </label>
        ```
- update your administration acceptance tests after design face-lift ([#1245](https://github.com/shopsys/shopsys/pull/1245))
    - you don't have to do anything if you haven't modified the admin acceptance tests or written your own scenarios
        - implementing the `ActorInterface` (see below) is still recommended in the long-run
    - implement `ActorInterface` in your `AcceptanceTesterClass`
        ```diff
          use Facebook\WebDriver\Remote\RemoteWebDriver;
        + use Tests\FrameworkBundle\Test\Codeception\ActorInterface;
          use Tests\ShopBundle\Test\Codeception\_generated\AcceptanceTesterActions;
        ```
        ```diff
           */
        - class AcceptanceTester extends Actor
        + class AcceptanceTester extends Actor implements ActorInterface
          {
        ```
    - use helper classes `AdminCheckbox` and `AdminRadiobutton` instead of directly manipulating `input[type="checkbox"]` and `input[type="radio"]` elements in your administration acceptance tests
        - when you need to work with a particular input, create an instance of the appropriate class via static method `createByCss()` and use its methods to manipulate the input or assert its values
        - run `php phing tests-acceptance` to see that your acceptance test pass

### Configuration
- simplify local configuration ([#1004](https://github.com/shopsys/shopsys/pull/1004))
    - update `app/config/packages/shopsys_shop.yml`
        ```diff
        router:
        -   locale_router_filepaths:
        -       cs: '%shopsys.root_dir%/src/Shopsys/ShopBundle/Resources/config/routing_front_cs.yml'
        -       en: '%shopsys.root_dir%/src/Shopsys/ShopBundle/Resources/config/routing_front_en.yml'
        +   locale_router_filepath_mask: '%shopsys.root_dir%/src/Shopsys/ShopBundle/Resources/config/routing_front_*.yml'
        ```
    - update `app/config/packages/shopsys_shop.yml`
        ```diff
        ->arrayNode('router')
            ->children()
        -       ->arrayNode('locale_router_filepaths')
        -           ->defaultValue([])
        -           ->prototype('scalar')
        +       ->scalarNode('locale_router_filepath_mask')
        +       ->end()
        +       ->scalarNode('friendly_url_router_filepath')
                ->end()
        -   ->end()
        -   ->scalarNode('friendly_url_router_filepath')
            ->end()
        ```
    - update `src/Shopsys/ShopBundle/DependencyInjection/ShopsysShopExtension.php`
        ```diff
        - $container->setParameter('shopsys.router.locale_router_filepaths', $config['router']['locale_router_filepaths']);
        + $container->setParameter('shopsys.router.locale_router_filepath_mask', $config['router']['locale_router_filepath_mask']);
        ```
- add the installation of the useful tools to your `docker/php-fpm/Dockerfile` ([#1239](https://github.com/shopsys/shopsys/pull/1239))
    ```diff
    libpq-dev \
    + vim \
    + nano \
    + mc \
    + htop \
    ```

### Tools
- get rid of not needed deprecations and BC-promise implementation from 7.x version
    - check and get rid of the use of all removed deprecated phing targets from [v7.3.0 release](./UPGRADE-unreleased.md#tools)

## [shopsys/coding-standards]
- run `php phing standards-fix` to fix code style as we now disallow Yoda style for comparison in the Shopsys Framework coding standards ([#1209](https://github.com/shopsys/shopsys/pull/1209))

[shopsys/framework]: https://github.com/shopsys/framework
[shopsys/coding-standards]: https://github.com/shopsys/coding-standards
