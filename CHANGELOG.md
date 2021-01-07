# Changelog
All notable changes, that change in some way the behavior of any of our packages that are maintained by monorepo repository.

There is a list of all the repositories maintained by monorepo:

* [shopsys/framework]
* [shopsys/project-base]
* [shopsys/shopsys]
* [shopsys/coding-standards]
* [shopsys/form-types-bundle]
* [shopsys/http-smoke-testing]
* [shopsys/migrations]
* [shopsys/monorepo-tools]
* [shopsys/plugin-interface]
* [shopsys/product-feed-google]
* [shopsys/product-feed-heureka]
* [shopsys/product-feed-heureka-delivery]
* [shopsys/product-feed-zbozi]
* [shopsys/google-cloud-bundle]
* [shopsys/read-model]
* [shopsys/backend-api]
* [shopsys/frontend-api]

Packages are formatted by release version. You can see all the changes done to package that you carry about with this tree.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html) as explained in the [Backward Compatibility Promise](https://docs.shopsys.com/en/latest/contributing/backward-compatibility-promise/).

The changelog is generated during the release process using [ChangelogLinker](https://github.com/symplify/changeloglinker) since `v7.0.0-alpha6` release.

<!-- changelog-linker -->

## [v9.1.0](https://github.com/shopsys/shopsys/compare/v9.0.4...v9.1.0) - 2021-01-06

### [shopsys/framework]

#### Added

- [#1951] new phing target for changing environment

#### Changed

- [#1933] optimization of calling replacing extended entities in queries, Thanks to [@pk16011990]
- [#1994] added link into breadcrumb item in admin if uri is not null
- [#2031] deprecated notice is triggered when deprecated method is called
- [#2070] do not use private in constraints, Thanks to [@pk16011990]
- [#2079] throwable is now available in logger context when a cron fails on error
- [#2080] optimization: flush the whole identity map when an order is created
- [#2084] do not run checks for cart modifications on cart delete
- [#2118] ConstantVisibilityRequiredSniff and ForceLateStaticBindingForProtectedConstantsSniff are ignored for sources folders only in project-base
- [#2133] enabled caching for DomainRouter and LocalizedRouter, Thanks to [@pk16011990]
- [#2148] use DBAL native queries for DML instead of ORM Native SQL
- [#2153] refactoring of NormalizeUrlTrailingSlashSubscriber

#### Fixed

- [#1760] added array_key_exists condition into to NotNullableColumnsFinder method
- [#1901] fixed AdministratorFacade::changePassword() method
- [#1952] JS validation: fix creating of prototype of compound item in collection, Thanks to [@pk16011990]
- [#2064] fixed currencies menu text
- [#2083] fixed checking of empty settings values
- [#2091] fixed rendering currency symbol in admin grid
- [#2095] wrong annotation of return type in getFullPathsIndexedByIdsForDomain, Thanks to [@CarabineSK]
- [#2142] fixed copying images when creating variants

#### Removed

- [#2020] removed SliderFormType excessive ID constraint, Thanks to [@milanStano]

### [shopsys/frontend-api]

#### Added

- [#1891] added support for changing personal data and password to frontend API
- [#1960] added resolvers for orders
- [#1996] added articles resolver
- [#2002] added special article resolvers
- [#2047] added brands to frontend API
- [#2057] added missing data to Product frontend API type
- [#2066] added promoted products
- [#2067] added seo information to FEAPI
- [#2068] added resolver for adverts into FEAPI
- [#2100] added support for customer user registration to Frontend API
- [#2110] FE API ordering products
- [#2119] added support for subscribing for e-mail newsletter to FE API
- [#2150] added support to find entity by slug into FEAPI
- [#2163] added search for products and categories in frontend API

#### Changed

- [#2019] made functional tests independent on domain locales
- [#2127] string inputs are always trimmed
- [#2140] removed unnecessary UUID validations from resolvers
- [#2151] FE API returns visible category by uuid
- [#2154] removed deprecated use of time as integer in IAT and EXP claims
- [#2159] rename Mutation to MutationDecorator, Thanks to [@stanoMilan]

#### Fixed

- [#1912] fixed ChangePasswordTest for FE API
- [#2158] fixed wrong annotations of ParameterWithValues

### [shopsys/shopsys]

#### Added

- [#1894] added psr/event-dispatcher in order to prevent phpstan to repport missing class errors
- [#1961] added phpstan-symfony extension
- [#1989] required new line at the end of files
- [#2074] added product seo informations into elasticsearch
- [#2141] added total count to frontend API connections

#### Changed

- [#1922] raised phpstan analyse to level 5
- [#1957] update tests to use tests container to decrease amount of services defined in `services_test.yaml`
- [#1976] moved setting common entity data to the method
- [#1999] ReadModel for product detail
- [#2025] optimized monorepo docker files
- [#2024] moved npm-global directory to project so it is included in volume
- [#2032] remove FE API only dependencies from framework
- [#2035] added more coding standards
- [#2037] changed default locale in test to domain locale
- [#2040] unified using ENVs
- [#2049] autocompletion for Phing targets
- [#2052] do not require short list syntax
- [#2055] made parameters overridable by ENV variables
- [#2075] made frontend API tests to be currency independent
- [#2077] removed merging common args monorepo docker-compose
- [#2086] allow placing scripts in administration after content
- [#2090] ProductDetailView provided by elasticsearch
- [#2107] move doctrine metadata and annotations cache to file instead of redis, Thanks to [@henzigo]
- [#2109] dropped support for PHP versions lower  than 7.4.1
- [#2117] new application environment for running acceptance tests
- [#2123] accessories is provided by elasticsearch for ListedProductView
- [#2131] single product is now loaded from Elasticsearch in FE API
- [#2132] maintainable frontend API apiary blueprint
- [#2146] cache friendly url slugs for generator into redis
- [#2155] excluded not commited files from yaml standards checks
- [#2156] product filter in frontend API

#### Fixed

- [#1899] fixed not working upload of files in wysiwyg editor, Thanks to [@RostislavKreisinger]
- [#2051] fixed wrong generated demodata for different number of domains than 2

### [shopsys/project-base]

#### Changed

- [#1879] load javascripts after content is loaded, Thanks to [@henzigo]
- [#1913] more reliable FE API tests
- [#1990] added condition for get accessories if module is enabled only
- [#2099] improved acceptance tests
- [#2103] tail logs unbuffered, Thanks to [@henzigo]
- [#2104] disable javascript validation for product filter form, Thanks to [@henzigo]
- [#2113] enabled logging in tests
- [#2120] added email length validation in SubscriptionFormType
- [#2145] add ACCEPTANCE file to .gitignore

#### Fixed

- [#1974] fixed path to tests in phpstan.neon
- [#2023] filterFormMacro.html.twig: display "+" only if counts > 0
- [#2094] fixed standards on new release of FriendsOfPHP/PHP-CS-Fixer
- [#2096] fix cleaning of old redis cache

#### Removed

- [#2089] removed hirak/prestissimo from build

### [shopsys/read-model]

#### Changed

- [#2126] do not call unnecessary elasticsearch queries, Thanks to [@henzigo]

## [v9.0.4](https://github.com/shopsys/shopsys/compare/v9.0.3...v9.0.4) - 2020-11-29

### [shopsys/project-base]

#### Fixed

- [#2135] locked composer to version 1.x for 9.0 branch

## [v9.0.3](https://github.com/shopsys/shopsys/compare/v9.0.2...v9.0.3) - 2020-09-30

### [shopsys/framework]

#### Changed

- [#2036] move creation of FilterQuery from facade to factory

#### Fixed

- [#2050] fixed loading of multi design templates

### [shopsys/frontend-api]

#### Changed

- [#2046] pagination now works with right number of products
- [#2043] frontend API now correctly uses methods from frontend API facades instead of framework ones

### [shopsys/shopsys]

- [#2045] frontend API tests are always run on CI in monorepo
- [#2041] Moved methods used only by FE API from framework facades to FE API facades
- [#2038] required symfony/http-kernel in 4.4.13 or higher due to security issue

#### Fixed

- [#2026] fixed import path in ecs configurations

## [v9.0.2](https://github.com/shopsys/shopsys/compare/v9.0.1...v9.0.2) - 2020-09-01

### [shopsys/framework]

#### Changed

- [#1823] remove final from relevant classes, Thanks to [@pk16011990]
- [#1941] better extensibility of article form type
- [#1972] fixed removing friendly urls
- [#1977] remove customer and his addresses with customer user
- [#2006] sellingTo is now set with the end of day time

#### Fixed

- [#1750] Product::setProductCategoryDomains() fix
- [#1932] updated AdministratorFrontSecurityFacade::isAdministratorLogged() to work well if used on frontend, Thanks to [@pk16011990]
- [#1956] IndexExportedEvent is called after elasticsearch export cron module finished
- [#1963] fixed multiple binding of product picker events
- [#1966] fixed clearing form options in product picker
- [#1967] select distinct product for vat replace, Thanks to [@mposchl]
- [#1971] fixed joining the same table with different alias
- [#1979] spinbox plus now works correctly with min value
- [#1982] categories in administration are now loaded using admin locale
- [#1986] design - admin - title button wrap bar only if contains buttons
- [#1987] design - admin - domain switcher for more domains
- [#1988] fixed stylelint phing targets
- [#1998] fixed delayed javascript validators
- [#2001] AbstractFileUploadType: remove fileConstraints class property
- [#2004] remove space in error page filename, Thanks to [@pk16011990]
- [#2009] test environment is no longer wrongly detected as prod
- [#2010] cart items are now correctly deleted when cart is deleted

### [shopsys/project-base]

#### Changed

- [#1918] generate images_id_seq in data fixtures automatically, Thanks to [@pk16011990]
- [#1937] call static method as static, Thanks to [@pk16011990]
- [#1938] removed unnecessary else conditions, Thanks to [@pk16011990]
- [#1939] use __DIR__ instead of dirname(__FILE__), Thanks to [@pk16011990]

#### Fixed

- [#1970] fixed displaying errors in popup window
- [#1995] design - in popup window with html conntent - call registerNewContent…

#### Removed

- [#1940] removed unused route /contactForm/, Thanks to [@pk16011990]

### [shopsys/shopsys]

#### Added

- [#1944] added note about possibility of credentials caching while releasing
- [#1949] releaser added warning when merging RC branch

#### Changed

- [#1777] ImageConfig accepts extended entities, Thanks to [@pk16011990]
- [#1946] updated releaser list of excluded packages
- [#1955] temporary fileupload is now uploaded to abstract filesystem
- [#1985] redis build-version now includes application environment
- [#2000] timezone for running crons

#### Fixed

- [#1947] fixed checking builds in after-release stage
- [#1948] releaser fixed npm command
- [#1964] fixed filtering of products in frontend API

## [v9.0.1](https://github.com/shopsys/shopsys/compare/v9.0.0...v9.0.1) - 2020-07-14

### [shopsys/http-smoke-testing]

#### Added

- [#1578] added Symfony 5 support, Thanks to [@MattCzerner]

### [shopsys/framework]

#### Changed

- [#1660] unified using form data if it is object, Thanks to [@pk16011990]
- [#1674] unified product variant form, Thanks to [@pk16011990]
- [#1692] build-demo phing target: create elasticsearch index and export data before generating error pages
- [#1708] CKEditor is now rendered right in full width
- [#1874] redirect URLs with a trailing slash (code 301), Thanks to [@RostislavKreisinger]

#### Fixed

- [#1619] production protection phing target now returns 1 on error
- [#1672] edit transport and payment names in order items after order is changed, Thanks to [@pk16011990]
- [#1719] ProductExportRepository: prevent Undefined offset notice
- [#1723] default product data now have availability set
- [#1794] fix condition for plus numbers of parameters in the product filter, Thanks to [@RostislavKreisinger]
- [#1825] Locale flags now use asset packages to retrieve its URL, Thanks to [@pk16011990]
- [#1857] added missing exception message about category was not found for product and domain, Thanks to [@sspooky13]
- [#1885] fixed calculate availability for new product
- [#1897] updated Currency::setRoundingType() for easier extension in project-base
- [#1902] fixed undefined property userData, Thanks to [@RostislavKreisinger]
- [#1904] select2 is no longer hidden under bottom bar in administration
- [#1908] BestsellingProductFormType now uses constant for rendering product list
- [#1909] fixed error 500 during logout when the user is already logged out
- [#1915] datepicker.js assigns options per instance, Thanks to [@milanStano]
- [#1916] fixed throwing OrderItemNotFoundException
- [#1919] fixed saving thumbnails in elfinder
- [#1920] fixed renaming files in elfinder
- [#1923] prevent duplicating of admin side menu
- [#1927] fixed validation of form after browser autofill

### [shopsys/project-base]

#### Added

- [#771] added variant availabilities to product detail
- [#1861] added extra_host for elasticsearch in production docker-compose.yml file, Thanks to [@ondrejbohac]

#### Changed

- [#1644] order can now be completed when successful flash message exists, Thanks to [@pk16011990]
- [#1810] optimization of ProductDataFixture.php, Thanks to [@RostislavKreisinger]
- [#1842] automatically delete sessions after 7 days of user inactivity, Thanks to [@stanoMilan]

#### Fixed

- [#1582] CartCest now uses correct translations
- [#1827] container log now catches all lines from first command output, Thanks to [@pk16011990]
- [#1906] windowForm: fix initialization of JS validation
- [#1926] fixed wrong url of freshly uploaded files in wysiwyg

### [shopsys/read-model]

#### Changed

- [#1730] ProductActionView creation: use calculatedSellingDenied

### [shopsys/shopsys]

#### Added

- [#1858] added new release worker with release new node module package

#### Changed

- [#1338] restrict access to currency settings to superadmin, Thanks to [@PetrHeinz]
- [#1862] upgrade dependency on symplify/easy-coding-standard, Thanks to [@spagr]

#### Fixed

- [#1880] fixed JavaScript translations generation
- [#1892] fixed Window.js - default options are not overridden by jQuery.extend function anymore
- [#1905] new entity extensions are now registered immediately

## [v8.1.2](https://github.com/shopsys/shopsys/compare/v8.1.1...v8.1.2) - 2020-06-02

### [shopsys/framework]

#### Fixed

- [#1868] fixed unique email validation in administration
- [#1865] updated snc/redis-bundle version
- [#1790] fp/jsformvalidator-bundle is pinned to minor 1.5

### [shopsys/shopsys]

#### Added

- [#1864] added double submit protection

#### Fixed

- [#1391] fix issues with QueryBuilderExtender's method AddOrExtendJoin, Thanks to [@pk16011990]

### [shopsys/product-feed-heureka]

- [#1670] Preserve relation between categories and Heureka categories, Thanks to [@pk16011990]

## [v7.3.4](https://github.com/shopsys/shopsys/compare/v7.3.3...v7.3.4) - 2020-05-29

### [shopsys/framework]

#### Fixed

- [#1868] fixed unique email validation in administration
- [#1865] updated snc/redis-bundle version
- [#1790] fp/jsformvalidator-bundle is pinned to minor 1.5

### [shopsys/shopsys]

#### Added

- [#1864] added double submit protection

#### Fixed

- [#1391] fix issues with QueryBuilderExtender's method AddOrExtendJoin, Thanks to [@pk16011990]

### [shopsys/product-feed-heureka]

- [#1670] Preserve relation between categories and Heureka categories, Thanks to [@pk16011990]

## [v9.0.0](https://github.com/shopsys/shopsys/compare/v8.1.1...v9.0.0) - 2020-05-25

### [shopsys/backend-api]

#### Fixed

- [#1671] prevent forgotten file after install and uninstall backend api in monorepo + fix patch, Thanks to [@pk16011990]

### [shopsys/framework]

#### Added

- [#1696] added fallback to migrate previous elasticsearch structure to current one
- [#1728] file upload: add NotBlank contraints for "currentFilenamesIndexedById" and "uploadedFilenames" fields
- [#1756] added validation of images extensions so only jpg, jpeg, gif and png are allowed
- [#1839] added onDelete action on customer user refresh token chain entity

#### Changed

- [#1844] domain-info-load phing target now does not include errors in output properties
- [#1669] Run all recalculators during deploy only if domain is created, Thanks to [@pk16011990]
- [#1772] validation errors for the uploadedFilenames are displayed
- [#1791] renamed rounding related menu entries
- [#1770] main_filesystem is now public service for elFinder to work properly
- [#1754] hide "Uploading..." flash message on error
- [#1752] reconfigured NotLogFakeHttpExceptionsErrorListener service
- [#1748] MessageFactoryInterface:createMessage(): rename $personalData parameter
- [#1803] refactored SelectToggle component
- [#1650] use protected properties in Transformers and AdvancedSearch, Thanks to [@pk16011990]
- [#1806] Responsive administration
- [#1652] Padding categories by level in advanced search, Thanks to [@pk16011990]
- [#1653] higher priority for static routes, lower for dynamic routes, Thanks to [@pk16011990]
- [#1634] admin issues before v 9 release
- [#1664] update fp/jsFormValidator bundle
- [#1846] Phing: domain info load target now does not include errors in output properties
- [#1675] unified rendering of currency and symbolAfterInput in forms
- [#1830] RegisterExtendedEntitiesCompilerPass: catch MappingException
- [#1700] customer structure refactoring
- [#1514] refresh administrator roles after edit own profile
- [#1720] used methods for providing config definition classes
- [#1828] email templates are now editable on separate page

#### Fixed

- [#1712] fixed wrong migration on database with data, Thanks to [@pk16011990]
- [#1767] fixed version of elasticsearch in composer.json
- [#1673] fixed editing of SliderItem's domain id, Thanks to [@pk16011990]
- [#1666] fixed multiple binding js-product-visibility, Thanks to [@pk16011990]
- [#1722] deliveryAddressChoiceFields.html.twig: fix risk of getter call on null
- [#1637] fixed contact form text db migration
- [#1761] fixed parameter name in remove item from order confirm window
- [#1703] fixed code sent to TransformationFailedException
- [#1651] fixed call sleep() on null on sleep cron module between two feeds, Thanks to [@pk16011990]
- [#1784] fixed removing product in product picker window
- [#1776] fixed ErrorExtractor::getAllErrorsAsArray()
- [#1789] fixed Authenticator definition
- [#1620] fixed wrong variable name in customer detail template
- [#1795] fixed ordering of slider items in administration
- [#1632] fixed window fix bar in development mode
- [#1787] ArticleFormType: fix wrong usage of null coalescing operator

#### Removed

- [#1799] subscription form removed from error pages
- [#1685] removed unused dependencies

### [shopsys/product-feed-heureka]

#### Changed

- [#1740] rename method "convertToShopEntities" to "convertToHeurekaCategoriesData"

### [shopsys/project-base]

#### Added

- [#1811] import jquery-ui-touch-punch and add safari support into babel
- [#1804] added plugin-proposal-object-rest-spread as babel plugin

#### Changed

- [#1641] Prepared folder for overwriting templates, Thanks to [@pk16011990]
- [#1640] compliance with the principle of encapsulation, Thanks to [@pk16011990]
- [#1782] strict comparison for categories in Category/panel.html.twig template, Thanks to [@mposchl]
- [#1677] sellingFrom moved to the future for not yet sellable product in data fixtures
- [#1820] improved clearing cache behavior
- [#1565] cart detail on hover
- [#1587] extension of user form is now working correctly

#### Fixed

- [#1797] fixed customer section validation
- [#1733] fixed phpstan.neon
- [#1630] fixed not working popup window on single image, Thanks to [@RostislavKreisinger]
- [#1655] fixed path resolving for domain icons
- [#1745] fixed dump function, Thanks to [@henzigo]

#### Removed

- [#1638] removed empty twig template from ShopBundle

### [shopsys/shopsys]

#### Added

- [#1545] added webpack encore
- [#1602] Upgrade to Elasticsearch 7, Thanks to [@pk16011990]
- [#1676] added change and remove methods to js register class
- [#1800] added double submit protection on all forms
- [#1757] added create order mutation to frontend API
- [#1731] added user login to frontend API
- [#1807] added livereload plugin for webpack
- [#1726] added transport and payment to frontend API
- [#1788] added logout to frontend API
- [#1736] added refresh tokens to frontend API
- [#1742] added signed tokens by private key to frontend API

#### Changed

- [#1601] Update to Postgres 12, Thanks to [@pk16011990]
- [#1636] partial product export to Elastic
- [#1659] do not change product availability to default when availability cannot be calculated immediately, Thanks to [@pk16011990]
- [#1622] easier elasticsearch extensibility
- [#1606] Redis upgrade
- [#1663] automatically generated entity extension map, Thanks to [@pk16011990]
- [#1645] export js translations
- [#1542] switch domain default currencies
- [#1635] multiple delivery addresses
- [#1478] bad constraint message for minimum password length, Thanks to [@sspooky13]
- [#1681] renamed js files
- [#1829] some little seo optimalizations
- [#1826] PHPStan limit maximum number of processes
- [#1818] updated fp/jsFormValidator bundle
- [#1814] unified config files extension to yaml
- [#1774] Creating instance of data object with DataFactory::createInstance() method
- [#1764] doctrine money type now requires sql comment hint
- [#1755] some little adjustments in js and webpack
- [#1853] updated some composer dependencies
- [#1737] Upgrade PHP to 7.4, Thanks to [@henzigo]
- [#1725] Webpack for less and svg
- [#1717] updated adminer
- [#1704] update symfony to version 4.4
- [#1739] cron definitions moved to project-base

#### Fixed

- [#1840] fixed categories sorting in admin
- [#1822] fixed parameters validation
- [#1732] jms/translation-bundle fixed to version 1.4.4
- [#1682] fixed Payment and Transport tests for single domain
- [#1766] fixed common styles in webpack config
- [#1819] better removed subscription form on error pages

#### Removed

- [#1817] removed webserver-bundle in favor of Symfony local server
- [#1801] removed deprecation before v9.0.0 release
- [#1779] removed unused imports

## [v8.1.1](https://github.com/shopsys/shopsys/compare/v8.1.0...v8.1.1) - 2020-01-17


### [shopsys/project-base]

#### Fixed

- [d29f6dd](https://github.com/shopsys/shopsys/commit/d29f6dd55591178001a58b6c36dd10010449a0d0) Lock NPM version to 6.13.2 due to invalid bin entry caused by newer versions of NPM

## [v8.1.0](https://github.com/shopsys/shopsys/compare/v8.0.0...v8.1.0) - 2020-01-16

### [shopsys/framework]

#### Changed

- [#1314] CronModuleExecutor is now service in DIC, Thanks to [@pk16011990]
- [#1360] disallow administrator login with default credentials in production environment
- [#1361] use DIC configuration instead of factory class to create Redis caches
- [#1476] unify working with images
- [#1487] improve working with currency rounding and fraction digits

#### Fixed

- [#1340] replace constants from other classes by literal values, Thanks to [@sspooky13]
- [#1364] ProductCodeController now autowires PromoCodeDataFactoryInterface instead of PromoCodeDataFactory
- [#1441] fixed image drag&drop in product gallery
- [#1446] refactoring error messages at admin order detail form
- [#1452] sorting arrows does not change column width in administration
- [#1517] added missing translations for promo codes
- [#1560] improved appearance of error messages with long words

### [shopsys/http-smoke-testing]

- [#1423] HttpSmokeTestCase uses constants for kernel options
- [#1451] Add annotation based configuration for smoke tests, Thanks to [@mariusbuescher]

### [shopsys/project-base]

#### Added

- [#1342] added travis build for automated testing of installation script to project-base
- [#1330] PHP-FPM pool configuration now can be changed
- [#1465] Add graphic checkboxes and radiobuttons
- [#1472] added possibility to override admin styles
- [#1483] added support for image and iframe lazy loading

#### Changed

- [#1384] simplified filling variables in Kubernetes manifests
- [#1464] webline layout

#### Fixed

- [#1285] redirect logged user from registration page to homepage, Thanks to [@pk16011990]
- [#1313] force locale for number formatting, Thanks to [@pk16011990]
- [#1336] fix typo (submited -> submitted), Thanks to [@sspooky13]
- [#1346] fix wrong annotation in Administrator
- [#1381] resolved all the errors reported by PHPStan level 4
- [#1479] fix functional tests for single domain
- [#1524] preserve custom checkbox CSS classes
- [#1561] fixed type of error flash message in template

### [shopsys/shopsys]

#### Added

- [#1343] added support to display dates in different timezone
- [#1393] added error ID to 500 error page
- [#1485] added styleguide to project

#### Changed

- [#1309] domain configuration is autoloaded in build.xml
- [#1354] upgrade the Adminer Docker image to 4.7
- [#1319] make promo codes editable on separate pages
- [#1344] automated fixes and additions of annotations for extended classes
- [#1392] dependencies in tests are injected now
- [#1408] Dockerfile: stop defining default env vars
- [#1425] eased installation of project with different domains and locales settings
- [#1532] made image lazy loading configurable

#### Fixed

- [#1289] composer.json enhancements (alphabetical sorting of extensions, 4 spaces as indentation), Thanks to [@sspooky13]
- [#1506] fixed version of symfony/monolog-bundle

#### Removed

- [#1276] docker-compose-win.yml.dist: remove php.ini volume mount
- [#1416] remove docker-sync version from install guide, Thanks to [@sspooky13]
- [#1461] remove old IE support

## [v8.0.0](https://github.com/shopsys/shopsys/compare/v7.3.1...v8.0.0) - 2019-07-30

### [shopsys/framework]

#### Added

- [#1194] admin: added quick menu for product detail
- [#1099] administration: quicksearch for products via dashboard
- [#906] admin design facelift
- [#875] mail spooling

#### Changed

- [#1215] admin: display real name of logged user instead of icon
- [#1207] DailyFeedCronModule: use getter for queue to eliminate side effects in constructor, Thanks to [@hason]
- [#1229] admin: currency is displayed in the order table header instead of behind every input
- [#1197] admin: logo is now a link to dashboard
- [#1191] changing elasticsearch data structure is safe even in production environment
- [#1168] admin: product detail does not open in new window
- [#1140] Registration of FriendlyUrlDataProviders is now done via service container
- [#1044] remove unnecessary passing parameter as a reference, Thanks to [@pk16011990]
- [#918] all entity factories now use EntityNameResolver

#### Fixed

- [#1248] DailyFeedCronModule set as lazy to avoid side-effects in constructor
- [#1234] CountryFormType: removal of duplicate constraint
- [#1232] LegalConditionsFacade::findArticle() returns null when the Article doesn't exist
- [#1231] DomainController: fix non-existing route
- [#1230] admin: consistency in separators in setting form
- [#1227] AdministratorFacade: fix duplicate user name check
- [#1226] admin: new order items have "Set prices manually" unchecked by default
- [#1225] admin advanced search: reset button will not close the filter
- [#1210] fixed ordering customers by telephone in administration
- [#1204] dashboard statistics display zero trend when there are no data in previous week

#### Removed

- [#1097] end of support for helios-ag/fm-elfinder-bundle:^6.2.1

### [shopsys/project-base]

#### Changed

- [#1256] enforce strict typing in project repository
- [#1239] php-fpm dockerfile contains useful tools (vim, nano, mc, htop), Thanks to [@pk16011990]

### [shopsys/shopsys]

#### Added

- [#1240] added Phing target that checks whether there is a project-base/vendor dir
- [#1201] Start Building Your Application article
- [#1245] acceptance test helper classes for graphical inputs
- [#1181] all constants now have set their visibility
- [#1096] use ElasticSearch to get data to readmodel
- [#1004] support all languages and collations, Thanks to [@henzigo]
- [#539] standards for .yaml and yaml.dist files, Thanks to [@sspooky13]

#### Changed

- [#1243] move admin assets into [shopsys/framework] repository
- [#1192] commerceguys/intl is now upgraded to ^1.0.0
- [#1176] BreadcrumbGenerators are now automatically registered in BreadcrumbResolver
- [#1155] separated backend API from default project-base
- [#1115] Product: availability cannot be set when using stock (and vice versa)
- [#1123] entities: removal of dependencies on services
- [#1072] Migration from IvoryCKEditorBundle to FOSCKEditorBundle
- [#1047] encapsulation of collections inside of entities, Thanks to [@pk16011990]
- [#933] phing target test-db-demo now exports products into elasticsearch with different index, Thanks to [@henzigo]

#### Fixed

- [#1219] fix grammar connected with "information" word
- [#1183] fixed path for domain icons directory

#### Removed

- [#1193] remove deprecations from 7.x.x
- [#1066] drop support for PHP 7.1

### [shopsys/backend-api]

#### Added

- [#1055] API Product export
- [#1203] create, update, delete products

#### Changed

- [#1250] moved backend API Product Controller classes to subdirectory

#### Fixed

- [#1208] backend-api installer is now updated with latest changes in build.xml file

### [shopsys/coding-standards]

#### Added

- [#1255] added return type hint spacing fixer
- [#1209] Yoda style for comparison is now disallowed
- [#1010] all nullable values are now defined using nullability (?) symbol

#### Changed

- [#1254] explicit visibility is part of the general coding-standards

### [shopsys/migrations]

#### Changed

- [#1262] add missing strict types and return types into template, Thanks to [@sspooky13]

## [v7.3.3](https://github.com/shopsys/shopsys/compare/v7.3.2...v7.3.3) - 2020-02-24

### [shopsys/coding-standards]

#### Changed

- [#1373] force unix line endings, Thanks to [@machicek]
- [#1470] FunctionCallSignatureSniff.Indent is now disabled

### [shopsys/framework]

#### Fixed

- [#1462] Correctly create prices for transports and payments, Thanks to [@henzigo]
- [#1494] handle error when the last cart item has been removed
- [#1609] fixed phpstan issue with changed namespace of doctrine/persistence

#### Changed

- [#1419] increased slow log priority to prevent miscalculation request time, Thanks to [@henzigo]
- [#1475] clean redis cache via scan and unlink, Thanks to [@henzigo]
- [#1502] changed documentation link in toolbar to docs.shopsys.com

### [shopsys/project-base]

#### Fixed

- [#1551] OrderCest is now more reliable

#### Changed

- [#1389] ErrorController uses environment from DIC parameter
- [#1459] removed usage of non-existing property ProductData::$price
- [#1668] upgraded npm dependencies
- [#1679] selling from date moved to the future for not yet sellable product


### [shopsys/shopsys]

#### Changed

- [#1549] restricted doctrine/persistence to version 1.2

## [v7.3.2](https://github.com/shopsys/shopsys/compare/v7.3.1...v7.3.2) - 2019-09-17

### [shopsys/framework]

#### Added

- [#1303] composer.json: added missing PHP extension dom

#### Changed

- [#1248] DailyFeedCronModule set as lazy to avoid side-effects in constructor

#### Fixed

- [#1189] UploadedFileDeleteDoctrineListener not throw not found entity, Thanks to [@stanoMilan]
- [#1210] fixed ordering customers by telephone in administration
- [#1225] admin advanced search: reset button will not close the filter
- [#1226] admin: new order items have "Set prices manually" unchecked by default
- [#1231] DomainController: fix non-existing route
- [#1232] LegalConditionsFacade::findArticle() returns null when the Article doesn't exist
- [#1290] convert DOMAIN environment variable to integer, Thanks to [@hason]
- [#1311] AdvancedSearch respects current route on reset form, Thanks to [@pk16011990]

#### Removed

- [#1234] CountryFormType: removal of duplicate constraint

### [shopsys/project-base]

#### Changed

- [#1198] gke-cluster.tf: remove 'google_container_engine_versions' data source
- [#1273] enable entity extension from components, Thanks to [@malyMiso]

#### Fixed

- [#1414] fix set category description for first domain, Thanks to [@sspooky13]
- [#1371] brand list - brand label in two rows with label text wrapping, Thanks to [@TomasGottvald]

#### Removed

- [#1355] remove unnecessary English translations

### [shopsys/shopsys]

#### Added

- [#1240] added Phing target that checks whether there is a project-base/vendor dir
- [#1323] commit migrations-lock.yml with other lock files when releasing
- [#1403] added FAQ entry about setting hostname for SMTP container

#### Changed

- [#1244] prepare releaser for work in multiple branches
- [#1264] releaser now commits package and composer locks
- [#1362] nginx.conf: remove access_log directive and use default settings
- [#1370] releaser simplification
- [#1402] Allow to scan PRs with SonarCloud

#### Fixed

- [#1282] php-fpm Dockerfile: update repository before install `libpg-dev`, Thanks to [@pk16011990]
- [#1335] fix typos in translation messages and demo data
- [#1353] fix configuration of PhpStan
- [#1405] releaser: excluded forked shopsys/changelog-linker from travis check
- [#1406] releaser: excluded forked shopsys/monorepo-builder from travis check
- [#1410] releaser: fixed path for upgrading-monorepo file

## [v7.3.1](https://github.com/shopsys/shopsys/compare/v7.3.0...v7.3.1) - 2019-07-15

### [shopsys/framework]

#### Fixed

- [#1195] fixed memory leak in cron ProductSearchExportCronModule

### [shopsys/project-base]

#### Changed

- [#1199] PHPStan level lowered to 1

## [v7.3.0](https://github.com/shopsys/shopsys/compare/v7.2.2...v7.3.0) - 2019-07-09

### [shopsys/framework]

#### Added

- [#1053] added elasticsearch debug toolbar for dev env
- [#1153] added immediate export to elastic on product update in administration

#### Changed

- [#1025] creating a new variant no longer redirects to product detail
- [#1105] changed variant abbreviation to a translation in administration
- [#1106] configure VarDumperExtension in all environments
- [#1114] all the properties and methods in Shopsys\FrameworkBundle\Twig namespace has now protected instead of private visibility
- [#1129] changed VAT calculation - not using a rounded coefficient now
- [#1161] RedisFacade: unification of used clients

#### Fixed

- [#1092] ImageConfigDefinition bugfix - added additionalSizes under types
- [#1094] ImageFacade: use provided "type" when accessing size config
- [#1109] checking of order item type is fixed by using type getters
- [#1122] fixed memory leak on shopsys:product-search:export-products command
- [#1125] multidomain sitemaps are now properly generated
- [#1145] transport and payments are prefilled to the order only if they exists
- [#1157] product variants without a price for a pricing group are exportable to elastic
- [#1159] products with zero price are now not returned from Elasticsearch
- [#1064] fixed sending emails with attachment

#### Removed

- [#1166] removed redundant service definitions

### [shopsys/project-base]

#### Changed

- [#1049] prepared DI configuration for project-base like framework
- [#1119] prevent indexing `CustomerPassword:setNewPassword` by robots

#### Fixed

- [#1073] created an empty folder app/Resources/ for overwritten templates
- [#1126] deploy-to-google-cloud.sh: script sets correct elasticsearch image from dockerhub
- [#1175] DI parameter %build-version% is always string now

### [shopsys/shopsys]

#### Added

- [#1018] added read model for front-end product lists
- [#1133] added Elasticsearch structure migration via reindexing for easier deployment
- [#1069] added sorting by language to Elasticsearch

#### Changed

- [#1139] entity-extension.md has been updated to be more useful by new users
- [#1040] upgraded phpstan from level 0 to level 4
- [#1121] use autocomplete="new-password" attributed for password inputs
- [#1154] stop using excluded_404s in monolog config for incompatible types
- [#1162] added exceptions for constructor parameters in BC promise
- [#1164] all services injected in order to prevent BC breaks are now injected same way
- [#1068] restructuring of Phing targets
- [#1172] docker-sync now does not exclude project-base/docs in monorepo

#### Fixed

- [#1113] ProductAvailabilityCalculation: fix calculation of not-yet-persisted products
- [#1177] build.xml: build-deploy-* phing targets are fixed
- [#1180] **(BC-BREAK)** fixed wrong field in exporting products to Elasticsearch

### [shopsys/migrations]

#### Fixed

- [#1063] Configuration::shouldExecuteMigration is fixed

## [v7.2.2](https://github.com/shopsys/shopsys/compare/v7.2.1...v7.2.2) - 2019-06-18

### [shopsys/framework]

#### Fixed

- [#1120] escaping values in emails to prevent HTML injection

## [v7.2.1](https://github.com/shopsys/shopsys/compare/v7.2.0...v7.2.1) - 2019-05-29

### [shopsys/framework]

#### Fixed

- [#1054] ProductSearchCreateStructureCommand exits on error
- [#1058] CountryFormType uses NotInArray instead of Callback constraint now
- [#1074] custom extension of FlysystemVolumeDriver is loaded via files instead of classmap
- [#1083] ProductSearchExportWithFilterRepository: get product selling prices only for certain domain, Thanks to [@malyMiso]
- [#1084] CurrentPromoCodeFacadeTest: fixed call of getMockForAbstractClass

### [shopsys/project-base]

#### Fixed

- [#1041] Form::isValid() should be called after Form::isSubmitted(), Thanks to [@pk16011990]
- [#1043] fix typo in twig template, Thanks to [@pk16011990]
- [#1073] created an empty folder app/Resources/ for overwritten templates
- [#1077] ingress-nginx deployment config loads now tagged version instead of master branch version
- [#1078] initialization of php-fpm container via kubernetes is now done via www-data user
- [#1082] fixed FilterQueryTest to properly use Elasticsearch index

## [v7.2.0](https://github.com/shopsys/shopsys/compare/v7.1.0...v7.2.0) - 2019-05-15

### [shopsys/shopsys]

#### Added

- [#943] Elasticsearch filtering
- [#912] added link to elastic instruction to FAQ section
- [#960] guidelines-for-pull-request.md: added note about branch targeting
- [#959] UpdateChangelogReleaseWorker: add note about PR targets
- [#927] add missing step in upgrade instructions to v7.1.0, Thanks to [@henzigo]
- [#978] configure Travis to check standards and unit tests in monorepo, Thanks to [@henzigo]
- [#990] code quality principles article

#### Changed

- [#930] use redis for doctrine and framework cache, Thanks to [@henzigo]
- [#942] absolute paths used via local filesystem flysystem now use paths without drive letters
- [#939] tests refactoring - remove wrong annotations and use shorter way of writing return value, Thanks to [@pk16011990]
- [#904] constants are now extendable
- [#916] demonstrational extended code in project-base should have the final say over the parent method calls
- [#924] admin locale is now recommended to be changed in parameters_common.yml
- [#961] native installation guide is up-to-date and generalized
- [#894] updated list of sites in README.md
- [#893] moved link to dev workflow instruction from introduction to contribution section
- [#975] upgrading notes: "low priority" tag is not used anymore (after the stable release)

#### Fixed

- [#946] postgresql.conf is now used by Postgres
- [#947] nginx has now same limit for filesize as is set in php.ini
- [#932] fm_elfinder main filesystem volume driver
- [#993] fixed typo non-breaking space -> space
- [#967] FAQ: fix path to Product Searching via Elasticsearch
- [#1016] README.md: add a link to the Travis build badge

#### Removed

- [#1020] remove duplicate entrypoint call in production guide
- [#992] remove duplicate entrypoint call in commands in postgres container
- [#940] remove duplicate test database build in kubernetes ci script, Thanks to [@henzigo]
- [#944] removed mentions about microservices from product search export command
- [#964] obsolete note in BC promise removed

### [shopsys/coding-standards]

#### Fixed

- [#995] helper PhpToDocTypeTransformer is updated for use with new version of phpcs-fixer package
- [#1008] include config of better-phpdoc-parser in ECS config

### [shopsys/framework]

#### Added

- [#968] added x-powered-by to response headers
- [#956] fields in forms can be ordered, Thanks to [@henzigo]

#### Changed

- [#920] cron: Error page: don't log fake exception after generating an error page, Thanks to [@sspooky13]
- [#952] don't log administrator was disconnected because admin was too long inactive, Thanks to [@sspooky13]
- [#999] used ICU collations to assure correct sorting throughout OSs

#### Fixed

- [#949] fixed wrong behavior while extending ProductFormType and CategoryFormType
- [#1000] fix correct creating friendly url without indexPostfix, Thanks to [@henzigo]
- [#954] Current domain router match route for POST and ajax condition, Thanks to [@stanoMilan]
- [#958] cron works correctly with time config of "00"

### [shopsys/http-smoke-testing]

#### Changed

- [#1014] use current [shopsys/coding-standards] package

### [shopsys/project-base]

#### Added

- [#989] create configuration file for crons to project base, Thanks to [@henzigo]
- [#910] availability of docker is now checked before installation

#### Changed

- [#888] kubernetes containers accesspoints are routed via ingress
- [#928] use Redis standard prefix delimiter, Thanks to [@henzigo]
- [#1001] move database_server_version to parameters_common.yml, Thanks to [@henzigo]

#### Fixed

- [#931] fixed: translations are now extracted also from overwritten templates in `app/Resources`
- [#980] ecs-fix phing target fixes result of ecs phing target

## [v7.1.1](https://github.com/shopsys/shopsys/compare/v7.1.0...v7.1.1) - 2019-06-18

### [shopsys/framework]

#### Fixed

- [#1084] CurrentPromoCodeFacadeTest: fixed call of getMockForAbstractClass
- [#1120] escaping values in emails to prevent HTML injection

### [shopsys/shopsys]

#### Fixed

- [#993] fixed typo non-breaking space -> space

### [shopsys/coding-standards]

#### Fixed

- [#995] helper PhpToDocTypeTransformer is updated for use with new version of phpcs-fixer package
- [#1008] include config of better-phpdoc-parser in ECS config

## [v7.1.0](https://github.com/shopsys/shopsys/compare/v7.0.0...v7.1.0) - 2019-04-02

### [shopsys/shopsys]

#### Added

- [#887] added documentation about working with forms
- [#886] added version prefix to redis caches so the application can be safely deployed
- [#870] new class for representing monetary values PHASE 6
- [#864] Added grid documentation

#### Fixed

- [#891] fix error in javascript validation of ChoiceType
- [#901] consistent spelling: behavior, color
- [#889] feeds: using correct calculatedSellingDenied

#### Changed

- [#868] upgraded UPGRADE.md with experience from upgrading
- [#876] Update README.md
- [#892] form types docs tweaks
- [#808] Redesigned print page of product detail page
- [#883] remake of model documentation
- [#903] releaser tweaks
- [#895] product category filter in advanced search

### [shopsys/coding-standards]

- [#879] marked symplify/better-phpdoc-parser in version >=5.4.14 as conflicting
- [#848] sniff that forbids Doctrine Inheritance mapping

### [shopsys/framework]

#### Fixed

- [#874] fixed Article::$hidden annotation, Thanks to [@Rixafy]
- [#905] fix missing service in performance tests of feeds
- [#881] Firing opening subcategories menu animation only once
- [#846] cleaning empty Redis cache doesn't cause an error anymore

#### Changed

- [#882] Set adaptive width to selectboxes created by selectize

### shopsys/google-cloud-bundle

#### Changed

- [#878] unified shopsys requirements to dev-master

### [shopsys/project-base]

#### Fixed

- [#902] Performance\CategoryDataFixture: fix maximum count in progress bar
- [#899] fix EntityExtensionTest when extending entity related to Product
- [#885] overflow of long e-mail for unique e-mail validation

## [v7.0.1](https://github.com/shopsys/shopsys/compare/v7.0.0...v7.0.1) - 2019-06-18

### [shopsys/framework]

#### Fixed

- [#1084] CurrentPromoCodeFacadeTest: fixed call of getMockForAbstractClass
- [#1120] escaping values in emails to prevent HTML injection

### [shopsys/shopsys]

#### Fixed

- [#993] fixed typo non-breaking space -> space

### [shopsys/coding-standards]

#### Fixed

- [#995] helper PhpToDocTypeTransformer is updated for use with new version of phpcs-fixer package
- [#1008] include config of better-phpdoc-parser in ECS config

## [v7.0.0](https://github.com/shopsys/shopsys/compare/v7.0.0-beta6...v7.0.0) - 2019-02-27

### [shopsys/framework]

#### Changed

- [#817] now it is possible to run multiple cron commands

#### Fixed

- [#852] fixed empty cart when no listable product left in cart
- [#829] fixed js validation constraints
- [#869] fixed files uploading
- [#851] flysystem for ImageFacade::copyImages and FileUpload::postFlushEntity is unified

### [shopsys/project-base]

#### Added

- [#825] promocode flow in cart is covered by acceptance tests
- [#836] implemented picture element

#### Changed

- [#824] Elasticsearch contains products after "build-demo" target now, thanks to [@pejaycz]

### [shopsys/shopsys]

#### Changed

- [#845] shopsys dependencies now using caret notation
- [#830] improved documentation "How to set up domains and locales"
- [#782] changed forms in popup so they work with js validation
- [#854] data fixtures moved to project-base
- [#821] new class for representing monetary values
- [#844] naming of promo code and discount code is unified

#### Fixed
- [#779] upgrade instructions of cart as entity has been updated to be more format consistent
- [#857] link to BC promise made absolute in PR template

#### Removed

- [#853] releaser docs: removed note about confusing message

## [v7.0.0-beta6](https://github.com/shopsys/shopsys/compare/v7.0.0-beta5...v7.0.0-beta6) - 2019-02-18

### [shopsys/coding-standards]

#### Changed

- [#796] ObjectIsCreatedByFactorySniff: allow using descendant factories

#### Fixed

- [#813] fixed whitespace before method argument

### [shopsys/framework]

#### Added

- [#736] added Phing target for cleaning redis cache: clean-redis
- [#792] added ability to use distinct templates for the other domains

#### Changed

- [#715] OrderItem is now easily extendable
- [#762] unified list of countries across domains
- [#781] directories created during shopsys:create-directories command are configurable in yml configuration
- [#787] reviewed data object properties
- [#806] administration locale is now possible to set in parameters

#### Fixed

- [#732] avoid 404 in new product page on an empty shop

#### Removed

- [#793] microservices has been removed and their functionality has been moved to framework

### [shopsys/project-base]

#### Added

- [#759] created install script
- [#825] promocode flow in cart is covered by acceptance tests

#### Fixed

- [#816] warm up production cache before generating error pages
- [#809] page reloaded after promocode is set
- [#777] kubernetes uses relay setting for sending mails

### [shopsys/shopsys]

#### Added

- [#766] added support for multiple image sizes (picture element)
- [#788] added new coding standard: forbidden private visibility in namespace
- [#815] added checker to check availability of redis
- [#823] added warnings about version to installation guides
- [#763] added acknowledgement about 3-rd party software licenses
- [#780] added missing references to shopsys/google-cloud-bundle
- [#800] added backward compatibility promise
- [#730] added support for Google Cloud Storage Bucket
- [#764] explained minimal version of Docker in docs
- [#798] added minimal requirements to installation guides
- [#773] added functional documentation (variants, visibility and exclude from sale)
- [#774] added missing shopsys/google-cloud-bundle into monorepo split package list
- [#803] upgrade notes are extended and fixed

#### Changed

- [#694] updated PHP to version 7.3
- [#811] renamed docs directory "wip_glassbox" to "extensibility"
- [#782] changed forms in popup so they work with js validation
- [#826] PHPStan updated and resolved dependencies
- [#755] updated npm packages to latest version
- [#768] extracted "Domain, Multidomain, Multilanguage" documentation into a separated article

#### Fixed

- [#831] fixed project-base install conflict due to snc/redis-bundle version
- [#822] fixed releaser due to microservice removal
- [#789] fixed kubernetes naming
- [#801] markdown formatting fixed in 3rd party copyrights
- [#758] configuration of docker-compose and docker-sync was enhanced for windows development in monorepo
- [#779] upgrade instructions of cart as entity has been updated to be more format consistent
- [#743] docs tweaks
- [#790] use PR # instead of internal backlog IDs in contributing guide for commits
- [#783] fixed production installation documentation

## [v7.0.0-beta5](https://github.com/shopsys/shopsys/compare/v7.0.0-beta4...v7.0.0-beta5) - 2019-01-21

### [shopsys/coding-standards]

#### Changed

- [#707] order of imports and sort algorithm is now explicitly set

### [shopsys/framework]

#### Added

- [#619] Simplify adding new form groups

#### Changed

- [#688] renamed AdvancedSearchFacade to AdvancedSearchProductFacade
- [#650] change visibility in extendable classes according to our docs
- [#692] redirectController is now registered as a public container service
- [#624] ImageUploadType now can be used for uploading specific image types
- [#690] refactored checking duplicated e-mail to User constructor
- [#687] refactored AdvancedSearch filter names to Filter classes
- [#723] Copy categories to variant if main variant is edited or variant is added

#### Fixed

- [#706] fixed symfony deprecation warnings
- [#647] fixed link to our Twitter account [@ShopsysFW] in demo data
- [#640] Fixed cart merging after login (closes [#588]), Thanks to [@dzarda86]
- [#628] selectboxes rollout problem for dev env
- [#644] JsFormValidatorFactory: make sure prototype's parent is set in CollectionType
- [#655] calculatedHidden attribute of a main variant is taken over from the hidden attribute, Thanks to [@dzarda86]
- [#676] formatNumber filter supplied with missing orderLocale variable, Thanks to [@dzarda86]
- [#712] Stop generating url for unsafe route 'fp_js_form_validator.check_unique_entity', Thanks to [@pk16011990]
- [#567] fix unnecessary log error after login as admin on second PC, Thanks to [@henzigo]

#### Removed

- [#641] FormGroup: unused option "is_group_container_to_render_as_the_last_one" removed
- [#609] remove redundant PaginationResult instantiation

### [shopsys/microservice-product-search-export]

#### Fixed

- [#637] fixed search in EAN, partno, catnum
- [#735] added symfony/dotenv to composer.json

### [shopsys/project-base]

#### Added

- [#673] added support for custom prefixing in redis

#### Changed

- [#596] Trusted proxies are now configurable in parameters.yml file
- [#703] improved frontend performance
- [#674] Dockerignore no longer excludes docker/nginx folder
- [#645] SVG icons in generated document
- [#702] switched to Debian php-fpm image

#### Fixed

- [#685] fix wrong variable name in flash message
- [#660] Bootstrap: do not prematurely boot Kernel when running in console
- [#710] Removed blank space when popup is active

### [shopsys/shopsys]

#### Added

- [#729] added scripts for monorepo maintenance into /.ci directory
- [#623] Add Releaser to automate release process, Thanks to [@TomasVotruba]
- [#719] New SSFW references added to readme.md
- [#642] entity-extension.md: added warning about extending OrderItem and its descendants
- [#705] added cookbook about basic data import
- [#651] It's possible to add index prefix to elastic search
- [#649] docs: added missing link to Adding an Icon into a Button cookbook
- [#633] Google Cloud deploy using Terraform, Kustomize and Kubernetes
- [#646] document behavior of less compiling
- [#693] documentation of translations

#### Changed

- [#733] twig/twig v2.6.1 is now ignored
- [#610] ProductOnCurrentDomainFacade: rename getPaginatedProductDetails\* methods
- [#699] Improve upgrade instructions
- [#708] upgrading-monorepo.md: improved instructions
- [#709] Improved entity data objects documentation
- [#627] model service layer removal
- [#611] how-to-get-a-cluster-running.md updated
- [#659] Docs: Updated informations about monorepo ([#454])
- [#716] Phing: PHPStan analysis of packages is separated into another target

#### Fixed

- [#656] docs: duplicated frontend sections are fixed and joint into one
- [#679] webserver container starts after php-fpm is started

## [v7.0.0-beta4] - 2018-11-28

### [shopsys/project-base]

#### Changed

- [#635] allow composer unlimited memory
- [#616] services.yml: automatic registration of classes with suffix "Repository" in namespace `ShopBundle\Model\`

## [v7.0.0-beta3] - 2018-11-27

### [shopsys/coding-standards]

#### Added

- [#554] Add fixers to autocomplete missing return and param annotations, Thanks to [@TomasVotruba]

### [shopsys/framework]

#### Added

- [#564] added a new translation extractor for properties of Constraints
- [#546] added new Symfony command "shopsys:environment:change"
- [#544] eased adding JS to admin

#### Changed

- [#576] `OrderFormType` in administration is now rendered by default

#### Fixed

- [#581] Product creation: calculated availability is set additionally if necessary
- [#568] Unified demo multidomain data with demo data

#### Removed

- [#595] removal of automatic price calculation

### [shopsys/project-base]

#### Changed

- [#541] Rename database tests to functional tests
- [#566] Set development docker build target before production and CI targets
- [#543] the environment setting during "composer install" is not interactive
- [#557] php-fpm image has standard workdir (`/var/www/html`) in ci stage
- [#613] restart smtp server after crash on production, Thanks to [@henzigo]
- [#547] `content-test` directory is used instead of `content` during the tests

#### Fixed

- [#598] fix folder sharing for production docker-compose, Thanks to [@henzigo]
- [#524] fixes of annotations issues (PHPStan upgrade preparation), Thanks to [@ondrejmirtes]
- [#600] Fixing heading sizes in frontend
- [#618] maintenance page fix

### [shopsys/shopsys]

#### Added

- [#556] Updated documentation about phing targets and added article about development on Shopsys Framework
- [#559] Docs: added `cookbook/modifying-a-template-in-administration.md`
- [#553] docs: added `introduction/faq.md` with frequently asked questions
- [#527] Added script for exporting logs into jenkins workspace
- [#573] add FQN @param, @return and @var everywhere, Thanks to [@TomasVotruba]
- [#535] added .dockerignore files
- [#563] Open Source License Acknowledgements and Third-Party Copyrights

#### Changed

- [#560] monorepo: `tests-packages` now include tests of `coding-standards` package
- [#601] docs: information about logging are more discoverable now
- [#545] Part of the application build is now contained in the build of the image
- [#533] main php-fpm container now uses multi-stage build feature
- [#620] generate error pages in `restart_kubernetes.sh`
- [#540] domains URLs are auto-configured during "composer install"
- [#606] Stale bot for GitHub issues delayed

#### Removed

- [#528] Removed `depends_on` and `links` from `docker-compose.yml` files
- [#551] github token erase

## [v7.0.0-beta2] - 2018-10-19

### [shopsys/framework]

#### Changed

- [#519] Feeds generation now works with performance data
- [#513] Manipulation with domains is modified and documented now
- [#509] Records of `SettingValues` table need to be valid with application

#### Fixed

- [#491] Fixed command `debug:router` by asking for domain

### [shopsys/migrations]

#### Fixed

- [#515] Migration count command is now fixed and initialized with correct configuration

### [shopsys/project-base]

#### Added

- [#497] Adding `php.ini` to image is now done only in `Dockerfiles`
- [#486] Docker for production

#### Changed

- [#496] Require global package hirak/prestisimo in docker images

#### Fixed

- [#502] Fixed acceptance tests (loading DB dump)

### [shopsys/shopsys]

#### Added

- [#493] Added list of 3rd party licenses
- [#499] Added Docker-sync for Windows development
- [#500] Docs for Docker installation on Linux: add Docker Compose, Thanks to [@thirdknown]
- [#510] Added link to `upgrade.md` file in knowledgebase index
- [#498] Docs: instructions for merging into master
- [#511] Enable logging of errors in all php containers

#### Changed

- [#506] Improved upgrade instruction
- [#487] Installation guides: minimal stability for composer create-project changed from alpha to beta
- [#512] `Dockerfiles` of microservices now use multi-stage build feature
- [#525] `UPGRADE.md`: improved unreleased instructions
- [#494] Microservices webserver using `nginx` + `php-fpm`
- [#490] Use shopsys packages instead of hacking composer.json to speed up installation

#### Fixed

- [#521] Fixed typo in `.github/ISSUE_TEMPLATE.md`

## [v7.0.0-beta1] - 2018-09-18

### [shopsys/framework]

#### Changed

- [#472] Changed controllers and factories "private" methods and attributes to "protected" to allow extensibility

#### Fixed

- [#473] Fixed rendering forms in extended admin controller to be as same as in framework
- [#480] admin: fix product form for product variants

### [shopsys/shopsys]

#### Changed

- [#476] Demo images are part of the framework package now
- [#468] Settings for docker on mac are now more optimized

## [v7.0.0-alpha6] - 2018-09-14

### [shopsys/coding-standards]

#### Added

- [#384] added new rule to keep class spacing consistent, Thanks to [@TomasVotruba]

### [shopsys/framework]

- [#425] Solves issue: Difficult to add icon into submit button [#161], Thanks to [@kulincz]

#### Changed

- [#295] javascript compiling: improved memory consumption, Thanks to [@pk16011990]

#### Fixed

- [#386] fixed FileUpload wrong second parameter for UploadFailedException, Thanks to [@Maybeway]
- [#420] Order flow fix - function call on string ([#419]), Thanks to [@jDolba]

#### Removed

- [#428] \*EditFormTypes removed

### [shopsys/monorepo-tools]

#### Fixed

- [#433] fix: splitting now works correctly on repositories containing files with spaces, Thanks to [@dominikkaluza]

### [shopsys/project-base]

#### Changed

- [#449] config files are now split into individual package configuration files
- [#447] improved `UnableToResolveDomainException` error page

### [shopsys/shopsys]

#### Added

- [#409] added framework models extension
- [#424] adding-a-new-advert-position.md: added instructions for editing images.yml config file
- [#429] added microservice for product search export
- [#463] warm up Symfony cache in microservices during start-up

#### Changed

- [#393] Continuous integration via Kubernetes
    - for details, see [Introduction to Kubernetes](/docs/kubernetes/introduction-to-kubernetes.md)
- [#444] Improve Postgres configuration to improve performance
- [#430] Microservices are now built as Docker images
- [#446] Generate CHANGELOG.md with ChangelogLinker, Thanks to [@TomasVotruba]
- [#438] Attribute telephone moved from a billing address to the personal data of a user

#### Fixed

- [#436] fix: Symfony >=3.4.15 marked as conflicting in composer.json

## [v7.0.0-alpha5] - 2018-08-22
### [shopsys/framework]
#### Added
- [#354 - Product search in elasticsearch](https://github.com/shopsys/shopsys/pull/354)
    - elasticsearch docker container: volumes are now set in order to preserve data when the container is shut down
    - added Symfony commands for creating and removing Elasticsearch indexes
    - added Symfony command for exporting all products to Elasticsearch
    - added new phing targets for working with Elasticsearch
    - added CRON module for exporting products' data into Elasticsearch
    - product search uses Elasticsearch
    - docs: added [article](https://github.com/shopsys/shopsys/blob/v7.0.0-alpha5/docs/introduction/product-search-via-elasticsearch.md) with Elasticsearch overview
- [#375 ProductFormType should be extensible](https://github.com/shopsys/shopsys/pull/375)
    - `WarningMessageType` is ready to use
    - `DisplayOnlyUrlType` is ready to use
    - `LocalizedFullWidthType` is ready to use
    - `ProductParameterValueType` is ready to use
    - `ProductCalculatedPricesType` is ready to use
    - `ProductsType` can be used for variants group with customized button name and info title above the form group
    - `DisplayOnlyType` can be used for disabled groups to display only message instead of original fields of the group
    - `PluginCrudExtensionFacade` was modified to extend forms in `GroupType` design
    - templates of FormTypes were modified for use with `ProductFormType` elements
        - `multidomain_row` can contain label that is needed for categories assignment
        - `disabledField` template variable is used via `attr.disabledField`
    - Admin: product form was modified
        - detail, edit, new twig temlpates code was reduced
        - FormType elements were joint into groups and newly created FormTypes were used
        - javascript validation and tests were bound to the new paths of the form elements
        - docs were updated
- [#401 - Microservice Product Search](https://github.com/shopsys/shopsys/pull/401)
    - added [Microservice Product Search](https://github.com/shopsys/microservice-product-search), microservice is used for the searching of products on Shopsys Framework
    - added MicroserviceClient component

#### Changed
- [#385 - AccessDeniedHttpException replaced by AccessDeniedException](https://github.com/shopsys/shopsys/pull/385)
- [#395 - ability of adding new advert position from project-base](https://github.com/shopsys/shopsys/pull/395)
    - added new `AdvertPositionRegistry` class responsible for providing known advert banner positions
    - existence of the class enables adding new custom positions from project-base (by using class inheritance)
    - twig templates: advert position names are used directly instead of using class constants to ease the usage
    - added new [cookbook for adding a new advert position](./docs/cookbook/adding-a-new-advert-position.md)
- [#378 - Upgrade PostgreSQL to version 10](https://github.com/shopsys/shopsys/pull/378):
    - please look at [UPGRADE.md](./UPGRADE.md) to upgrade without losing your data.

#### Fixed
- [#260 - JS validation: dynamically added form inputs are now validated](https://github.com/shopsys/shopsys/pull/260)
- [#397 - classes excluded from ObjectIsCreatedByFactorySniff rule are fixed](https://github.com/shopsys/shopsys/pull/397)

### [shopsys/project-base]
#### Changed
- [#401 - Microservice Product Search](https://github.com/shopsys/shopsys/pull/401)
    - added [Microservice Product Search](https://github.com/shopsys/microservice-product-search), microservice is used for the searching of products on Shopsys Framework
    - now the following phing targets are also triggered over the microservice
        - standards
        - standards-diff
        - standards-fix
        - standards-fix-diff
        - standards-packages
        - standards-fix-packages
        - standards-fix-packages-diff
    - availability of microservices is checked during the build

#### Fixed
- [#359 - product search: fix wrong variable usage in url to reset product filter](https://github.com/shopsys/shopsys/pull/359)

### [shopsys/shopsys]
#### Added
- [#379 - Stale bot activation](https://github.com/shopsys/shopsys/pull/379)
    - added configuration file for GitHub Stale bot
    - templates for pull requests and issues were moved into [.github](/.github) folder
- [#401 - Microservice Product Search](https://github.com/shopsys/shopsys/pull/401)
    - added [Microservice Product Search](https://github.com/shopsys/microservice-product-search), microservice is used for the searching of products on Shopsys Framework

#### Changed
- [#389 - Standardized YAML files indentation to 4 spaces](https://github.com/shopsys/shopsys/pull/389)

### [shopsys/monorepo-tools]
#### Fixed
- [#399 - monorepo-tools: Handle rewriting of empty commits](https://github.com/shopsys/shopsys/pull/399) [@drekbour]

## [v7.0.0-alpha4] - 2018-08-02
### [shopsys/framework]
#### Added
- [#335 - Possibility to add a new administration page](https://github.com/shopsys/shopsys/pull/335)
    - added cookbook [Adding a New Administration Page](/docs/cookbook/adding-a-new-administration-page.md) along with the side menu and breadcrumbs

#### Changed
- [#302 - All persistent files like uploads are now stored using abstract filesystem (Flysystem)](https://github.com/shopsys/shopsys/pull/302)
    - abstract filesystem is used to store:
        - uploaded files and images
        - uploaded files and images via WYSIWYG
        - generated feeds
        - generated sitemaps
    - all services using PernamentPhpFileCache now use RedisCache instead
- [#286 - Instantiate entity data objects by factories](https://github.com/shopsys/shopsys/pull/286)
    - entity data objects have only an empty constructor now
    - creation of entity data objects moved to factories to allow extensibility
- [#244 Redesign of administration](https://github.com/shopsys/shopsys/pull/244)
    - full-width layout
    - colors changed to match new Shopsys CI
    - main menu moved to the left panel along with settings menu
    - menu items do not have type anymore, which lead to simplification of the code
    - menu was slightly restructured
- [#285 - Removal of base data fixtures](https://github.com/shopsys/shopsys/pull/285)
    - all Base Data Fixtures were removed
    - the data are created either in database migrations or in Demo Data Fixtures
- [#271 - Complete refactoring of feeds functionality](https://github.com/shopsys/shopsys/pull/271)
  - modules are responsible for querying the data to improve performance
  - interfaces from package product-feed-interface are not used anymore as they were only important with open-box architecture
  - only relevant data is fetched from the database, should result in enhanced performance
  - FeedInterface and FeedInfoInterface define the way feeds are registered in the system
  - FeedExport is responsible for the actual generation of a file in batches on a specific domain
  - FeedRenderer is responsible for rendering the feed from Twig template
  - FeedPathProvider is responsible for providing the correct filepath and url to the specified feed on a domain
  - ProductUrlsBatchLoader and ProductParametersBatchLoader are responsible for loading product data in batches
  - cron modules use the logger for debug information
  - DailyFeedCronModule is responsible for continuation of the correct feed after waking up
- [#182 - Cart: flush() is called only if there are really some changes in cart items](https://github.com/shopsys/shopsys/pull/182)
- admin menu is now implemented using the KnpMenuBundle as a part of [#335 - Possibility to add a new administration page](https://github.com/shopsys/shopsys/pull/335)
    - old implementation using the `admin_menu.yml` configuration along with `AdminMenuYamlFileExtractor` was removed
    - class `Breadcrumb` was renamed to `BreadcrumbOverrider` and its scope was reduced
- [#313 - Streamed logging](https://github.com/shopsys/shopsys/pull/313)
    - monolog logs into streams instead of files (use `docker-compose logs` to access it)
    - see details in the [Logging](/docs/introduction/logging.md) article
- [#341 - Category entity in constructor of CategoryRepository is resolved via EntityNameResolver](https://github.com/shopsys/shopsys/pull/341)
- [#364 - Admin: brand form is rendered via BrandFormType](https://github.com/shopsys/shopsys/pull/364)
- [#370 - MultidomainEntityClassFinderFacade: metadata are checked on class name resolved by EntityNameResolver](https://github.com/shopsys/shopsys/pull/370)

#### Fixed
- [#304 - Unnecessary SQL queries on category detail in admin](https://github.com/shopsys/shopsys/pull/304):
    - category translations for ancestor category are loaded in the same query as categories
- [#317 - Travis build is failing for shopsys/framework](https://github.com/shopsys/shopsys/pull/317):
    - framework package requires redis bundle and redis extension
    - redis extension enabled in configuration for travis
- [#316 - Admin: feed items on feeds generation page contain clickable link and datetime](https://github.com/shopsys/shopsys/pull/316)
    - checks for existing file and for modified time of file use abstract filesystem methods
- [#314 - Dropped triggers before creation](https://github.com/shopsys/shopsys/pull/314)
- [#263 - CartWatcherFacade: fixed swapped messages](https://github.com/shopsys/shopsys/pull/263)
- [#339 - Downgrade snc/redis-bundle to 2.1.4 due to Issue in phpredis](https://github.com/shopsys/shopsys/pull/339)
- [#351 - added missing typehints in methods of CookiesFacade and OrderMailService](https://github.com/shopsys/shopsys/pull/351)
- [#352 - flushes executed in loops are now executed outside of loop](https://github.com/shopsys/shopsys/pull/352)
- [#342 - procedures are now executed only if relevant columns are changed](https://github.com/shopsys/shopsys/pull/342)
- [#362 - guidelines-for-pull-request.md: fixed indentation of lines and code blocks](https://github.com/shopsys/shopsys/pull/362)
- [#372 - test fails if framework is set as singledomain](https://github.com/shopsys/shopsys/pull/372)

#### Removed
- [#371 - Remove unused dependencies](https://github.com/shopsys/shopsys/pull/371) [@TomasVotruba]
- Error reporting functionality was removed as a part of [#313 - Streamed logging](https://github.com/shopsys/shopsys/pull/313)
    - error reporting should be done from the outside of the application (eg. by [Kubernetes](https://kubernetes.io/))

### [shopsys/project-base]
#### Fixed
- [#347 - Composer: disable installation of broken version of codeception/stub](https://github.com/shopsys/shopsys/pull/347)
- [#353 - Fixed paths in project-base/docker/conf/docker-compose-win.yml.dist](https://github.com/shopsys/shopsys/pull/353)
- [#363 - docker-sync.yml added to gitignore to allow individual configuration](https://github.com/shopsys/shopsys/pull/363)

### [shopsys/shopsys]
#### Added
- [#320 - Docs: overview of possible and impossible glassbox customizations](https://github.com/shopsys/shopsys/pull/320)
    - added [framework-extensibility.md](/docs/extensibility/framework-extensibility.md) article

#### Changed
- [#296 - normalize phing target "timezones-check"](https://github.com/shopsys/shopsys/pull/296): [@pk16011990]

### [shopsys/monorepo-tools]
#### Added
- [#345 - monorepo-tools: allow incremental build of monorepo](https://github.com/shopsys/shopsys/pull/345) [@lukaso]
- [#311 - monorepo split allows adding new package when monorepo is already tagged](https://github.com/shopsys/shopsys/pull/311)
#### Fixed
- [#281 - monorepo-tools: Fix scripts to work on OS X](https://github.com/shopsys/shopsys/pull/282) [@lukaso]

### [shopsys/coding-standards]
#### Added
- [#371 - Add UnusedPrivateElementsSniff](https://github.com/shopsys/shopsys/pull/371) [@TomasVotruba]
- [#308 - Sniff for forgotten dumps](https://github.com/shopsys/shopsys/pull/308)
    - ecs tester for coding standards was added with tests for sniffs and fixers [@TomasVotruba]
    - added support for checking standards of file types twig, html
    - added sniff for checking of forgotten dump functions

### [shopsys/product-feed-google]
#### Changed
- [#286 - Instantiate entity data objects by factories](https://github.com/shopsys/shopsys/pull/286)
    - entity data objects have only an empty constructor now
    - creation of entity data objects moved to factories to allow extensibility
- [#271 - Complete refactoring of feeds functionality](https://github.com/shopsys/shopsys/pull/271)
    - for details see section shopsys/framework

#### Fixed
- [#323 - Packages that depend on shopsys/framework need redis extension enabled](https://github.com/shopsys/shopsys/pull/323)
    - redis extension in travis config was enabled

### [shopsys/product-feed-heureka]
#### Changed
- [#286 - Instantiate entity data objects by factories](https://github.com/shopsys/shopsys/pull/286)
    - entity data objects have only an empty constructor now
    - creation of entity data objects moved to factories to allow extensibility
- [#271 - Complete refactoring of feeds functionality](https://github.com/shopsys/shopsys/pull/271)
    - for details see section shopsys/framework

#### Fixed
- [#323 - Packages that depend on shopsys/framework need redis extension enabled](https://github.com/shopsys/shopsys/pull/323)
    - redis extension in travis config was enabled

#### Removed
- [#371 - Remove unused dependencies](https://github.com/shopsys/shopsys/pull/371) [@TomasVotruba]

### [shopsys/product-feed-heureka-delivery]
#### Changed
- [#271 - Complete refactoring of feeds functionality](https://github.com/shopsys/shopsys/pull/271)
    - for details see section shopsys/framework

#### Fixed
- [#323 - Packages that depend on shopsys/framework need redis extension enabled](https://github.com/shopsys/shopsys/pull/323)
    - redis extension in travis config was enabled

### [shopsys/product-feed-interface]
#### Abandoned
The package was removed from monorepo during [#271 - Complete refactoring of feeds functionality](https://github.com/shopsys/shopsys/pull/271) and it's development was discontinued.
It was only important with [the original open-box architecture](https://blog.shopsys.com/architecture-and-workflow-overview-f54ccae348ce), but after the creation of [shopsys/framework] there is no need for isolating interfaces in separate packages.

### [shopsys/product-feed-zbozi]
#### Changed
- [#286 - Instantiate entity data objects by factories](https://github.com/shopsys/shopsys/pull/286)
    - entity data objects have only an empty constructor now
    - creation of entity data objects moved to factories to allow extensibility
- [#271 - Complete refactoring of feeds functionality](https://github.com/shopsys/shopsys/pull/271)
    - for details see section shopsys/framework

#### Fixed
- [#323 - Packages that depend on shopsys/framework need redis extension enabled](https://github.com/shopsys/shopsys/pull/323)
    - redis extension in travis config was enabled

### [shopsys/project-base]
#### Added
- configuration for admin controllers as a part of [#335 - Possibility to add a new administration page](https://github.com/shopsys/shopsys/pull/335)
    - see the config file in `src/Shopsys/ShopBundle/Resources/config/routing.yml`

#### Fixed
- [#315 - Route logout/ without csrf token returns not found](https://github.com/shopsys/shopsys/pull/315)
    - route logout/ must to be called with token in every case because LogoutListener from Symfony throws exception if token generator is set in configuration of firewall but the route logout is used without csrf token parameter
- [#339 - Downgrade snc/redis-bundle to 2.1.4 due to Issue in phpredis](https://github.com/shopsys/shopsys/pull/339)

#### Removed
- [#371 - Remove unused dependencies](https://github.com/shopsys/shopsys/pull/371) [@TomasVotruba]

### [shopsys/microservice-product-search]
#### Added
- [#368 - Microservice Product Search](https://github.com/shopsys/shopsys/pull/368)
    - the repository was added, extracting current product search of Shopsys Framework

## [v7.0.0-alpha3] - 2018-07-03
### [shopsys/framework]
#### Changed
- [#272 - Changed concept of Components](https://github.com/shopsys/shopsys/pull/143):
    - added definition of Components in [components.md](./docs/introduction/components.md):
    - by this definition, classes that did not match it were moved or refactored.
    - FriendlyUrlGenerator refactored: FriendlyUrlGeneratorFacade does not know anything about particular entities that the friendly urls are generated for.
        These data are now served from implementations of FriendlyUrlDataProviderInterface.
- [#143 - Shopsys framework now uses latest version of Shopsys coding standards](https://github.com/shopsys/shopsys/pull/143) [Shopsys Coding Standards dev-master](./packages/coding-standards) is now used
    - version of coding-standards package was updated to dev-master in [composer.json](./packages/framework/composer.json)
    - modified [travis script](./packages/framework/project-base/.travis.yml)
        - removed check for php 7.0
        - php-cs-fixer, phpcs, phpmd binaries replaced by ecs binaries
        - inline code skips moved to [autoload-easy-coding-standard.yml](./packages/framework/autoload-easy-coding-standard.yml)
- [#171 - Update to twig 2.x](https://github.com/shopsys/shopsys/pull/171):
    - updated to Twig 2.4.8
    - all depracated calls has been fixed
- [#188 - Rebrading of Shopsys Framework](https://github.com/shopsys/shopsys/pull/188/)
    - all occurrences of netdevelo were changed to shopsys
- [#257 - Admin: reset.less: disable scrollbar normalization in order to fix problems with jQuery UI drag&drop]( https://github.com/shopsys/shopsys/pull/257)
    - dragged item is now at correct position
        -scrollbar normalization was disabled for sortable components
- [#261 - Sending personal data to Heureka can be disabled](https://github.com/shopsys/shopsys/pull/261)
    - the last step of cart contains opt-out checkbox to disable sending personal data to Heureka (if Heureka Verified by Customers is enabled on the domain)
- [#206 clearing Setting's cache is now done via DoctrineEventListener](https://github.com/shopsys/shopsys/pull/206)
    - `EntityManagerFacade` was removed
    - Doctrine identity map can be cleared via `EntityManager::clear()` directly
- [#254 - Removal of EntityDetail classes](https://github.com/shopsys/shopsys/pull/276)
    - `TransportDetail` and `TransportDetailFactory` were removed - `TransportFacade` is now able to provide transport base prices
    - `PaymentDetail` and `PaymentDetailFactory` were removed - `PaymentFacade` is now able to provide payment base prices
    - `ProductDetail` and `ProductDetailFactory` were removed - new `ProductCachedAttributesFacade` is now responsible for caching of products selling prices and parameters
    - `CategoryDetail` was renamed to `CategoryWithPreloadedChildren` and methods for it's creation were moved from `CategoryDetailFactory` to new `CategoryWithPreloadedChildrenFactory`
    - `LazyLoadedCategoryDetail` was renamed to `CategoryWithLazyLoadedVisibleChildren` and methods for it's creation were moved from deleted `CategoryDetailFactory` to new `CategoryWithLazyLoadedVisibleChildrenFactory`
- [#274 grid: drag&drop is supported with enabled gridInlineEdit](https://github.com/shopsys/shopsys/pull/274)
- [#165 Different approach to multidomain entities](https://github.com/shopsys/shopsys/pull/165)
    - multi-domain entities were changed so they are used similarly to translations
    - main entities have a bidirectional association to a collection of its entity domains (eg. `BrandDomain`)
        - only the main entity has access to its entity domains
        - multi-domain attributes are accessed via the main entity
    - the main entities are responsible for creating and editing its entity domains
        - entity domain factories such as `BrandDomainFactory` were removed
    - entity domains have their own IDs instead of compound primary keys
    - entities that were modified: `Brand`, `Product`, `Category`, `Payment` and `Transport`
    - `BrandEditFormType`, `BrandDetail` and `BrandDetailFactory` were removed as they were no longer necessary
    - `DomainsType` now uses array of booleans indexed by domain IDs instead of array of domain IDs to be consistent with the behavior of `MultidomainType`
    - `CategoryDomain::$hidden` was changed to `CategoryDomain::$enabled` in sake of consistency
    - `PaymentDomain` and `TransportDomain` are now created even for domains on which the entity should not be visible (to allow for other multi-domain entities and in the sake of consistency)

#### Fixed
- [#246 - docker-sync.yml.dist: fixed not existing relative paths](https://github.com/shopsys/shopsys/pull/246) [@DavidKuna]
- [#132 - Admin: brand edit page: URLs setting rendering](https://github.com/shopsys/shopsys/pull/132):
    - admin: brand detail page: rendering of URLs setting
        - brand creation: URLs setting is not rendered at all
        - brand editing: URLs section is rendered in the SEO section
- [#173 - remove editData from model: all editData from framework model were merged into Data](https://github.com/shopsys/shopsys/pull/173):
    - remove editData from model: all editData from framework model were merged into its Data relatives
        - merged model EditData into Data with its Factory and modified Facade and Controller for
                - Product
                - Payment
                - Transport
                - Brand
- [#176 - Admin: Validation for free shipping is inconsistent](https://github.com/shopsys/shopsys/pull/176)
    - `Resources/views/Admin/Content/TransportAndPayment/freeTransportAndPaymentLimitSetting.html.twig`: `form_errors` was included for `form_widget` for consistency of admin forms
- [#228 - show selectbox options](https://github.com/shopsys/shopsys/pull/228)
    - the use of jQuery plugin for selectboxes was modified on Shopsys Framework side so options will now be seen
- [#243 - Admin: changed domain icon in e-shop domain administration can be saved](https://github.com/shopsys/shopsys/pull/243)
    - changed domain icon in e-shop domain administration can be saved
- copying of localized entities
    - detection of new locale is now done before multidomain data are created

### [shopsys/project-base]
#### Changed
- [#143 - Shopsys framework now uses latest version of Shopsys coding standards](https://github.com/shopsys/shopsys/pull/143) [Shopsys Coding Standards dev-master](./packages/coding-standards/) is now used
    - version of coding-standards package was updated to dev-master in [composer.json](./packages/project-base/composer.json)
    - inline code skips moved to [autoload-easy-coding-standard.yml](./project-base/autoload-easy-coding-standard.yml)
    - replaced php-cs-fixer. phpcs. phpmd for ecs in `build.xml` and `build-dev.xml` scripts
- [#171 - Update to twig 2.x](https://github.com/shopsys/shopsys/pull/171):
    - updated to Twig 2.4.8
    - all depracated calls has been fixed
- [#188 - Rebrading of Shopsys Framework](https://github.com/shopsys/shopsys/pull/188/)
    - old logo was changed for the new Shopsys Framework logo
    - all occurrences of netdevelo were changed to shopsys
- [#165 Different approach to multidomain entities](https://github.com/shopsys/shopsys/pull/165)
    - multi-domain attributes are accessed via their main entities (instead of usual entity details)

#### Fixed
- [#131 - correct rendering of checkbox label](https://github.com/shopsys/shopsys/pull/131):
    - `Front/Form/theme.html.twig`: block `checkbox_row` now uses block `form_label` for proper label rendering
        - the absence of `label` html tag was causing problems with JS validation (the error message was not included in the popup overview)
- [#229 - php-fpm/Dockerfile: switch to another mirror of alpine linux repository](https://github.com/shopsys/shopsys/pull/229):
    - fix uninstallable postgres 9.5 by using repository https://dl-cdn.alpinelinux.org/alpine/ instead of https://dl-3.alpinelinux.org/alpine/
- [#242 - php-fpm/Dockerfile: drop usage of https when accessing dl-cdn.alpinelinux.org](https://github.com/shopsys/shopsys/pull/242)
- [#277 - Tests fail when only one domain is set](https://github.com/shopsys/shopsys/issues/277)

#### Security
- [#178 - JsFormValidatorBundle security issue with Ajax validation](https://github.com/shopsys/shopsys/pull/178)
    - removed the bundle's public route that allowed lookup any DB table by any field
    - the purpose of the route is for ajax validation of an entity uniqueness but the feature is not used anyway

### [shopsys/shopsys]
#### Added
- [#143](https://github.com/shopsys/shopsys/pull/143) [Shopsys Coding Standards dev-master](./packages/coding-standards/) is now used
    - config [easy-coding-standard.yml](./easy-coding-standard.yml]) for importing rules of new easy-coding-standard package

#### Changed
- [#143](https://github.com/shopsys/shopsys/pull/143) [Shopsys Coding Standards dev-master](./packages/coding-standards/) is now used
    - php-cs-fixer, phpcs, phpmd binaries replaced by ecs binaries
    - build scripts were modified to work with new easy-coding-standard checker
- [#230 - composer-dev updates dependencies if composer.json was changed](https://github.com/shopsys/shopsys/pull/230)

#### Fixed
- [#266 ecs fix and unification in monorepo](https://github.com/shopsys/shopsys/pull/266)
    - ObjectIsCreatedByFactorySniff: cover edge case
        - previous implementation failed eg. when creating a class using a variable (new $className;)
   - autoload-easy-coding-standard.yml renamed to easy-coding-standard.yml as it is not autoloaded in any way
   - all phing targets excluding *-diff use --clear-cache option
   - all packages use their own configuration file
   - all packeges skip ObjectIsCreatedByFactorySniff in tests folder

### [shopsys/coding-standards]
#### Added
- [#249 - First architectonical codesniff](https://github.com/shopsys/shopsys/pull/249)
    - new sniff `ObjectIsCreatedByFactorySniff` was created and was integrated into coding standards as service

#### Changed
- [#143](https://github.com/shopsys/shopsys/pull/143) [EasyCodingStandard v4.3.0](https://github.com/Symplify/EasyCodingStandard/tree/4.3) is now used
    - rules config file changed its format from neon to yaml

#### Fixed
- [#222 - coding-standards package is now up to date with new PHP_codesniffer v3.3.0](https://github.com/shopsys/shopsys/pull/222)
    - import of parent class of ForbiddenExitSniff was corrected

### [shopsys/form-types-bundle]
#### Changed
- [#143 - Shopsys framework now uses latest version of Shopsys coding standards](https://github.com/shopsys/shopsys/pull/143) [Shopsys Coding Standards dev-master](./packages/coding-standards/) is now used
    - version of coding-standards package was updated to dev-master in [composer.json](./packages/form-types-bundle/composer.json)
    - modified [travis script](./packages/form-types-bundle/.travis.yml)
        - removed check for php 7.0 due to compatibility with ecs
        - php-cs-fixer, phpcs, phpmd binaries replaced by ecs binary

### [shopsys/migrations]
#### Changed
- [#143 - Shopsys framework now uses latest version of Shopsys coding standards](https://github.com/shopsys/shopsys/pull/143) [Shopsys Coding Standards dev-master](./packages/coding-standards/) is now used
    - version of coding-standards package was updated to dev-master in [composer.json](./packages/migrations/composer.json)
    - modified [travis script](./packages/migrations/.travis.yml)
        - removed check for php 7.0 due to compatibility with ecs
        - php-cs-fixer, phpcs, phpmd binaries replaced by ecs binary
- [#185 Migrations now can be installed in different order or even be skipped](https://github.com/shopsys/shopsys/pull/185)
    - order of installed migration is saved in migrations-lock.yml
        - this order can be changed
        - migrations can be marked as skipped
    - you can read about the details in the [documentation](./docs/introduction/database-migrations.md)

### [shopsys/plugin-interface]
#### Changed
- [#143 - Shopsys framework now uses latest version of Shopsys coding standards](https://github.com/shopsys/shopsys/pull/143) [Shopsys Coding Standards dev-master](./packages/coding-standards/) is now used
    - version of coding-standards package was updated to dev-master in [composer.json](./packages/plugin-interface/composer.json)
    - modified [travis script](./packages/plugin-interface/.travis.yml)
        - removed check for php 7.0 due to compatibility with ecs
        - php-cs-fixer, phpcs, phpmd binaries replaced by ecs binary

### [shopsys/product-feed-google]
#### Changed
- [#143 - Shopsys framework now uses latest version of Shopsys coding standards](https://github.com/shopsys/shopsys/pull/143) [Shopsys Coding Standards dev-master](./packages/coding-standards/) is now used
    - version of coding-standards package was updated to dev-master in [composer.json](./packages/product-feed-google/composer.json)
    - modified [travis script](./packages/product-feed-google/.travis.yml)
        - removed check for php 7.0 due to compatibility with ecs
        - php-cs-fixer, phpcs, phpmd binaries replaced by ecs binary
- [#171 - Update to twig 2.x](https://github.com/shopsys/shopsys/pull/171):
    - updated to Twig 2.4.8
    - all depracated calls has been fixed

### [shopsys/product-feed-heureka]
#### Changed
- [#143 - Shopsys framework now uses latest version of Shopsys coding standards](https://github.com/shopsys/shopsys/pull/143) [Shopsys Coding Standards dev-master](./packages/coding-standards/) is now used
    - version of coding-standards package was updated to dev-master in [composer.json](./packages/product-feed-heureka/composer.json)
    - inline code skips moved to [autoload-easy-coding-standard.yml](./packages/product-feed-heureka/autoload-easy-coding-standard.yml)
    - modified [travis script](./packages/product-feed-heureka/.travis.yml)
        - removed check for php 7.0 due to compatibility with ecs
        - php-cs-fixer, phpcs, phpmd binaries replaced by ecs binary
- [#171 - Update to twig 2.x](https://github.com/shopsys/shopsys/pull/171):
    - updated to Twig 2.4.8
    - all depracated calls has been fixed

### [shopsys/product-feed-heureka-delivery]
#### Changed
- [#143 - Shopsys framework now uses latest version of Shopsys coding standards](https://github.com/shopsys/shopsys/pull/143) [Shopsys Coding Standards dev-master](./packages/coding-standards/) is now used
    - version of coding-standards package was updated to dev-master in [composer.json](./packages/product-feed-heureka-delivery/composer.json)
    - modified [travis script](./packages/product-feed-heureka-delivery/.travis.yml)
        - removed check for php 7.0 due to compatibility with ecs
        - php-cs-fixer, phpcs, phpmd binaries replaced by ecs binary
- [#171 - Update to twig 2.x](https://github.com/shopsys/shopsys/pull/171):
    - updated to Twig 2.4.8
    - all depracated calls has been fixed

### [shopsys/product-feed-interface]
#### Changed
- [#143 - Shopsys framework now uses latest version of Shopsys coding standards](https://github.com/shopsys/shopsys/pull/143) [Shopsys Coding Standards dev-master](./packages/coding-standards/) is now used
    - version of coding-standards package was updated to dev-master in [composer.json](./packages/product-feed-interface/composer.json)
    - modified [travis script](./packages/product-feed-interface/.travis.yml)
        - removed check for php 7.0 due to compatibility with ecs
        - php-cs-fixer, phpcs, phpmd binaries replaced by ecs binary

### [shopsys/product-feed-zbozi]
#### Changed
- [#143 - Shopsys framework now uses latest version of Shopsys coding standards](https://github.com/shopsys/shopsys/pull/143) [Shopsys Coding Standards dev-master](./packages/coding-standards/) is now used
    - version of coding-standards package was updated to dev-master in [composer.json](./packages/product-feed-zbozi/composer.json)
    - inline code skips moved to [autoload-easy-coding-standard.yml](./packages/product-feed-zbozi/autoload-easy-coding-standard.yml)
    - modified [travis script](./packages/product-feed-zbozi/.travis.yml)
        - removed check for php 7.0 due to compatibility with ecs
        - php-cs-fixer, phpcs, phpmd binaries replaced by ecs binary
- [#171 - Update to twig 2.x](https://github.com/shopsys/shopsys/pull/171):
    - updated to Twig 2.4.8
    - all depracated calls has been fixed

## [v7.0.0-alpha2] - 2018-05-24
### [shopsys/framework]
#### Added
- [#74 - Export personal information](https://github.com/shopsys/shopsys/pull/74):
    - Countries have code in ISO 3166-1 alpha-2
    - admin: added site content and email template for personal data export
- [#95 - Entity name resolving in EntityManager, QueryBuilders and Repositories](https://github.com/shopsys/shopsys/pull/95):
    - extended glass-box model entities are now used instead of their parent entities in EntityManager and QueryBuilders
        - this removes the need to manually override all repositories that work with extended entities
        - the functionality is automatically tested in [shopsys/project-base](https://github.com/shopsys/project-base)
            - see `\Tests\ShopBundle\Database\EntityExtension\EntityExtensionTest`
- [#107 - Entities by factories](https://github.com/shopsys/shopsys/pull/107):
    - entities are created by factories
        - allowing override factory that creates extended entities in project-base

#### Changed
- [#102 - Protected visibility of all private properties and methods of facades](https://github.com/shopsys/shopsys/pull/102):
    - visibility of all private properties and methods of repositories of entities was changed to protected
        - there are changed only repositories of entities because currently there was no need for extendibility of other repositories
        - protected visibility allows overriding of behavior from projects
- [#116 - Visibility of properties and methods of DataFactories and Repositories is protected](https://github.com/shopsys/shopsys/pull/116)
    - visibility of all private properties and methods of DataFactories was changed to protected
        - protected visibility allows overriding of behavior from projects
- [#113 - terminology: expression "indexes" is used now instead of "indices"](https://github.com/shopsys/shopsys/pull/113)
    - unification of terminology - indices and indexes
        - there is only "indexes" expression used now
- [#103 - Defaultly rendered form types](https://github.com/shopsys/shopsys/pull/103):
    - `CustomerFormType`, `PaymentFormType` and `TransportFormType` are now all rendered using FormType classes and they
        are ready for extension from `project-base` side.
- [#70 - extraction of project-independent part of Shopsys\Environment](https://github.com/shopsys/shopsys/pull/70):
    - moved constants with types of environment from [shopsys/project-base](https://github.com/shopsys/project-base)
        - moved from `\Shopsys\Environment` to `\Shopsys\FrameworkBundle\Component\Environment\EnvironmentType`
- [#87 - service deprecations](https://github.com/shopsys/shopsys/pull/87):
    - service definition follows Symfony 4 autowiring standards (@EdoBarnas)
        - FQN is always used as service ID
    - usage of interfaces is preferred, if possible
    - all services are explicitly defined
        - services with common suffixes (`*Facade`, `*Repository` etc.) are auto-discovered
        - see `services.yml` for details
- [#91 - all exception interfaces are now Throwable](https://github.com/shopsys/shopsys/pull/91):
    - all exception interfaces are now Throwable
    - visibility of all private properties and methods of facades was changed to protected
        - protected visibility allows overriding of behavior from projects
- [#130 - License condition for turnover changed from 12 to 3 months](https://github.com/shopsys/shopsys/pull/130)

#### Fixed
- [#89 - choiceList values are prepared for js ChoiceToBooleanArrayTransformer](https://github.com/shopsys/shopsys/pull/89)
    - choiceList values are prepared for js Choice(s)ToBooleanArrayTransformer
        - fixed "The choices were not found" console js error in the params filter
- [relevant CHANGELOG.md files updated](https://github.com/shopsys/shopsys/commit/68d730ac9eed9f8cf29c843f89718194ad51b1da):
    - command `shopsys:server:run` for running PHP built-in web server for a chosen domain
- [#108 - demo entity extension](https://github.com/shopsys/shopsys/pull/108)
    - db indices for product name are now created for translations in all locales
    - `LoadDataFixturesCommand` - fixed the `--fixtures` option description

### [shopsys/project-base]
#### Added
- [#74 - Export personal information](https://github.com/shopsys/shopsys/pull/74):
    - frontend: added site for requesting personal data export [@stanoMilan]
- [#94 - Installation guide update](https://github.com/shopsys/shopsys/pull/94):
    - support for [native installation](https://github.com/shopsys/shopsys/blob/master/docs/installation/native-installation.md) of the application

#### Changed
- [#70 - extraction of project-independent part of Shopsys\Environment](https://github.com/shopsys/shopsys/pull/70):
    - moved constants with types of environment into [shopsys/framework](https://github.com/shopsys/framework)
    - moved from `\Shopsys\Environment` to `\Shopsys\FrameworkBundle\Component\Environment\EnvironmentType`
- [Dependency Injection strict mode is now enabled](https://github.com/shopsys/shopsys/commit/cdcb51268d56770ae460fe22b41cc09f51c4aab6) [@EdoBarnas]:
    - disables autowiring features that were removed in Symfony 4

#### Fixed
- [#92 - swiftmailer setting delivery_address renamed to delivery_addresses](https://github.com/shopsys/shopsys/pull/92):
    - swiftmailer setting `delivery_address` renamed to `delivery_addresses` as the former does not exist anymore in version 3.*
        - see https://github.com/symfony/swiftmailer-bundle/commit/5edfbd39eaefb176922a346c16b0ae3aaeec87e0
        - the new setting requires array instead of string so the parameter `mailer_master_email_address` is wrapped into array in config
- [`FpJsFormValidator` error in console on FE order pages](https://github.com/shopsys/shopsys/commit/fbadde0966e92941dd470591d6a8a4924a798aa8)
- [failure during Docker image build triggered by `E: Unable to locate package postgresql-client-9.5`](https://github.com/shopsys/shopsys/pull/110)

#### Removed
- [#94 - Installation guide update](https://github.com/shopsys/shopsys/pull/94):
    - support of installation using Docker for Windows 10 Home and lower
    - virtualization is extremely slow, native installation has much better results in such case

### [shopsys/shopsys]
#### Added
- [#108 - demo entity extension](https://github.com/shopsys/shopsys/pull/108):
    - [cookbook](docs/cookbook/adding-new-attribute-to-an-entity.md) for adding new attribute to an entity

#### Changed
- [#128 - CHANGELOG.md new format](https://github.com/shopsys/shopsys/pull/128)
- [#110 - PHP-FPM Docker image tweaked for easier usage](https://github.com/shopsys/shopsys/pull/110):
    - PHP-FPM Docker image tweaked for easier usage
    - switched to Docker image `php:7.2-fpm-alpine` instead of `phpdockerio/php72-fpm:latest`
            - official PHP Docker image is much more stable and provides tags other than `latest`
            - built on Alpine linux which uses `apk` instead of `apt-get`
            - in the container there is no `bash` installed, use `sh` instead
    - all installation guides verified and tweaked
        - Docker installation supported on Linux, MacOS and Windows 10 Pro and higher (recommended way of installing the application)
        - native installation is also supported (recommended on Windows 10 Home and lower)
    - as a rule, using minor versions of docker images (eg. `1.2` or `1.2-alpine`) if possible
    - docs and `docker-compose.yml` templates reflect [changes of Docker images in shopsys/project-base]
    - `docker-compose-win.yml.dist` created for Windows OS which creates local volume because of permission problems with
        `postgresql` mounting
    - docs: changed `./phing` instruction code with `php phing` to make it work with all operating systems

#### Fixed
- [#117 - documentation: missing redis extension in required php extensions](https://github.com/shopsys/shopsys/pull/117) [@pk16011990]
- [#124 - Admin: Customer cannot be saved + fixed js error from administration console](https://github.com/shopsys/shopsys/pull/124):
    - admin: e-mail validation in customer editation is working correctly now

### [shopsys/http-smoke-testing]
#### Added
- [Troubleshooting section in `README.md` with explanation why tests do not fail on non-existing routes](https://github.com/shopsys/http-smoke-testing/commit/8f700eda96c2f6e1b018e56f5b03a46d09b4ae00)

### [shopsys/product-feed-google]
#### Changed
- [#116 - Visibility of properties and methods of DataFactories and Repositories is protected](https://github.com/shopsys/shopsys/pull/116):
    - visibility of all private properties and methods of repositories of entities was changed to protected
        - there are changed only repositories of entities because currently there was no need for extendibility of other repositories
        - protected visibility allows overriding of behavior from projects
- [Doctrine entities are used for storing data instead of using `DataStorageProviderInterface`](https://github.com/shopsys/shopsys/commit/3f32f513276f112d8ef4bdf854e413829bcf80f8)
    - previously saved data will be migrated
- [#102 - Protected visibility of all private properties and methods of facades](https://github.com/shopsys/shopsys/pull/102):
    - visibility of all private properties and methods of facades was changed to protected
        - protected visibility allows overriding of behavior from projects

### [shopsys/product-feed-heureka]
#### Changed
- [#116 - Visibility of properties and methods of DataFactories and Repositories is protected](https://github.com/shopsys/shopsys/pull/116):
    - visibility of all private properties and methods of repositories of entities was changed to protected
        - there are changed only repositories of entities because currently there was no need for extendibility of other repositories
        - protected visibility allows overriding of behavior from projects
- [Doctrine entities are used for storing data instead of using `DataStorageProviderInterface`](https://github.com/shopsys/shopsys/commit/3f32f513276f112d8ef4bdf854e413829bcf80f8)
    - previously saved data will be migrated
- [#102 - Protected visibility of all private properties and methods of facades](https://github.com/shopsys/shopsys/pull/102):
    - visibility of all private properties and methods of facades was changed to protected
        - protected visibility allows overriding of behavior from projects

### [shopsys/product-feed-zbozi]
#### Changed
- [#116 - Visibility of properties and methods of DataFactories and Repositories is protected](https://github.com/shopsys/shopsys/pull/116):
    - visibility of all private properties and methods of repositories of entities was changed to protected
        - there are changed only repositories of entities because currently there was no need for extendibility of other repositories
        - protected visibility allows overriding of behavior from projects
- [Doctrine entities are used for storing data instead of using `DataStorageProviderInterface`](https://github.com/shopsys/shopsys/commit/3f32f513276f112d8ef4bdf854e413829bcf80f8)
    - previously saved data will be migrated
- [#102 - Protected visibility of all private properties and methods of facades](https://github.com/shopsys/shopsys/pull/102):
    - visibility of all private properties and methods of facades was changed to protected
        - protected visibility allows overriding of behavior from projects

## [v7.0.0-alpha1] - 2018-04-12
- We are releasing version 7 (open-source project known as Shopsys Framework) to better distinguish it from Shopsys 6
  (internal platform of Shopsys company) and older versions that we have been developing and improving for 15 years.

### [shopsys/framework]
#### Added
- extracted core functionality of [Shopsys Framework](http://www.shopsys-framework.com/)
from its open-box repository [shopsys/project-base](https://github.com/shopsys/project-base)
    - this will allow the core to be upgraded via `composer update` in different project implementations
    - core functionality includes:
        - all Shopsys-specific Symfony commands
        - model and components with business logic and their data fixtures
        - Symfony controllers with form definitions, Twig templates and all javascripts of the web-based administration
        - custom form types, form extensions and twig extensions
        - compiler passes to allow basic extensibility with plugins (eg. product feeds)
    - this is going to be a base of a newly built architecture of [Shopsys Framework](http://www.shopsys-framework.com/)
- styles related to admin extracted from [shopsys/project-base](https://github.com/shopsys/project-base) package
    - this will allow styles to be upgraded via `composer update` in project implementations
- glass-box model entities are now extensible from project-base without changing the framework code
    - the entity extension is a work in progress
    - currently it would require you to override a lot of classes to use the extended entities instead of the parents
- [Shopsys Community License](https://github.com/shopsys/framework/blob/master/LICENSE)

#### Changed
- configuration of form types in administration is enabled using form type options
    -  following form types configured using options:
        - VatSettingsFormType
        - SliderItemFormType
        - ShopInfoSettingFormType
        - SeoSettingFormType
        - MailSettingFormType
        - LegalConditionsSettingFormType
        - HeurekaShopCertificationFormType
        - CustomerCommunicationFormType
        - CookiesSettingFormType
        - CategoryFormType
        - ArticleFormType
        - AdvertFormType
        - AdministratorFormType

### [shopsys/http-smoke-testing]
#### Changed
- added support of phpunit/phpunit ^6.0 and ^7.0 (@simara-svatopluk)

### [shopsys/product-feed-google]
#### Changed
- renamed [`TestStandardFeedItem`] to [`TestGoogleStandardFeedItem`]
- updated phpunit/phpunit to version 7

### [shopsys/product-feed-heureka]
#### Changed
- renamed [`TestStandardFeedItem`] to [`TestHeurekaStandardFeedItem`]
- updated phpunit/phpunit to version 7

### [shopsys/product-feed-zbozi]
#### Changed
- renamed [`TestStandardFeedItem`] to [`TestZboziStandardFeedItem`]
- updated phpunit/phpunit to version 7

### [shopsys/product-feed-interface]
#### Removed
- `HeurekaCategoryNameProviderInterface` as it is specific to Heureka product feed
   - [shopsys/product-feed-heureka](https://github.com/shopsys/product-feed-heureka) manages Heureka categories on its own since v0.5.0

### [shopsys/monorepo-tools]
#### Added
- bash scripts for building and splitting monolithic repository from existing packages
    - scripts are designed to be re-used even if different use-cases

### [shopsys/migrations]
#### Changed
- updated phpunit/phpunit to version 7
- DB migrations are installed from all registered bundles
    - they should be located in directory "Migrations" in the root of the bundle
- command `shopsys:migrations:generate` now offers a list of available bundles for generating the migrations

### [shopsys/plugin-interface]
#### Removed
- storing data via Data storage was removed
    - removed interfaces `PluginDataStorageProviderInterface` and `DataStorageInterface`
    - only Doctrine entities are further allowed for storing data

### [shopsys/shopsys]
#### Added
- basic infrastructure so that the monorepo can be installed and used as standard application
    - for details see [the Monorepo article](./docs/introduction/monorepo.md#infrastructure) in documentation
- [Shopsys Community License](./LICENSE)
- documentation was moved from [shopsys/project-base](https://github.com/shopsys/project-base)

### [shopsys/project-base]
#### Added
- Sessions are now stored in Redis
- Admin - Legal conditions: added personal data settings
- Frontend site for requesting personal data information
    - Admin - added email template for personal data request
    - Frontend send email with link to personal data access site
- docs: new WIP documentation about working with glassbox
- docker: [`php-fpm/Dockerfile`](./project-base/docker/php-fpm/Dockerfile) added installation of `grunt-cli` to be able to run `grunt watch`
    - [`docker-compose.yml.dist`](docker/conf/docker-compose.yml.dist) and [`docker-compose-mac.yml.dist`](docker/conf/docker-compose-mac.yml.dist): opened port 35729 for livereload, that is used by `grunt watch`

#### Changed
- `JavascriptCompilerService` can now compile javascript from more than one source directory
    - the compiler supports subdirectory `common` in addition to `admin` and  `frontend`
- **the core functionality was extracted to a separate repository [shopsys/framework](https://github.com/shopsys/framework)**
    - this will allow the core to be upgraded via `composer update` in different project implementations
    - core functionality includes:
        - all Shopsys-specific Symfony commands
        - model and components with business logic and their data fixtures
        - database migrations
        - Symfony controllers with form definitions, Twig templates and all javascripts of the web-based administration
        - custom form types, form extensions and twig extensions
        - compiler passes to allow basic extensibility with plugins (eg. product feeds)
    - this is going to be a base of a newly built architecture of [Shopsys Framework](http://www.shopsys-framework.com/)
    - translations are extracted from both this repository and the framework package during `phing dump-translations`
        - this is because the translations are located solely in this package
- styles related to admin extracted into [shopsys/framework](https://github.com/shopsys/framework) package
    - this will allow styles to be upgraded via `composer update` in project implementations
- grunt now compiles less files also from [shopsys/framework](https://github.com/shopsys/framework) package
- updated phpunit/phpunit to version 7
- phing target dump-translations does not delete messages, that are not found in translated directories
- docs updated in order to provide up-to-date information about the current project state
- installation guides: updated instructions for creating new project from Shopsys Framework sources
- basics-about-package-architecture.md updated to reflect current architecture state
- updated doctrine/doctrine-fixtures-bundle
    - all fixtures now use autowiring
- services that are not obtained directly from container in the application are not defined as public anymore
    - IntegrationTestingBundle was removed
    - all services that must be public because of tests moved to services_test.yml
    - unnecessary service obtaining from container replaced with autowiring
- new images for no image and empty cart
- **the license was changed from MIT to [Shopsys Community License](./LICENSE)**

#### Removed
- documentation was moved into the main [Shopsys repository](https://github.com/shopsys/shopsys/blob/master/docs)

## Before monorepo
Before we managed to implement monorepo for our packages, we had slightly different versions for each of our package, we had stored our packages on internal server so we dont have PR available.
That's why is this section formatted differently.

### [shopsys/http-smoke-testing]
#### [1.1.0](https://github.com/shopsys/http-smoke-testing/compare/v1.0.0...v1.1.0) - 2017-11-01
##### Added
- [CONTRIBUTING.md](https://github.com/shopsys/http-smoke-testing/blob/master/CONTRIBUTING.md)

##### Changed
- Improved IDE auto-completion when customizing test cases via [`RouteConfig`](https://github.com/shopsys/http-smoke-testing/blob/master/src/RouteConfig.php)
    - Methods `changeDefaultRequestDataSet()` and `addExtraRequestDataSet()` now return new interface [`RequestDataSetInterface`](https://github.com/shopsys/http-smoke-testing/blob/master/src/RequestDataSetConfig.php).
    - This new interface includes only a subset of methods in [`RequestDataSet`](https://github.com/shopsys/http-smoke-testing/blob/master/src/RequestDataSet.php) that is relevant to test case customization.

#### [1.0.1](https://github.com/shopsys/http-smoke-testing/compare/v1.0.0...v1.0.1) - 2017-07-03
##### Added
- Unit test for RequestDataSetGenerator class
- This Changelog

#### [1.0.0] - 2017-05-23
##### Added
- Extracted HTTP smoke testing functionality from [Shopsys Framework](http://www.shopsys-framework.com/)
- `.travis.yml` file with Travis CI configuration

### [shopsys/product-feed-google]
#### [0.2.1](https://github.com/shopsys/product-feed-google/compare/v0.2.0...v0.2.1) - 2018-02-19
##### Fixed
- services.yml autodiscovery settings

#### [0.2.0](https://github.com/shopsys/product-feed-google/compare/v0.1.2...v0.2.0) - 2018-02-19
##### Changed
- services.yml updated to Symfony 3.4 best practices

#### [0.1.2](https://github.com/shopsys/product-feed-google/compare/v0.1.1...v0.1.2) - 2018-02-12
##### Fixed
- Fix availability value

#### [0.1.1](https://github.com/shopsys/product-feed-google/compare/v0.1.0...v0.1.1) - 2017-10-04
##### Added
- support for shopsys/plugin-interface 0.3.0
- support for shopsys/product-feed-interface 0.5.0

#### [0.1.0] - 2017-09-25
##### Added
- added basic logic of product feed for Google
- composer.json: added shopsys/coding-standards into require-dev

### [shopsys/product-feed-heureka]
#### [0.6.1](https://github.com/shopsys/product-feed-heureka/compare/v0.6.0...v0.6.1) - 2018-02-19
##### Changed
- updated package shopsys/form-types-bundle to version 0.2.0

#### [0.6.0](https://github.com/shopsys/product-feed-heureka/compare/v0.5.1...v0.6.0) - 2018-02-19
##### Changed
- services.yml updated to Symfony 3.4 best practices

#### [0.5.1](https://github.com/shopsys/product-feed-heureka/compare/v0.5.0...v0.5.1) - 2017-10-06
- names of Heureka categories are now cached by category ID in [`HeurekaFeedConfig`](./packages/product-feed-heureka/src/HeurekaFeedConfig.php)

#### [0.5.0](https://github.com/shopsys/product-feed-heureka/compare/v0.4.2...v0.5.0) - 2017-10-05
##### Added
- logic of Heureka categorization moved from [Shopsys Framework](https://www.shopsys-framework.com/) core repository
    - Heureka categories are downloaded everyday via CRON module
    - extends CRUD of categories for assigning Heureka categories to categories on your online store
    - contains demo data fixtures

#### [0.4.2](https://github.com/shopsys/product-feed-heureka/compare/v0.4.1...v0.4.2) - 2017-10-05
##### Added
- support for shopsys/plugin-interface 0.3.0
- support for shopsys/product-feed-interface 0.5.0

#### [0.4.1](https://github.com/shopsys/product-feed-heureka/compare/v0.4.0...v0.4.1) - 2017-09-25
##### Added
- [CONTRIBUTING.md](https://github.com/shopsys/product-feed-heureka/blob/master/CONTRIBUTING.md)
##### Changed
- Dependency [product-feed-interface](https://github.com/shopsys/product-feed-interface) upgraded from ~0.3.0 to ~0.4.0
- [`HeurekaFeedConfig`](https://github.com/shopsys/product-feed-heureka/blob/master/src/HeurekaFeedConfig.php) now filters not sellable products
- [`HeurekaFeedConfig`](https://github.com/shopsys/product-feed-heureka/blob/master/src/HeurekaFeedConfig.php) implemented method `getAdditionalData()`
- [`TestStandardFeedItem`](https://github.com/shopsys/product-feed-heureka/blob/master/tests/TestStandardFeedItem.php) implemented method `getCurrencyCode()`

#### [0.4.0](https://github.com/shopsys/product-feed-heureka/compare/v0.3.0...v0.4.0) - 2017-09-12
##### Added
- New dependencies for dev
    - phpunit/phpunit 5.7.21
    - twig/twig 1.34.0
    - twig/extensions 1.3.0
- New automatic test that is controlling right behavior of plugin
- Added travis build icon into [README.md](https://github.com/shopsys/product-feed-heureka/blob/master/README.md)
##### Changed
- Dependency [shopsys/product-feed-interface] upgraded from ~0.2.0 to ~0.3.0
##### Removed
- `HeurekaFeedConfig::getFeedItemRepository()`

#### [0.3.0](https://github.com/shopsys/product-feed-heureka/compare/v0.2.0...v0.3.0) - 2017-08-09
##### Added
- This Changelog
- UPGRADE.md
- Plugin demo data (cpc for 2 domains)
##### Changed
- Dependency [shopsys/plugin-interface] upgraded from ~0.1.0 to ~0.2.0

#### [0.2.0](https://github.com/shopsys/product-feed-heureka/compare/v0.1.0...v0.2.0) - 2017-08-02
##### Added
- Retrieving custom plugin data
    - Heureka category names
    - MAX_CPC (Maximum price per click)
- Extension of product form with custom field for MAX_CPC
- New dependencies
    - [shopsys/plugin-interface ~0.1.0](https://github.com/shopsys/plugin-interface)
    - [shopsys/form-types-bundle ~0.1.0](https://github.com/shopsys/form-types-bundle)
    - [symfony/form ^3.0](https://github.com/symfony/form)
    - [symfony/translation ^3.0](https://github.com/symfony/translation)
    - [symfony/validator ^3.0](https://github.com/symfony/validator)
##### Changed
- Dependency [shopsys/product-feed-interface] upgraded from ~0.1.0 to ~0.2.0

### [shopsys/product-feed-zbozi]
#### [0.5.0](https://github.com/shopsys/product-feed-zbozi/compare/v0.4.2...v0.5.0) - 2018-02-19
##### Changed
- services.yml updated to Symfony 3.4 best practices
- updated shopsys/form-types-bundle to version 0.2.0

#### [0.4.2](https://github.com/shopsys/product-feed-zbozi/compare/v0.4.1...v0.4.2) - 2017-10-04
##### Added
- support for shopsys/plugin-interface 0.3.0
- support for shopsys/product-feed-interface 0.5.0

#### [0.4.1](https://github.com/shopsys/product-feed-zbozi/compare/v0.4.0...v0.4.1) - 2017-09-25
##### Added
- [CONTRIBUTING.md](https://github.com/shopsys/product-feed-zbozi/blob/master/CONTRIBUTING.md)
##### Changed
- Dependency [shopsys/product-feed-interface] upgraded from ~0.3.0 to ~0.4.0
- [`ZboziFeedConfig`](https://github.com/shopsys/product-feed-zbozi/blob/master/src/ZboziFeedConfig.php) now filters not sellable products
- [`ZboziFeedConfig`](https://github.com/shopsys/product-feed-zbozi/blob/master/src/ZboziFeedConfig.php) implemented method `getAdditionalData()`
- [`TestStandardFeedItem`](https://github.com/shopsys/product-feed-zbozi/blob/master/tests/TestStandardFeedItem.php) implemented method `getCurrencyCode()`

#### [0.4.0](https://github.com/shopsys/product-feed-zbozi/compare/v0.3.0...v0.4.0) - 2017-09-12
##### Added
- New dependencies for dev
    - phpunit/phpunit >=5.0.0,<6.0
    - twig/twig 1.34.0
    - twig/extensions 1.3.0
- New automatic test that is controlling right behavior of plugin
- Added travis build icon into [README.md](https://github.com/shopsys/product-feed-zbozi/blob/master/README.md)
##### Changed
- Dependency [product-feed-interface](https://github.com/shopsys/product-feed-zbozi/blob/master/shopsys/product-feed-interface) upgraded from ~0.2.0 to ~0.3.0
##### Removed
- `ZboziFeedConfig::getFeedItemRepository()`

#### [0.3.0](https://github.com/shopsys/product-feed-zbozi/compare/v0.2.0...v0.3.0) - 2017-09-06
##### Added
- This Changelog
- UPGRADE.md
- Plugin demo data (cpc, cpc_search and show for 2 domains)
##### Changed
- Dependency [plugin-interface](https://github.com/shopsys/plugin-interface) upgraded from ~0.1.0 to ~0.2.0

#### [0.2.0](https://github.com/shopsys/product-feed-zbozi/compare/v0.1.0...v0.2.0) - 2017-08-08
##### Added
- Retrieving custom plugin data and extension of product form with custom fields
    - show (offer in feeds)
    - cpc (maximum price per click)
    - cpc_search (maximum price per click in offers)
- New dependencies
    - [shopsys/plugin-interface ~0.1.0](https://github.com/shopsys/plugin-interface)
    - [shopsys/form-types-bundle ~0.1.0](https://github.com/shopsys/form-types-bundle)
    - [symfony/form ^3.0](https://github.com/symfony/form)
    - [symfony/translation ^3.0](https://github.com/symfony/translation)
    - [symfony/validator ^3.0](https://github.com/symfony/validator)
##### Changed
- Dependency [product-feed-interface](https://github.com/shopsys/product-feed-zbozi/blob/master/shopsys/product-feed-interface) upgraded from ~0.1.0 to ~0.2.0

#### [0.1.0] - 2017-07-13
##### Added
- Extracted Zboží.cz product feed plugin from [Shopsys Framework](http://www.shopsys-framework.com/)
- `.travis.yml` file with Travis CI configuration

### [shopsys/product-feed-heureka-delivery]
#### [0.3.0](https://github.com/shopsys/product-feed-heureka-delivery/compare/v0.2.2...v0.3.0) - 2018-02-19
##### Changed
- services.yml updated to Symfony 3.4 best practices
- updated phpunit/phpunit to version 7

#### [0.2.2](https://github.com/shopsys/product-feed-heureka-delivery/compare/v0.2.1...v0.2.2) - 2017-10-04
##### Added
- support for [shopsys/product-feed-interface] 0.5.0

#### [0.2.1](https://github.com/shopsys/product-feed-heureka-delivery/compare/v0.2.0...v0.2.1) - 2017-09-25
##### Added
- [CONTRIBUTING.md](https://github.com/shopsys/product-feed-heureka-delivery/blob/master/CONTRIBUTING.md)

##### Changed
- Dependency [shopsys/product-feed-interface] upgraded from ~0.3.0 to ~0.4.0

#### [0.2.0](https://github.com/shopsys/product-feed-heureka-delivery/compare/v0.1.1...v0.2.0) - 2017-09-12
##### Added
- This Changelog (@vitek-rostislav)
- New dependencies for dev(@MattCzerner)
    - phpunit/phpunit >=5.0.0,<6.0
    - twig/twig 1.34.0
    - twig/extensions 1.3.0
- New automatic test that is controlling right behavior of plugin
- Added travis build icon into [README.md](https://github.com/shopsys/product-feed-heureka-delivery/blob/master/README.md)
##### Changed
- Dependency [plugin-interface](https://github.com/shopsys/product-feed-interface) upgraded from ~0.2.0 to ~0.3.0
##### Removed
- `HeurekaDeliveryFeedConfig::getFeedItemRepository()`

#### [0.1.1](https://github.com/shopsys/product-feed-heureka-delivery/compare/v0.1.0...v0.1.1) - 2017-08-18
##### Fixed
- Usage of `FeedItemInterface::getId()` method in `feed.xml.twig`
    - it was renamed from `FeedItemInterface::getItemId()` in [shopsys/product-feed-interface](https://github.com/shopsys/product-feed-interface) v0.2.0

#### [0.1.0] - 2017-08-10
##### Added
- Extracted Heureka product delivery feed plugin from [Shopsys Framework](http://www.shopsys-framework.com/)
- `.travis.yml` file with Travis CI configuration

#### [0.1.0] - 2017-07-13
##### Added
- Extracted Heureka product feed plugin from [Shopsys Framework](http://www.shopsys-framework.com/)
- `.travis.yml` file with Travis CI configuration

### [shopsys/migrations]
#### [2.3.0](https://github.com/shopsys/migrations/compare/v2.2.0...v2.3.0 ) - 2018-02-19
##### Changed
- services.yml updated to Symfony 3.4 best practices

### [shopsys/product-feed-interface]
#### [0.5.0](https://github.com/shopsys/product-feed-interface/compare/v0.4.0...v0.5.0) - 2017-10-04
- [`StandardFeedItemInterface`](src/StandardFeedItemInterface.php) contains ID of its main category

#### [0.4.0](https://github.com/shopsys/product-feed-interface/compare/v0.3.0...v0.4.0) - 2017-09-25
##### Added
- [CONTRIBUTING.md](CONTRIBUTING.md)
- [template for github pull requests](docs/PULL_REQUEST_TEMPLATE.md)
- [`StandardFeedItemInterface`](./packages/product-feed-interface/src/StandardFeedItemInterface.php) has new method `isSellingDenied()`
- [`FeedConfigInterface`](./packages/product-feed-interface/src/FeedConfigInterface.php) has new method `getAdditionalInformation()`
- [`StandardFeedItemInterface`](./packages/product-feed-interface/src/StandardFeedItemInterface.php) has new method `getCurrencyShortcut()`

#### [0.3.0](https://github.com/shopsys/product-feed-interface/compare/v0.2.1...v0.3.0) - 2017-09-12
##### Added
- This Changelog
- UPGRADE.md
##### Removed
- `FeedItemRepositoryInterface`
- `FeedConfigInterface::getFeedItemRepository()`

#### [0.2.1](https://github.com/shopsys/product-feed-interface/compare/v0.2.0...v0.2.1) - 2017-08-17
##### Added
- New interface for delivery feed items - `DeliveryFeedItemInterface`

#### [0.2.0](https://github.com/shopsys/product-feed-interface/compare/v0.1.0...v0.2.0) - 2017-08-07
##### Changed
- `FeedItemInterface`: renamed method `getItemId()` to `getId()`
- `FeedItemCustomValuesProviderInterface` renamed to `HeurekaCategoryNameProviderInterface`
##### Removed
- General data storage functionality extracted into separate package [shopsys/plugin-interface](https://github.com/shopsys/plugin-interface)
    - removed `FeedItemCustomValuesProviderInterface::getCustomValuesForItems()` and `FeedItemCustomValuesInterface`

#### [0.1.0] - 2017-07-13
##### Added
- Package of interfaces providing compatibility between [Shopsys Framework](https://www.shopsys-framework.com) and product feed plugins.
- `.travis.yml` file with Travis CI configuration

### [shopsys/plugin-interface]
#### [0.3.0](https://github.com/shopsys/plugin-interface/compare/v0.2.0...v0.3.0) - 2017-10-04
##### Added
 - [CONTRIBUTING.md](https://github.com/shopsys/plugin-interface/blob/master/CONTRIBUTING.md)
 - `DataStorageInterface` can return all saved data via `getAll()`
 - `IteratedCronModuleInterface` and `SimpleCronModuleInterface`

#### [0.2.0](https://github.com/shopsys/plugin-interface/compare/v0.1.0...v0.2.0) - 2017-09-06
##### Added
 - This Changelog
 - interface for loading plugin's demo data
     - `PluginDataFixtureInterface`

#### [0.1.0] - 2017-08-04
##### Added
 - Package of interfaces providing compatibility between [Shopsys Framework](https://www.shopsys-framework.com) and plugins
     - interfaces responsible for retrieving and saving plugin custom data
         - `DataStorageInterface`
         - `PluginDataStorageProviderInterface`
     - interface responsible for extending CRUD with plugin custom sub-forms
         - `PluginCrudExtensionInterface`
 - `.travis.yml` file with Travis CI configuration

### [shopsys/project-base]
#### [6.0.0-beta21] - 2018-03-05
- released only in closed beta
##### Added
- PHPStan support (@mhujer)
    - currently analysing source code by level 0
- PHP 7.2 support
- Uniformity of PHP and Postgres timezones is checked during the build
- in `TEST` environment `Domain` is created with all instances of `DomainConfig` having URL set to `%overwrite_domain_url%`
    - parameter is set only in `parameters_test.yml` as it is only relevant in `TEST` environment
    - overwriting can be switched off by setting the parameter to `~` (null in Yaml)
    - overwriting the domain URL is necessary for Selenium acceptance tests running in Docker
- LegalConditionsSetting: added privacy policy article selection
    - customers need to agree with privacy policy while registring, sending contact form and completing order process
- SubscriptionFormType: added required privacy policy agreement checkbox
- subscription form: added link to privacy policy agreement article
- NewsletterController now exports date of subscription to newsletter
- `services_command.yml` to set Commands as services
- [docker-troubleshooting.md](https://github.com/shopsys/shopsys/blob/master/docs/docker/docker-troubleshooting.md): added to help developers with common problems that occurs using docker for development
- Newsletter subscriber is distinguished by domain
    - Admin: E-mail newsletter now exports e-mails to csv for each domain separatedly
- DatabaseSearching: added getFullTextLikeSearchString()
- admin: E-mail newsletter: now contains list of registered e-mails with ability to delete them

##### Changed
- cache is cleared before PHPUnit tests only when run via [Console Commands for Application Management (Phing Targets)](https://github.com/shopsys/shopsys/blob/master/docs/introduction/console-commands-for-application-management-phing-targets.md), not when run using `phpunit` directly
- PHPUnit tests now fail on warning
- end of support of PHP 7.0
- renamed TermsAndCondition to LegalCondition to avoid multiple classes for legal conditions agreements
- emails with empty subject or body are no longer sent
- postgresql-client is installed in [php-fpm/dockerfile](./project-base/docker/php-fpm/Dockerfile) for `pg_dump` function
    - postgresql was downgraded to 9.5 because of compatibility with postgresql-client
- docker-compose: added container_name to smtp-server and adminer
- configuration of Docker Compose tweaked for easier development
    - `docker-compose.yml` is added to `.gitignore` for everyone to be able to make individual changes
    - the predefined templates are now in `/docker/conf` directory
    - `adminer` container uses port 1100 by default (as 1000 is often already in use)
    - Docker Sync is used only in configuration for MacOS as only there it is needed
    - `postgres` container is created with a volume for data persistence (in `var/postgres-data`)
    - see documentation of [Installation Using Docker](https://github.com/shopsys/shopsys/blob/master/docs/installation/installation-using-docker.md) for details
- default parameters in `parameters.yml.dist` and `parameters_test.yml.dist` are for Docker installation (instead of native)
- Front/NewsletterController: extracted duplicit rendering and add return typehints
- Symfony updated to version 3.4
    - autowiring is now done via Symfony PSR-4
    - services now use FQN as naming convention
    - services are private by default
    - inlined services (called via container) are set to public
    - services required by another service are defined in services.yml (e.g. Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorUserProvider: ~)
    - all inline calls of services changed to use FQN
    - services no longer required in services.yml have been removed
    - services instanced after DI container creation are set as synthetic
- users and administrators are logged out of all the sessions except the current one on password change (this is required in Symfony 4)
- running Phing without parameter now shows list of available targets instead of building application
- updated presta/sitemap-bundle to version 1.5.2 in order to avoid deprecated calls
 - updated SitemapListener to avoid using of deprecated SitemapListenerInterface
- updated symfony/swiftmailer-bundle to version 3.2.0 in order to fix deprecated calls
- all calls of Form::isValid() are called only on submitted forms in order to prevent deprecated call
- symlink so root/bin acts like root/project-base/bin
- all commands are now services, that are lazy loaded with autowired dependencies
- NewsletterFacadeTest: renamed properties to match class name

##### Fixed
- `BrandFacade::create()` now generates friendly URL for all domains (@sspooky13)
- `Admin/HeurekaController::embedWidgetAction()` moved to new `Front/HeurekaController` as the action is called in FE template
- PHPUnit tests do not fail on Windows machine with PHP 7.0 because of excessively long file paths
- customizeBundle.js: on-submit actions are no longer triggered when form validation error occurs
- fixed google product feed availability values by updating it to v0.1.2
- reloading of order preview now calls `Shopsys.register.registerNewContent()` (@petr.kadlec)
- CurrentPromoCodeFacace: promo code is not searched in database if code is empty (@petr.kadlec)
- CategoryRepository::getCategoriesWithVisibleChildren() checks visibility of children (@petr.kadlec)
- added missing migration for privacy policy article
- OrderStatusFilter: show names in labels instead of ids
- legal conditions text in order 3rd step is not HTML escaped anymore
- product search now does not cause 500 error when the search string ends with backslash

##### Removed
- PHPStorm Inspect is no longer used for static analysis of source code
- Phing targets standards-ci and standards-ci-diff because they were redundant to standards and standards-diff targets
- deprecated packages `symplify/controller-autowire` and `symplify/default-autowire`

#### [6.0.0-beta20] - 2017-12-11
- released only in closed beta

##### Changed
- Docker `nginx.conf` has been upgraded with better performance settings
    - JavaScript and CSS files are compressed with GZip
    - static content has cache headers set in order to leverage browser cache
##### Fixed
- miscellaneous annotations, typos and other minor fixes (@petr.kadlec)
- `CartController::addProductAction()`: now uses `Request` instance passed as the method argument (Symfony 3 style) instead of calling the base `Controller` method `getRequest()` (Symfony 2.x style) (@petr.kadlec)
    - see [Symfony upgrade log](https://github.com/symfony/symfony/blob/3.0/UPGRADE-3.0.md#frameworkbundle) for more information
- `ExecutionContextInterface::buildViolation()` (Symfony 3 style) is now used instead of `ExecutionContextInterface::addViolationAt()` (Symfony 2.x style) (@petr.kadlec)
    - see [Symfony upgrade log](https://github.com/symfony/symfony/blob/3.0/UPGRADE-3.0.md#validator) for more information

#### [6.0.0-beta19.2] - 2017-11-23
- released only in closed beta

##### Fixed
- updated symfony/symfony to v3.2.14 in order to avoid known security vulnerabilities

#### [6.0.0-beta19.1] - 2017-11-21
- released only in closed beta

##### Fixed
- coding standards check "phing standards" passes

#### [6.0.0-beta19] - 2017-11-21
- released only in closed beta

##### Added
- size of performance data fixtures and limits for performance testing are now configurable via parameters defined in [`parameters_common.yml`](./project-base/app/config/parameters_common.yml)
- performance tests report database query counts
- UserDataFixture: alias for SettingValueDataFixture to fix [PHP bug #66862](https://bugs.php.net/bug.php?id=66862)

##### Changed
- parameters that are in `parameters.yml` or `parameters_test.yml` that are not in their `.dist` templates are not removed during `composer install` anymore
- customer creating controllers are not catching exception for duplicate email, it is not necessary since it is done by UniqueEmail constraint now
- input "remember me" in login form is encapsulated by its label for better UX

#### [6.0.0-beta18] - 2017-10-19
- released only in closed beta

##### Added
- [coding standards documentation](https://github.com/shopsys/shopsys/blob/master/docs/contributing/coding-standards.md)
- acceptance tests asserting successful image upload in admin for product, transport and payment
- Docker based server stack for easier installation and development
    - see [Installation Using Docker](https://github.com/shopsys/shopsys/blob/master/docs/installation/installation-using-docker.md) for details
- plugins can now extend the CRUD of categories (using `CategoryFormType`)

##### Changed
- cache deletion before running unit tests is now done using `Symfony\Filesystem` instead of using console command
    - deleting via console command `cache:clear` is slow, because it creates whole application container first and then deletes all cache created in process
- Windows locales list: use more tolerant name for Czech locale
    - in Windows 2017 Fall Creators Update the locale name was changed from "Czech_Czech Republic" to "Czech_Czechia"
    - name "Czech" is acceptable in all Windows versions
- interfaces for CRON modules moved to [shopsys/plugin-interface](https://github.com/shopsys/plugin-interface)
- `ImageDemoCommand` now prompts to truncate "images" db table when it is not empty before new demo images are loaded

##### Deleted
- logic of Heureka categorization moved to [shopsys/product-feed-heureka](https://github.com/shopsys/product-feed-heureka)
    - all your current Heureka category data will be migrated into the new structure

##### Fixed
- proper `baseUrl` value from `domains_urls.yaml` is now stored into `settings` when creating new domain

#### [6.0.0-beta17] - 2017-10-03
- released only in closed beta

##### Added
- MIT license
- phing targets `eslint-check`, `eslint-check-diff`, `eslint-fix` and `eslint-fix-diff` to check and fix coding standards in JS files (@sspooky13)
    - executed as a part of targets `standards`, `standards-diff`, `standards-fix` and `standards-fix-diff`
- [product feed plugin for Google](https://github.com/shopsys/product-feed-google/)
- new article explaining [Basics About Package Architecture](https://github.com/shopsys/shopsys/blob/master/docs/introduction/basics-about-package-architecture.md)

##### Changed
- `StandardFeedItemRepository`: now selects available products instead of sellable, filtering of not sellable products is made in product plugins
- implementations of `StandardFeedItemInterface` now must have implemented methods `isSellingDenied()` and `getCurrencyCode()`
- implementations of `FeedConfigInterface` now must have implemented method `getAdditionalInformation()`

#### [6.0.0-beta16] - 2017-09-19
- released only in closed beta

##### Added
- new command `shopsys:plugin-data-fixtures:load` for loading demo data from plugins
    - called during build of demo database
- new documentation about Shopsys Framework model architecture
- `FeedItemRepositoryInterface`
    - moved from [shopsys/product-feed-interface](https://github.com/shopsys/product-feed-interface/)
- [template for github pull requests](https://github.com/shopsys/shopsys/blob/master/docs/PULL_REQUEST_TEMPLATE.md)

##### Changed
- dependency [shopsys/plugin-interface](https://github.com/shopsys/plugin-interface/) upgraded from 0.1.0 to 0.2.0
- dependency [shopsys/product-feed-heureka](https://github.com/shopsys/product-feed-heureka/) upgraded from 0.2.0 to 0.4.0
- dependency [shopsys/product-feed-zbozi](https://github.com/shopsys/product-feed-zbozi/) upgraded from 0.2.0 to 0.4.0
- dependency [shopsys/product-feed-heureka-delivery](https://github.com/shopsys/product-feed-heureka-delivery/) upgraded from 0.1.1 to 0.2.0
- dependency [shopsys/product-feed-interface](https://github.com/shopsys/product-feed-interface/) upgraded from 0.2.1 to 0.3.0
- it is no longer needed to redeclare feed plugin's implementations of `FeedConfigInterface` in `services.yml`
    - decision about providing proper instance of `FeedItemRepositoryInterface` is made in `FeedConfigFacade`
- FeedConfigRepository renamed to `FeedConfigRegistry`
    - it is not fetching data from Doctrine as other repositories, it only serves as a container for registering services of specific type
    - similar to `PluginDataFixtureRegistry` or `PluginCrudExtensionRegistry`
- `UknownPluginDataFixtureException` renamed to `UnknownPluginCrudExtensionTypeException` because of a typo
- `FeedConfigRegistry` now contains all FeedConfigs in one array (indexed by type)
    - definition and assertion of known feed configs types moved from [`RegisterProductFeedConfigsCompilerPass`](./src/Shopsys/ShopBundle/DependencyInjection/Compiler/RegisterProductFeedConfigsCompilerPass.php) to `FeedConfigRegistry`
    - changed message and arguments of `UnknownFeedConfigTypeException`
- renamed methods working with standard feeds only to be more expressive
    - renamed `FeedConfigFacade::getFeedConfigs()` to `getStandardFeedConfigs()`
    - renamed `FeedFacade::generateFeedsIteratively()` to `generateStandardFeedsIteratively()`
    - renamed `FeedGenerationConfigFactory::createAll()` to `createAllForStandardFeeds()`
- [`parameters.yml.dist`](./project-base/app/config/parameters.yml.dist): renamed parameter `email_for_error_reporting` to `error_reporting_email_to`
- sender email for error reporting is now configured in [`parameters.yml.dist`](./project-base/app/config/parameters.yml.dist)
- reimplemented `CategoriesType` (@Petr Heinz)
    - it now extends `CollectionType` instead of `ChoiceType`
    - it loads only those categories that are needed to show all selected categories in a tree, not all of them
    - collapsed categories can be loaded via AJAX
- `CategoryRepository::findById()` now uses `find()` method of Doctrine repository instead of query builder so it can use cached results
- it is possible to mention occurrences of an image size in [`images.yml`](./project-base/src/Shopsys/ShopBundle/Resources/config/images.yml)
    - previously they were directly in `ImageController`
    - they are not translatable anymore (too hard to maintain)

##### Removed
- email for error reporting removed from [`parameters_test.yml.dist`](./project-base/app/config/parameters_test.yml.dist)
- removed unused private properties from classes
- removed `CategoriesTypeTransformerFactory`
    - the `CategoriesTypeTransformer` can be fully autowired after deletion of `$domainId`

##### Fixed
- [`InlineEditPage::createNewRow()`](./project-base/tests/ShopBundle/Acceptance/acceptance/PageObject/Admin/InlineEditPage.php) now waits for AJAX to complete
    - fixes false negatives of acceptance test [`PromoCodeInlineEditCest::testPromoCodeCreate()`](./project-base/tests/ShopBundle/Acceptance/acceptance/PromoCodeInlineEditCest.php)

#### [6.0.0-beta15] - 2017-08-31
- previous beta versions released only internally (mentioned changes since 6.0.0-alpha)
- this version was released only in closed beta

##### Added
- PHP 7 support
- [a basic knowledgebase](https://github.com/shopsys/shopsys/blob/master/docs/index.md)
    - installation guide
    - guidelines for contributions
    - cookbooks
    - articles on automated testing

##### Changed
- update to Symfony 3
- PSR-2 compliance
- English as a main language
    - language of first front-end domain
    - language of administration
    - all translatable message sources in English

##### Deleted
- separation of HTTP smoke test module into a component:
    - https://github.com/shopsys/http-smoke-testing/
- separation of product feed modules into plugins:
    - https://github.com/shopsys/plugin-interface/
    - https://github.com/shopsys/product-feed-interface/
    - https://github.com/shopsys/product-feed-heureka/
    - https://github.com/shopsys/product-feed-heureka-delivery/
    - https://github.com/shopsys/product-feed-zbozi/

#### [6.0.0-alpha] - 2016-11-09
- developed since 2014-03-31
- used only as internal platform for e-commerce projects of Shopsys Agency
- released only internally

##### Added
- product catalogue
- registered customers
- basic orders management
- back-end administration
- front-end fulltext search
- front-end product filtering
- 3-step ordering process
- products variants
- simple promo codes
- product feeds for product aggregators
- basic CMS
- multiple administrators
- support for several currencies
- support for several languages
- support for several domains
- full friendly URL for main entities
- customizable SEO attributes for main entities

### [shopsys/coding-standards]
#### [4.0](https://github.com/shopsys/coding-standards/compare/v3.1.1...v4.0.0) - 2018-01-27
##### Added
- composer script `run-all-checks` for easier testing of the package (@TomasVotruba)

##### Changed
- `OrmJoinColumnRequireNullableFixer` marked as *risky* (@sustmi)
- [#11](https://github.com/shopsys/coding-standards/pull/11) dropped support of PHP 7.0
- [#12](https://github.com/shopsys/coding-standards/pull/12/) [EasyCodingStandard](https://github.com/Symplify/EasyCodingStandard) is now used (@TomasVotruba)
    - the tool encapsulates PHP-CS-Fixer and PHP_CodeSniffer
    - rules configuration is now unified in single file - [`easy-coding-standard.neon`](./packages/coding-standards/easy-coding-standard.neon)
    - the option `ignore-whitespace` for rules checking method and class length is not available anymore
        - the limits were increased to 550 (class length) and 60 (method length)

##### Removed
- PHP Mess Detector (@TomasVotruba)
- line length sniff (@TomasVotruba)

#### [3.1.1](https://github.com/shopsys/coding-standards/compare/v3.1.0...v3.1.1) - 2017-10-31
##### Fixed
- enabled custom fixers

#### [3.1.0](https://github.com/shopsys/coding-standards/compare/v3.0.2...v3.1.0) - 2017-10-12
##### Added
- This changelog
- [Description of used coding standards rules](./packages/coding-standards/docs/description-of-used-coding-standards-rules.md)
- New rules in [phpcs-fixer ruleset](./packages/coding-standards/build/phpcs-fixer.php_cs):
    - combine_consecutive_unsets
    - function_typehint_space
    - hash_to_slash_comment
    - lowercase_cast
    - native_function_casing
    - no_empty_comment
    - no_empty_phpdoc
    - no_spaces_around_offset
    - no_unneeded_control_parentheses
    - no_useless_return
    - no_whitespace_before_comma_in_array
    - non_printable_character
    - normalize_index_brace
    - phpdoc_annotation_without_dot
    - phpdoc_no_useless_inheritdoc
    - phpdoc_single_line_var_spacing
    - protected_to_private
    - semicolon_after_instruction
    - short_scalar_cast
    - space_after_semicolon
    - whitespace_after_comma_in_array

##### Changed
- friendsofphp/php-cs-fixer upgraded from version 2.1 to version 2.3
- [phpcs-fixer ruleset](./build/phpcs-fixer.php_cs)
    - replaced deprecated "hash_to_slash_comment" rule with "single_line_comment_style" rule
    - custom NoUnusedImportsFixer replaced with standard "no_unused_imports" rule

##### Deleted
- Redundant rules which were already covered by other rules

### [shopsys/form-types-bundle]
#### [0.2.0](https://github.com/shopsys/form-types-bundle/compare/v0.1.0...v0.2.0) - 2018-02-19
##### Added
- This Changelog
- [CONTRIBUTING.md](https://github.com/shopsys/form-types-bundle/blob/master/CONTRIBUTING.md)
##### Changed
- services.yml updated to Symfony 3.4 best practices

#### [0.1.0] - 2017-08-04
##### Added
- Custom form types extracted from [Shopsys Framework](http://www.shopsys-framework.com/), see [README](https://github.com/shopsys/form-types-bundle/blob/master/README.md) for more information
    - MultidomainType
    - YesNoType
- `.travis.yml` file with Travis CI configuration

[v7.0.0-beta4]: https://github.com/shopsys/shopsys/compare/v7.0.0-beta3...v7.0.0-beta4
[v7.0.0-beta3]: https://github.com/shopsys/shopsys/compare/v7.0.0-beta2...v7.0.0-beta3
[v7.0.0-beta2]: https://github.com/shopsys/shopsys/compare/v7.0.0-beta1...v7.0.0-beta2
[v7.0.0-beta1]: https://github.com/shopsys/shopsys/compare/v7.0.0-alpha6...v7.0.0-beta1
[v7.0.0-alpha6]: https://github.com/shopsys/shopsys/compare/v7.0.0-alpha5...v7.0.0-alpha6
[v7.0.0-alpha5]: https://github.com/shopsys/shopsys/compare/v7.0.0-alpha4...v7.0.0-alpha5
[v7.0.0-alpha4]: https://github.com/shopsys/shopsys/compare/v7.0.0-alpha3...v7.0.0-alpha4
[v7.0.0-alpha3]: https://github.com/shopsys/shopsys/compare/v7.0.0-alpha2...v7.0.0-alpha3
[v7.0.0-alpha2]: https://github.com/shopsys/shopsys/compare/v7.0.0-alpha1...v7.0.0-alpha2

[shopsys/shopsys]: https://github.com/shopsys/shopsys
[shopsys/project-base]: https://github.com/shopsys/project-base
[shopsys/framework]: https://github.com/shopsys/framework
[shopsys/product-feed-zbozi]: https://github.com/shopsys/product-feed-zbozi
[shopsys/product-feed-google]: https://github.com/shopsys/product-feed-google
[shopsys/product-feed-heureka]: https://github.com/shopsys/product-feed-heureka
[shopsys/product-feed-heureka-delivery]: https://github.com/shopsys/product-feed-heureka-delivery
[shopsys/product-feed-interface]: https://github.com/shopsys/product-feed-interface
[shopsys/plugin-interface]: https://github.com/shopsys/plugin-interface
[shopsys/coding-standards]: https://github.com/shopsys/coding-standards
[shopsys/http-smoke-testing]: https://github.com/shopsys/http-smoke-testing
[shopsys/form-types-bundle]: https://github.com/shopsys/form-types-bundle
[shopsys/migrations]: https://github.com/shopsys/migrations
[shopsys/monorepo-tools]: https://github.com/shopsys/monorepo-tools
[shopsys/google-cloud-bundle]: https://github.com/shopsys/google-cloud-bundle
[shopsys/read-model]: https://github.com/shopsys/read-model
[shopsys/backend-api]: https://github.com/shopsys/backend-api
[shopsys/frontend-api]: https://github.com/shopsys/frontend-api

[@pk16011990]: https://github.com/pk16011990
[@stanoMilan]: https://github.com/stanoMilan
[@EdoBarnas]: https://github.com/EdoBarnas
[@DavidKuna]: https://github.com/DavidKuna
[@lukaso]: https://github.com/lukaso
[@TomasVotruba]: https://github.com/TomasVotruba
[@drekbour]: https://github.com/drekbour
[@dominikkaluza]: https://github.com/dominikkaluza
[@jDolba]: https://github.com/jDolba

[#466]: https://github.com/shopsys/shopsys/pull/466
[#465]: https://github.com/shopsys/shopsys/pull/465
[#463]: https://github.com/shopsys/shopsys/pull/463
[#461]: https://github.com/shopsys/shopsys/pull/461
[#460]: https://github.com/shopsys/shopsys/pull/460
[#459]: https://github.com/shopsys/shopsys/pull/459
[#458]: https://github.com/shopsys/shopsys/pull/458
[#456]: https://github.com/shopsys/shopsys/pull/456
[#451]: https://github.com/shopsys/shopsys/pull/451
[#449]: https://github.com/shopsys/shopsys/pull/449
[#447]: https://github.com/shopsys/shopsys/pull/447
[#446]: https://github.com/shopsys/shopsys/pull/446
[#444]: https://github.com/shopsys/shopsys/pull/444
[#439]: https://github.com/shopsys/shopsys/pull/439
[#438]: https://github.com/shopsys/shopsys/pull/438
[#436]: https://github.com/shopsys/shopsys/pull/436
[#433]: https://github.com/shopsys/shopsys/pull/433
[#432]: https://github.com/shopsys/shopsys/pull/432
[#431]: https://github.com/shopsys/shopsys/pull/431
[#430]: https://github.com/shopsys/shopsys/pull/430
[#429]: https://github.com/shopsys/shopsys/pull/429
[#428]: https://github.com/shopsys/shopsys/pull/428
[#427]: https://github.com/shopsys/shopsys/pull/427
[#425]: https://github.com/shopsys/shopsys/pull/425
[#424]: https://github.com/shopsys/shopsys/pull/424
[#423]: https://github.com/shopsys/shopsys/pull/423
[#422]: https://github.com/shopsys/shopsys/pull/422
[#420]: https://github.com/shopsys/shopsys/pull/420
[#419]: https://github.com/shopsys/shopsys/pull/419
[#411]: https://github.com/shopsys/shopsys/pull/411
[#410]: https://github.com/shopsys/shopsys/pull/410
[#409]: https://github.com/shopsys/shopsys/pull/409
[#403]: https://github.com/shopsys/shopsys/pull/403
[#393]: https://github.com/shopsys/shopsys/pull/393
[#386]: https://github.com/shopsys/shopsys/pull/386
[#384]: https://github.com/shopsys/shopsys/pull/384
[#295]: https://github.com/shopsys/shopsys/pull/295
[#161]: https://github.com/shopsys/shopsys/pull/161
[#142]: https://github.com/shopsys/shopsys/pull/142
[@kulincz]: https://github.com/kulincz
[@Maybeway]: https://github.com/Maybeway
[#480]: https://github.com/shopsys/shopsys/pull/480
[#476]: https://github.com/shopsys/shopsys/pull/476
[#473]: https://github.com/shopsys/shopsys/pull/473
[#472]: https://github.com/shopsys/shopsys/pull/472
[#468]: https://github.com/shopsys/shopsys/pull/468
[#525]: https://github.com/shopsys/shopsys/pull/525
[#521]: https://github.com/shopsys/shopsys/pull/521
[#519]: https://github.com/shopsys/shopsys/pull/519
[#515]: https://github.com/shopsys/shopsys/pull/515
[#513]: https://github.com/shopsys/shopsys/pull/513
[#512]: https://github.com/shopsys/shopsys/pull/512
[#511]: https://github.com/shopsys/shopsys/pull/511
[#510]: https://github.com/shopsys/shopsys/pull/510
[#509]: https://github.com/shopsys/shopsys/pull/509
[#506]: https://github.com/shopsys/shopsys/pull/506
[#502]: https://github.com/shopsys/shopsys/pull/502
[#500]: https://github.com/shopsys/shopsys/pull/500
[#499]: https://github.com/shopsys/shopsys/pull/499
[#498]: https://github.com/shopsys/shopsys/pull/498
[#497]: https://github.com/shopsys/shopsys/pull/497
[#496]: https://github.com/shopsys/shopsys/pull/496
[#494]: https://github.com/shopsys/shopsys/pull/494
[#493]: https://github.com/shopsys/shopsys/pull/493
[#491]: https://github.com/shopsys/shopsys/pull/491
[#490]: https://github.com/shopsys/shopsys/pull/490
[#487]: https://github.com/shopsys/shopsys/pull/487
[#486]: https://github.com/shopsys/shopsys/pull/486
[@thirdknown]: https://github.com/thirdknown
[#625]: https://github.com/shopsys/shopsys/pull/625
[#620]: https://github.com/shopsys/shopsys/pull/620
[#618]: https://github.com/shopsys/shopsys/pull/618
[#617]: https://github.com/shopsys/shopsys/pull/617
[#614]: https://github.com/shopsys/shopsys/pull/614
[#613]: https://github.com/shopsys/shopsys/pull/613
[#608]: https://github.com/shopsys/shopsys/pull/608
[#606]: https://github.com/shopsys/shopsys/pull/606
[#605]: https://github.com/shopsys/shopsys/pull/605
[#602]: https://github.com/shopsys/shopsys/pull/602
[#601]: https://github.com/shopsys/shopsys/pull/601
[#600]: https://github.com/shopsys/shopsys/pull/600
[#598]: https://github.com/shopsys/shopsys/pull/598
[#595]: https://github.com/shopsys/shopsys/pull/595
[#593]: https://github.com/shopsys/shopsys/pull/593
[#592]: https://github.com/shopsys/shopsys/pull/592
[#587]: https://github.com/shopsys/shopsys/pull/587
[#582]: https://github.com/shopsys/shopsys/pull/582
[#581]: https://github.com/shopsys/shopsys/pull/581
[#580]: https://github.com/shopsys/shopsys/pull/580
[#578]: https://github.com/shopsys/shopsys/pull/578
[#576]: https://github.com/shopsys/shopsys/pull/576
[#575]: https://github.com/shopsys/shopsys/pull/575
[#574]: https://github.com/shopsys/shopsys/pull/574
[#573]: https://github.com/shopsys/shopsys/pull/573
[#570]: https://github.com/shopsys/shopsys/pull/570
[#568]: https://github.com/shopsys/shopsys/pull/568
[#566]: https://github.com/shopsys/shopsys/pull/566
[#564]: https://github.com/shopsys/shopsys/pull/564
[#563]: https://github.com/shopsys/shopsys/pull/563
[#560]: https://github.com/shopsys/shopsys/pull/560
[#559]: https://github.com/shopsys/shopsys/pull/559
[#558]: https://github.com/shopsys/shopsys/pull/558
[#557]: https://github.com/shopsys/shopsys/pull/557
[#556]: https://github.com/shopsys/shopsys/pull/556
[#554]: https://github.com/shopsys/shopsys/pull/554
[#553]: https://github.com/shopsys/shopsys/pull/553
[#552]: https://github.com/shopsys/shopsys/pull/552
[#551]: https://github.com/shopsys/shopsys/pull/551
[#547]: https://github.com/shopsys/shopsys/pull/547
[#546]: https://github.com/shopsys/shopsys/pull/546
[#545]: https://github.com/shopsys/shopsys/pull/545
[#544]: https://github.com/shopsys/shopsys/pull/544
[#543]: https://github.com/shopsys/shopsys/pull/543
[#542]: https://github.com/shopsys/shopsys/pull/542
[#541]: https://github.com/shopsys/shopsys/pull/541
[#540]: https://github.com/shopsys/shopsys/pull/540
[#538]: https://github.com/shopsys/shopsys/pull/538
[#535]: https://github.com/shopsys/shopsys/pull/535
[#534]: https://github.com/shopsys/shopsys/pull/534
[#533]: https://github.com/shopsys/shopsys/pull/533
[#530]: https://github.com/shopsys/shopsys/pull/530
[#528]: https://github.com/shopsys/shopsys/pull/528
[#527]: https://github.com/shopsys/shopsys/pull/527
[#524]: https://github.com/shopsys/shopsys/pull/524
[#522]: https://github.com/shopsys/shopsys/pull/522
[#503]: https://github.com/shopsys/shopsys/pull/503
[@ondrejmirtes]: https://github.com/ondrejmirtes
[@henzigo]: https://github.com/henzigo
[#635]: https://github.com/shopsys/shopsys/pull/635
[#616]: https://github.com/shopsys/shopsys/pull/616
[#735]: https://github.com/shopsys/shopsys/pull/735
[#733]: https://github.com/shopsys/shopsys/pull/733
[#729]: https://github.com/shopsys/shopsys/pull/729
[#723]: https://github.com/shopsys/shopsys/pull/723
[#719]: https://github.com/shopsys/shopsys/pull/719
[#716]: https://github.com/shopsys/shopsys/pull/716
[#712]: https://github.com/shopsys/shopsys/pull/712
[#710]: https://github.com/shopsys/shopsys/pull/710
[#709]: https://github.com/shopsys/shopsys/pull/709
[#708]: https://github.com/shopsys/shopsys/pull/708
[#707]: https://github.com/shopsys/shopsys/pull/707
[#706]: https://github.com/shopsys/shopsys/pull/706
[#705]: https://github.com/shopsys/shopsys/pull/705
[#703]: https://github.com/shopsys/shopsys/pull/703
[#702]: https://github.com/shopsys/shopsys/pull/702
[#699]: https://github.com/shopsys/shopsys/pull/699
[#693]: https://github.com/shopsys/shopsys/pull/693
[#692]: https://github.com/shopsys/shopsys/pull/692
[#690]: https://github.com/shopsys/shopsys/pull/690
[#688]: https://github.com/shopsys/shopsys/pull/688
[#687]: https://github.com/shopsys/shopsys/pull/687
[#685]: https://github.com/shopsys/shopsys/pull/685
[#679]: https://github.com/shopsys/shopsys/pull/679
[#676]: https://github.com/shopsys/shopsys/pull/676
[#674]: https://github.com/shopsys/shopsys/pull/674
[#673]: https://github.com/shopsys/shopsys/pull/673
[#660]: https://github.com/shopsys/shopsys/pull/660
[#659]: https://github.com/shopsys/shopsys/pull/659
[#656]: https://github.com/shopsys/shopsys/pull/656
[#655]: https://github.com/shopsys/shopsys/pull/655
[#651]: https://github.com/shopsys/shopsys/pull/651
[#650]: https://github.com/shopsys/shopsys/pull/650
[#649]: https://github.com/shopsys/shopsys/pull/649
[#647]: https://github.com/shopsys/shopsys/pull/647
[#646]: https://github.com/shopsys/shopsys/pull/646
[#645]: https://github.com/shopsys/shopsys/pull/645
[#644]: https://github.com/shopsys/shopsys/pull/644
[#642]: https://github.com/shopsys/shopsys/pull/642
[#641]: https://github.com/shopsys/shopsys/pull/641
[#640]: https://github.com/shopsys/shopsys/pull/640
[#637]: https://github.com/shopsys/shopsys/pull/637
[#636]: https://github.com/shopsys/shopsys/pull/636
[#633]: https://github.com/shopsys/shopsys/pull/633
[#628]: https://github.com/shopsys/shopsys/pull/628
[#627]: https://github.com/shopsys/shopsys/pull/627
[#624]: https://github.com/shopsys/shopsys/pull/624
[#623]: https://github.com/shopsys/shopsys/pull/623
[#619]: https://github.com/shopsys/shopsys/pull/619
[#611]: https://github.com/shopsys/shopsys/pull/611
[#610]: https://github.com/shopsys/shopsys/pull/610
[#609]: https://github.com/shopsys/shopsys/pull/609
[#596]: https://github.com/shopsys/shopsys/pull/596
[#588]: https://github.com/shopsys/shopsys/pull/588
[#454]: https://github.com/shopsys/shopsys/pull/454
[#567]: https://github.com/shopsys/shopsys/pull/567
[@dzarda86]: https://github.com/dzarda86
[#831]: https://github.com/shopsys/shopsys/pull/831
[#826]: https://github.com/shopsys/shopsys/pull/826
[#825]: https://github.com/shopsys/shopsys/pull/825
[#823]: https://github.com/shopsys/shopsys/pull/823
[#822]: https://github.com/shopsys/shopsys/pull/822
[#816]: https://github.com/shopsys/shopsys/pull/816
[#815]: https://github.com/shopsys/shopsys/pull/815
[#813]: https://github.com/shopsys/shopsys/pull/813
[#811]: https://github.com/shopsys/shopsys/pull/811
[#809]: https://github.com/shopsys/shopsys/pull/809
[#806]: https://github.com/shopsys/shopsys/pull/806
[#803]: https://github.com/shopsys/shopsys/pull/803
[#801]: https://github.com/shopsys/shopsys/pull/801
[#800]: https://github.com/shopsys/shopsys/pull/800
[#798]: https://github.com/shopsys/shopsys/pull/798
[#796]: https://github.com/shopsys/shopsys/pull/796
[#793]: https://github.com/shopsys/shopsys/pull/793
[#792]: https://github.com/shopsys/shopsys/pull/792
[#790]: https://github.com/shopsys/shopsys/pull/790
[#789]: https://github.com/shopsys/shopsys/pull/789
[#788]: https://github.com/shopsys/shopsys/pull/788
[#787]: https://github.com/shopsys/shopsys/pull/787
[#783]: https://github.com/shopsys/shopsys/pull/783
[#782]: https://github.com/shopsys/shopsys/pull/782
[#781]: https://github.com/shopsys/shopsys/pull/781
[#780]: https://github.com/shopsys/shopsys/pull/780
[#779]: https://github.com/shopsys/shopsys/pull/779
[#777]: https://github.com/shopsys/shopsys/pull/777
[#775]: https://github.com/shopsys/shopsys/pull/775
[#774]: https://github.com/shopsys/shopsys/pull/774
[#773]: https://github.com/shopsys/shopsys/pull/773
[#768]: https://github.com/shopsys/shopsys/pull/768
[#766]: https://github.com/shopsys/shopsys/pull/766
[#764]: https://github.com/shopsys/shopsys/pull/764
[#763]: https://github.com/shopsys/shopsys/pull/763
[#762]: https://github.com/shopsys/shopsys/pull/762
[#760]: https://github.com/shopsys/shopsys/pull/760
[#759]: https://github.com/shopsys/shopsys/pull/759
[#758]: https://github.com/shopsys/shopsys/pull/758
[#755]: https://github.com/shopsys/shopsys/pull/755
[#752]: https://github.com/shopsys/shopsys/pull/752
[#749]: https://github.com/shopsys/shopsys/pull/749
[#743]: https://github.com/shopsys/shopsys/pull/743
[#736]: https://github.com/shopsys/shopsys/pull/736
[#732]: https://github.com/shopsys/shopsys/pull/732
[#730]: https://github.com/shopsys/shopsys/pull/730
[#715]: https://github.com/shopsys/shopsys/pull/715
[#694]: https://github.com/shopsys/shopsys/pull/694

[#871]: https://github.com/shopsys/shopsys/pull/871
[#869]: https://github.com/shopsys/shopsys/pull/869
[#867]: https://github.com/shopsys/shopsys/pull/867
[#857]: https://github.com/shopsys/shopsys/pull/857
[#856]: https://github.com/shopsys/shopsys/pull/856
[#854]: https://github.com/shopsys/shopsys/pull/854
[#853]: https://github.com/shopsys/shopsys/pull/853
[#852]: https://github.com/shopsys/shopsys/pull/852
[#851]: https://github.com/shopsys/shopsys/pull/851
[#845]: https://github.com/shopsys/shopsys/pull/845
[#844]: https://github.com/shopsys/shopsys/pull/844
[#840]: https://github.com/shopsys/shopsys/pull/840
[#838]: https://github.com/shopsys/shopsys/pull/838
[#836]: https://github.com/shopsys/shopsys/pull/836
[#830]: https://github.com/shopsys/shopsys/pull/830
[#829]: https://github.com/shopsys/shopsys/pull/829
[#824]: https://github.com/shopsys/shopsys/pull/824
[#821]: https://github.com/shopsys/shopsys/pull/821
[#817]: https://github.com/shopsys/shopsys/pull/817
[@pejaycz]: https://github.com/pejaycz
[#993]: https://github.com/shopsys/shopsys/pull/993
[#995]: https://github.com/shopsys/shopsys/pull/995
[#1008]: https://github.com/shopsys/shopsys/pull/1008
[#1084]: https://github.com/shopsys/shopsys/pull/1084
[#1120]: https://github.com/shopsys/shopsys/pull/1120

[#905]: https://github.com/shopsys/shopsys/pull/905
[#903]: https://github.com/shopsys/shopsys/pull/903
[#902]: https://github.com/shopsys/shopsys/pull/902
[#901]: https://github.com/shopsys/shopsys/pull/901
[#899]: https://github.com/shopsys/shopsys/pull/899
[#895]: https://github.com/shopsys/shopsys/pull/895
[#892]: https://github.com/shopsys/shopsys/pull/892
[#891]: https://github.com/shopsys/shopsys/pull/891
[#889]: https://github.com/shopsys/shopsys/pull/889
[#887]: https://github.com/shopsys/shopsys/pull/887
[#886]: https://github.com/shopsys/shopsys/pull/886
[#885]: https://github.com/shopsys/shopsys/pull/885
[#883]: https://github.com/shopsys/shopsys/pull/883
[#882]: https://github.com/shopsys/shopsys/pull/882
[#881]: https://github.com/shopsys/shopsys/pull/881
[#879]: https://github.com/shopsys/shopsys/pull/879
[#878]: https://github.com/shopsys/shopsys/pull/878
[#877]: https://github.com/shopsys/shopsys/pull/877
[#876]: https://github.com/shopsys/shopsys/pull/876
[#875]: https://github.com/shopsys/shopsys/pull/875
[#874]: https://github.com/shopsys/shopsys/pull/874
[#873]: https://github.com/shopsys/shopsys/pull/873
[#870]: https://github.com/shopsys/shopsys/pull/870
[#868]: https://github.com/shopsys/shopsys/pull/868
[#864]: https://github.com/shopsys/shopsys/pull/864
[#848]: https://github.com/shopsys/shopsys/pull/848
[#846]: https://github.com/shopsys/shopsys/pull/846
[#808]: https://github.com/shopsys/shopsys/pull/808
[@Rixafy]: https://github.com/Rixafy

[#1020]: https://github.com/shopsys/shopsys/pull/1020
[#1016]: https://github.com/shopsys/shopsys/pull/1016
[#1014]: https://github.com/shopsys/shopsys/pull/1014
[#1013]: https://github.com/shopsys/shopsys/pull/1013
[#1006]: https://github.com/shopsys/shopsys/pull/1006
[#1001]: https://github.com/shopsys/shopsys/pull/1001
[#1000]: https://github.com/shopsys/shopsys/pull/1000
[#999]: https://github.com/shopsys/shopsys/pull/999
[#992]: https://github.com/shopsys/shopsys/pull/992
[#991]: https://github.com/shopsys/shopsys/pull/991
[#990]: https://github.com/shopsys/shopsys/pull/990
[#989]: https://github.com/shopsys/shopsys/pull/989
[#980]: https://github.com/shopsys/shopsys/pull/980
[#978]: https://github.com/shopsys/shopsys/pull/978
[#975]: https://github.com/shopsys/shopsys/pull/975
[#968]: https://github.com/shopsys/shopsys/pull/968
[#967]: https://github.com/shopsys/shopsys/pull/967
[#964]: https://github.com/shopsys/shopsys/pull/964
[#961]: https://github.com/shopsys/shopsys/pull/961
[#960]: https://github.com/shopsys/shopsys/pull/960
[#959]: https://github.com/shopsys/shopsys/pull/959
[#958]: https://github.com/shopsys/shopsys/pull/958
[#956]: https://github.com/shopsys/shopsys/pull/956
[#954]: https://github.com/shopsys/shopsys/pull/954
[#952]: https://github.com/shopsys/shopsys/pull/952
[#949]: https://github.com/shopsys/shopsys/pull/949
[#947]: https://github.com/shopsys/shopsys/pull/947
[#946]: https://github.com/shopsys/shopsys/pull/946
[#944]: https://github.com/shopsys/shopsys/pull/944
[#943]: https://github.com/shopsys/shopsys/pull/943
[#942]: https://github.com/shopsys/shopsys/pull/942
[#940]: https://github.com/shopsys/shopsys/pull/940
[#939]: https://github.com/shopsys/shopsys/pull/939
[#932]: https://github.com/shopsys/shopsys/pull/932
[#931]: https://github.com/shopsys/shopsys/pull/931
[#930]: https://github.com/shopsys/shopsys/pull/930
[#928]: https://github.com/shopsys/shopsys/pull/928
[#927]: https://github.com/shopsys/shopsys/pull/927
[#924]: https://github.com/shopsys/shopsys/pull/924
[#921]: https://github.com/shopsys/shopsys/pull/921
[#920]: https://github.com/shopsys/shopsys/pull/920
[#916]: https://github.com/shopsys/shopsys/pull/916
[#912]: https://github.com/shopsys/shopsys/pull/912
[#910]: https://github.com/shopsys/shopsys/pull/910
[#904]: https://github.com/shopsys/shopsys/pull/904
[#894]: https://github.com/shopsys/shopsys/pull/894
[#893]: https://github.com/shopsys/shopsys/pull/893
[#888]: https://github.com/shopsys/shopsys/pull/888
[@sspooky13]: https://github.com/sspooky13
[#1041]: https://github.com/shopsys/shopsys/pull/1041
[#1043]: https://github.com/shopsys/shopsys/pull/1043
[#1054]: https://github.com/shopsys/shopsys/pull/1054
[#1058]: https://github.com/shopsys/shopsys/pull/1058
[#1073]: https://github.com/shopsys/shopsys/pull/1073
[#1074]: https://github.com/shopsys/shopsys/pull/1074
[#1077]: https://github.com/shopsys/shopsys/pull/1077
[#1078]: https://github.com/shopsys/shopsys/pull/1078
[#1082]: https://github.com/shopsys/shopsys/pull/1082
[#1083]: https://github.com/shopsys/shopsys/pull/1083
[@malyMiso]: https://github.com/malyMiso
[#1018]: https://github.com/shopsys/shopsys/pull/1018
[#1025]: https://github.com/shopsys/shopsys/pull/1025
[#1040]: https://github.com/shopsys/shopsys/pull/1040
[#1049]: https://github.com/shopsys/shopsys/pull/1049
[#1053]: https://github.com/shopsys/shopsys/pull/1053
[#1063]: https://github.com/shopsys/shopsys/pull/1063
[#1064]: https://github.com/shopsys/shopsys/pull/1064
[#1068]: https://github.com/shopsys/shopsys/pull/1068
[#1069]: https://github.com/shopsys/shopsys/pull/1069
[#1073]: https://github.com/shopsys/shopsys/pull/1073
[#1092]: https://github.com/shopsys/shopsys/pull/1092
[#1094]: https://github.com/shopsys/shopsys/pull/1094
[#1105]: https://github.com/shopsys/shopsys/pull/1105
[#1106]: https://github.com/shopsys/shopsys/pull/1106
[#1109]: https://github.com/shopsys/shopsys/pull/1109
[#1113]: https://github.com/shopsys/shopsys/pull/1113
[#1114]: https://github.com/shopsys/shopsys/pull/1114
[#1119]: https://github.com/shopsys/shopsys/pull/1119
[#1121]: https://github.com/shopsys/shopsys/pull/1121
[#1122]: https://github.com/shopsys/shopsys/pull/1122
[#1125]: https://github.com/shopsys/shopsys/pull/1125
[#1126]: https://github.com/shopsys/shopsys/pull/1126
[#1129]: https://github.com/shopsys/shopsys/pull/1129
[#1133]: https://github.com/shopsys/shopsys/pull/1133
[#1139]: https://github.com/shopsys/shopsys/pull/1139
[#1145]: https://github.com/shopsys/shopsys/pull/1145
[#1153]: https://github.com/shopsys/shopsys/pull/1153
[#1154]: https://github.com/shopsys/shopsys/pull/1154
[#1157]: https://github.com/shopsys/shopsys/pull/1157
[#1159]: https://github.com/shopsys/shopsys/pull/1159
[#1161]: https://github.com/shopsys/shopsys/pull/1161
[#1162]: https://github.com/shopsys/shopsys/pull/1162
[#1164]: https://github.com/shopsys/shopsys/pull/1164
[#1166]: https://github.com/shopsys/shopsys/pull/1166
[#1172]: https://github.com/shopsys/shopsys/pull/1172
[#1175]: https://github.com/shopsys/shopsys/pull/1175
[#1177]: https://github.com/shopsys/shopsys/pull/1177
[#1180]: https://github.com/shopsys/shopsys/pull/1180
[#1195]: https://github.com/shopsys/shopsys/pull/1195
[#1199]: https://github.com/shopsys/shopsys/pull/1199
[#1410]: https://github.com/shopsys/shopsys/pull/1410
[#1406]: https://github.com/shopsys/shopsys/pull/1406
[#1405]: https://github.com/shopsys/shopsys/pull/1405
[#1403]: https://github.com/shopsys/shopsys/pull/1403
[#1402]: https://github.com/shopsys/shopsys/pull/1402
[#1371]: https://github.com/shopsys/shopsys/pull/1371
[#1370]: https://github.com/shopsys/shopsys/pull/1370
[#1362]: https://github.com/shopsys/shopsys/pull/1362
[#1355]: https://github.com/shopsys/shopsys/pull/1355
[#1353]: https://github.com/shopsys/shopsys/pull/1353
[#1335]: https://github.com/shopsys/shopsys/pull/1335
[#1323]: https://github.com/shopsys/shopsys/pull/1323
[#1311]: https://github.com/shopsys/shopsys/pull/1311
[#1303]: https://github.com/shopsys/shopsys/pull/1303
[#1290]: https://github.com/shopsys/shopsys/pull/1290
[#1282]: https://github.com/shopsys/shopsys/pull/1282
[#1273]: https://github.com/shopsys/shopsys/pull/1273
[#1264]: https://github.com/shopsys/shopsys/pull/1264
[#1248]: https://github.com/shopsys/shopsys/pull/1248
[#1244]: https://github.com/shopsys/shopsys/pull/1244
[#1240]: https://github.com/shopsys/shopsys/pull/1240
[#1234]: https://github.com/shopsys/shopsys/pull/1234
[#1232]: https://github.com/shopsys/shopsys/pull/1232
[#1231]: https://github.com/shopsys/shopsys/pull/1231
[#1226]: https://github.com/shopsys/shopsys/pull/1226
[#1225]: https://github.com/shopsys/shopsys/pull/1225
[#1210]: https://github.com/shopsys/shopsys/pull/1210
[#1198]: https://github.com/shopsys/shopsys/pull/1198
[#1189]: https://github.com/shopsys/shopsys/pull/1189
[#1414]: https://github.com/shopsys/shopsys/pull/1414
[@hason]: https://github.com/hason
[@TomasGottvald]: https://github.com/TomasGottvald
[#1262]: https://github.com/shopsys/shopsys/pull/1262
[#1256]: https://github.com/shopsys/shopsys/pull/1256
[#1255]: https://github.com/shopsys/shopsys/pull/1255
[#1254]: https://github.com/shopsys/shopsys/pull/1254
[#1250]: https://github.com/shopsys/shopsys/pull/1250
[#1248]: https://github.com/shopsys/shopsys/pull/1248
[#1245]: https://github.com/shopsys/shopsys/pull/1245
[#1243]: https://github.com/shopsys/shopsys/pull/1243
[#1240]: https://github.com/shopsys/shopsys/pull/1240
[#1239]: https://github.com/shopsys/shopsys/pull/1239
[#1234]: https://github.com/shopsys/shopsys/pull/1234
[#1232]: https://github.com/shopsys/shopsys/pull/1232
[#1231]: https://github.com/shopsys/shopsys/pull/1231
[#1230]: https://github.com/shopsys/shopsys/pull/1230
[#1229]: https://github.com/shopsys/shopsys/pull/1229
[#1227]: https://github.com/shopsys/shopsys/pull/1227
[#1226]: https://github.com/shopsys/shopsys/pull/1226
[#1225]: https://github.com/shopsys/shopsys/pull/1225
[#1219]: https://github.com/shopsys/shopsys/pull/1219
[#1215]: https://github.com/shopsys/shopsys/pull/1215
[#1210]: https://github.com/shopsys/shopsys/pull/1210
[#1209]: https://github.com/shopsys/shopsys/pull/1209
[#1208]: https://github.com/shopsys/shopsys/pull/1208
[#1207]: https://github.com/shopsys/shopsys/pull/1207
[#1204]: https://github.com/shopsys/shopsys/pull/1204
[#1203]: https://github.com/shopsys/shopsys/pull/1203
[#1201]: https://github.com/shopsys/shopsys/pull/1201
[#1197]: https://github.com/shopsys/shopsys/pull/1197
[#1194]: https://github.com/shopsys/shopsys/pull/1194
[#1193]: https://github.com/shopsys/shopsys/pull/1193
[#1192]: https://github.com/shopsys/shopsys/pull/1192
[#1191]: https://github.com/shopsys/shopsys/pull/1191
[#1183]: https://github.com/shopsys/shopsys/pull/1183
[#1181]: https://github.com/shopsys/shopsys/pull/1181
[#1176]: https://github.com/shopsys/shopsys/pull/1176
[#1168]: https://github.com/shopsys/shopsys/pull/1168
[#1155]: https://github.com/shopsys/shopsys/pull/1155
[#1140]: https://github.com/shopsys/shopsys/pull/1140
[#1123]: https://github.com/shopsys/shopsys/pull/1123
[#1096]: https://github.com/shopsys/shopsys/pull/1096
[#1047]: https://github.com/shopsys/shopsys/pull/1047
[#1044]: https://github.com/shopsys/shopsys/pull/1044
[#1010]: https://github.com/shopsys/shopsys/pull/1010
[#1115]: https://github.com/shopsys/shopsys/pull/1115
[#1099]: https://github.com/shopsys/shopsys/pull/1099
[#1097]: https://github.com/shopsys/shopsys/pull/1097
[#1072]: https://github.com/shopsys/shopsys/pull/1072
[#1066]: https://github.com/shopsys/shopsys/pull/1066
[#1055]: https://github.com/shopsys/shopsys/pull/1055
[#1004]: https://github.com/shopsys/shopsys/pull/1004
[#933]: https://github.com/shopsys/shopsys/pull/933
[#918]: https://github.com/shopsys/shopsys/pull/918
[#906]: https://github.com/shopsys/shopsys/pull/906
[#875]: https://github.com/shopsys/shopsys/pull/875
[#539]: https://github.com/shopsys/shopsys/pull/539

[#1679]: https://github.com/shopsys/shopsys/pull/1679
[#1668]: https://github.com/shopsys/shopsys/pull/1668
[#1609]: https://github.com/shopsys/shopsys/pull/1609
[#1551]: https://github.com/shopsys/shopsys/pull/1551
[#1549]: https://github.com/shopsys/shopsys/pull/1549
[#1502]: https://github.com/shopsys/shopsys/pull/1502
[#1494]: https://github.com/shopsys/shopsys/pull/1494
[#1475]: https://github.com/shopsys/shopsys/pull/1475
[#1470]: https://github.com/shopsys/shopsys/pull/1470
[#1462]: https://github.com/shopsys/shopsys/pull/1462
[#1459]: https://github.com/shopsys/shopsys/pull/1459
[#1419]: https://github.com/shopsys/shopsys/pull/1419
[#1389]: https://github.com/shopsys/shopsys/pull/1389
[#1373]: https://github.com/shopsys/shopsys/pull/1373
[@machicek]: https://github.com/machicek

[#1561]: https://github.com/shopsys/shopsys/pull/1561
[#1560]: https://github.com/shopsys/shopsys/pull/1560
[#1532]: https://github.com/shopsys/shopsys/pull/1532
[#1524]: https://github.com/shopsys/shopsys/pull/1524
[#1517]: https://github.com/shopsys/shopsys/pull/1517
[#1506]: https://github.com/shopsys/shopsys/pull/1506
[#1487]: https://github.com/shopsys/shopsys/pull/1487
[#1485]: https://github.com/shopsys/shopsys/pull/1485
[#1483]: https://github.com/shopsys/shopsys/pull/1483
[#1479]: https://github.com/shopsys/shopsys/pull/1479
[#1476]: https://github.com/shopsys/shopsys/pull/1476
[#1472]: https://github.com/shopsys/shopsys/pull/1472
[#1465]: https://github.com/shopsys/shopsys/pull/1465
[#1464]: https://github.com/shopsys/shopsys/pull/1464
[#1461]: https://github.com/shopsys/shopsys/pull/1461
[#1452]: https://github.com/shopsys/shopsys/pull/1452
[#1451]: https://github.com/shopsys/shopsys/pull/1451
[#1446]: https://github.com/shopsys/shopsys/pull/1446
[#1441]: https://github.com/shopsys/shopsys/pull/1441
[#1425]: https://github.com/shopsys/shopsys/pull/1425
[#1423]: https://github.com/shopsys/shopsys/pull/1423
[#1416]: https://github.com/shopsys/shopsys/pull/1416
[#1408]: https://github.com/shopsys/shopsys/pull/1408
[#1393]: https://github.com/shopsys/shopsys/pull/1393
[#1392]: https://github.com/shopsys/shopsys/pull/1392
[#1384]: https://github.com/shopsys/shopsys/pull/1384
[#1381]: https://github.com/shopsys/shopsys/pull/1381
[#1364]: https://github.com/shopsys/shopsys/pull/1364
[#1361]: https://github.com/shopsys/shopsys/pull/1361
[#1360]: https://github.com/shopsys/shopsys/pull/1360
[#1354]: https://github.com/shopsys/shopsys/pull/1354
[#1346]: https://github.com/shopsys/shopsys/pull/1346
[#1344]: https://github.com/shopsys/shopsys/pull/1344
[#1343]: https://github.com/shopsys/shopsys/pull/1343
[#1342]: https://github.com/shopsys/shopsys/pull/1342
[#1340]: https://github.com/shopsys/shopsys/pull/1340
[#1336]: https://github.com/shopsys/shopsys/pull/1336
[#1330]: https://github.com/shopsys/shopsys/pull/1330
[#1319]: https://github.com/shopsys/shopsys/pull/1319
[#1314]: https://github.com/shopsys/shopsys/pull/1314
[#1313]: https://github.com/shopsys/shopsys/pull/1313
[#1309]: https://github.com/shopsys/shopsys/pull/1309
[#1289]: https://github.com/shopsys/shopsys/pull/1289
[#1285]: https://github.com/shopsys/shopsys/pull/1285
[#1276]: https://github.com/shopsys/shopsys/pull/1276
[@mariusbuescher]: https://github.com/mariusbuescher

[#1854]: https://github.com/shopsys/shopsys/pull/1854
[#1853]: https://github.com/shopsys/shopsys/pull/1853
[#1850]: https://github.com/shopsys/shopsys/pull/1850
[#1846]: https://github.com/shopsys/shopsys/pull/1846
[#1845]: https://github.com/shopsys/shopsys/pull/1845
[#1844]: https://github.com/shopsys/shopsys/pull/1844
[#1843]: https://github.com/shopsys/shopsys/pull/1843
[#1841]: https://github.com/shopsys/shopsys/pull/1841
[#1840]: https://github.com/shopsys/shopsys/pull/1840
[#1839]: https://github.com/shopsys/shopsys/pull/1839
[#1838]: https://github.com/shopsys/shopsys/pull/1838
[#1837]: https://github.com/shopsys/shopsys/pull/1837
[#1834]: https://github.com/shopsys/shopsys/pull/1834
[#1833]: https://github.com/shopsys/shopsys/pull/1833
[#1831]: https://github.com/shopsys/shopsys/pull/1831
[#1830]: https://github.com/shopsys/shopsys/pull/1830
[#1829]: https://github.com/shopsys/shopsys/pull/1829
[#1828]: https://github.com/shopsys/shopsys/pull/1828
[#1826]: https://github.com/shopsys/shopsys/pull/1826
[#1824]: https://github.com/shopsys/shopsys/pull/1824
[#1822]: https://github.com/shopsys/shopsys/pull/1822
[#1821]: https://github.com/shopsys/shopsys/pull/1821
[#1820]: https://github.com/shopsys/shopsys/pull/1820
[#1819]: https://github.com/shopsys/shopsys/pull/1819
[#1818]: https://github.com/shopsys/shopsys/pull/1818
[#1817]: https://github.com/shopsys/shopsys/pull/1817
[#1814]: https://github.com/shopsys/shopsys/pull/1814
[#1811]: https://github.com/shopsys/shopsys/pull/1811
[#1809]: https://github.com/shopsys/shopsys/pull/1809
[#1808]: https://github.com/shopsys/shopsys/pull/1808
[#1807]: https://github.com/shopsys/shopsys/pull/1807
[#1806]: https://github.com/shopsys/shopsys/pull/1806
[#1804]: https://github.com/shopsys/shopsys/pull/1804
[#1803]: https://github.com/shopsys/shopsys/pull/1803
[#1801]: https://github.com/shopsys/shopsys/pull/1801
[#1800]: https://github.com/shopsys/shopsys/pull/1800
[#1799]: https://github.com/shopsys/shopsys/pull/1799
[#1797]: https://github.com/shopsys/shopsys/pull/1797
[#1795]: https://github.com/shopsys/shopsys/pull/1795
[#1793]: https://github.com/shopsys/shopsys/pull/1793
[#1792]: https://github.com/shopsys/shopsys/pull/1792
[#1791]: https://github.com/shopsys/shopsys/pull/1791
[#1789]: https://github.com/shopsys/shopsys/pull/1789
[#1788]: https://github.com/shopsys/shopsys/pull/1788
[#1787]: https://github.com/shopsys/shopsys/pull/1787
[#1786]: https://github.com/shopsys/shopsys/pull/1786
[#1785]: https://github.com/shopsys/shopsys/pull/1785
[#1784]: https://github.com/shopsys/shopsys/pull/1784
[#1782]: https://github.com/shopsys/shopsys/pull/1782
[#1781]: https://github.com/shopsys/shopsys/pull/1781
[#1779]: https://github.com/shopsys/shopsys/pull/1779
[#1776]: https://github.com/shopsys/shopsys/pull/1776
[#1774]: https://github.com/shopsys/shopsys/pull/1774
[#1772]: https://github.com/shopsys/shopsys/pull/1772
[#1771]: https://github.com/shopsys/shopsys/pull/1771
[#1770]: https://github.com/shopsys/shopsys/pull/1770
[#1769]: https://github.com/shopsys/shopsys/pull/1769
[#1767]: https://github.com/shopsys/shopsys/pull/1767
[#1766]: https://github.com/shopsys/shopsys/pull/1766
[#1765]: https://github.com/shopsys/shopsys/pull/1765
[#1764]: https://github.com/shopsys/shopsys/pull/1764
[#1761]: https://github.com/shopsys/shopsys/pull/1761
[#1758]: https://github.com/shopsys/shopsys/pull/1758
[#1757]: https://github.com/shopsys/shopsys/pull/1757
[#1756]: https://github.com/shopsys/shopsys/pull/1756
[#1755]: https://github.com/shopsys/shopsys/pull/1755
[#1754]: https://github.com/shopsys/shopsys/pull/1754
[#1753]: https://github.com/shopsys/shopsys/pull/1753
[#1752]: https://github.com/shopsys/shopsys/pull/1752
[#1748]: https://github.com/shopsys/shopsys/pull/1748
[#1745]: https://github.com/shopsys/shopsys/pull/1745
[#1742]: https://github.com/shopsys/shopsys/pull/1742
[#1740]: https://github.com/shopsys/shopsys/pull/1740
[#1739]: https://github.com/shopsys/shopsys/pull/1739
[#1738]: https://github.com/shopsys/shopsys/pull/1738
[#1737]: https://github.com/shopsys/shopsys/pull/1737
[#1736]: https://github.com/shopsys/shopsys/pull/1736
[#1733]: https://github.com/shopsys/shopsys/pull/1733
[#1732]: https://github.com/shopsys/shopsys/pull/1732
[#1731]: https://github.com/shopsys/shopsys/pull/1731
[#1728]: https://github.com/shopsys/shopsys/pull/1728
[#1726]: https://github.com/shopsys/shopsys/pull/1726
[#1725]: https://github.com/shopsys/shopsys/pull/1725
[#1722]: https://github.com/shopsys/shopsys/pull/1722
[#1720]: https://github.com/shopsys/shopsys/pull/1720
[#1718]: https://github.com/shopsys/shopsys/pull/1718
[#1717]: https://github.com/shopsys/shopsys/pull/1717
[#1712]: https://github.com/shopsys/shopsys/pull/1712
[#1704]: https://github.com/shopsys/shopsys/pull/1704
[#1703]: https://github.com/shopsys/shopsys/pull/1703
[#1700]: https://github.com/shopsys/shopsys/pull/1700
[#1698]: https://github.com/shopsys/shopsys/pull/1698
[#1696]: https://github.com/shopsys/shopsys/pull/1696
[#1685]: https://github.com/shopsys/shopsys/pull/1685
[#1683]: https://github.com/shopsys/shopsys/pull/1683
[#1682]: https://github.com/shopsys/shopsys/pull/1682
[#1681]: https://github.com/shopsys/shopsys/pull/1681
[#1677]: https://github.com/shopsys/shopsys/pull/1677
[#1676]: https://github.com/shopsys/shopsys/pull/1676
[#1675]: https://github.com/shopsys/shopsys/pull/1675
[#1673]: https://github.com/shopsys/shopsys/pull/1673
[#1671]: https://github.com/shopsys/shopsys/pull/1671
[#1669]: https://github.com/shopsys/shopsys/pull/1669
[#1666]: https://github.com/shopsys/shopsys/pull/1666
[#1664]: https://github.com/shopsys/shopsys/pull/1664
[#1663]: https://github.com/shopsys/shopsys/pull/1663
[#1659]: https://github.com/shopsys/shopsys/pull/1659
[#1655]: https://github.com/shopsys/shopsys/pull/1655
[#1654]: https://github.com/shopsys/shopsys/pull/1654
[#1653]: https://github.com/shopsys/shopsys/pull/1653
[#1652]: https://github.com/shopsys/shopsys/pull/1652
[#1651]: https://github.com/shopsys/shopsys/pull/1651
[#1650]: https://github.com/shopsys/shopsys/pull/1650
[#1649]: https://github.com/shopsys/shopsys/pull/1649
[#1647]: https://github.com/shopsys/shopsys/pull/1647
[#1645]: https://github.com/shopsys/shopsys/pull/1645

[#1641]: https://github.com/shopsys/shopsys/pull/1641
[#1640]: https://github.com/shopsys/shopsys/pull/1640
[#1638]: https://github.com/shopsys/shopsys/pull/1638
[#1637]: https://github.com/shopsys/shopsys/pull/1637
[#1636]: https://github.com/shopsys/shopsys/pull/1636
[#1635]: https://github.com/shopsys/shopsys/pull/1635
[#1634]: https://github.com/shopsys/shopsys/pull/1634
[#1632]: https://github.com/shopsys/shopsys/pull/1632
[#1630]: https://github.com/shopsys/shopsys/pull/1630
[#1629]: https://github.com/shopsys/shopsys/pull/1629
[#1627]: https://github.com/shopsys/shopsys/pull/1627
[#1626]: https://github.com/shopsys/shopsys/pull/1626
[#1624]: https://github.com/shopsys/shopsys/pull/1624
[#1623]: https://github.com/shopsys/shopsys/pull/1623
[#1622]: https://github.com/shopsys/shopsys/pull/1622
[#1620]: https://github.com/shopsys/shopsys/pull/1620
[#1618]: https://github.com/shopsys/shopsys/pull/1618
[#1611]: https://github.com/shopsys/shopsys/pull/1611
[#1610]: https://github.com/shopsys/shopsys/pull/1610
[#1608]: https://github.com/shopsys/shopsys/pull/1608
[#1606]: https://github.com/shopsys/shopsys/pull/1606
[#1605]: https://github.com/shopsys/shopsys/pull/1605
[#1602]: https://github.com/shopsys/shopsys/pull/1602
[#1601]: https://github.com/shopsys/shopsys/pull/1601
[#1587]: https://github.com/shopsys/shopsys/pull/1587
[#1580]: https://github.com/shopsys/shopsys/pull/1580
[#1566]: https://github.com/shopsys/shopsys/pull/1566
[#1565]: https://github.com/shopsys/shopsys/pull/1565
[#1545]: https://github.com/shopsys/shopsys/pull/1545
[#1542]: https://github.com/shopsys/shopsys/pull/1542
[#1514]: https://github.com/shopsys/shopsys/pull/1514
[#1478]: https://github.com/shopsys/shopsys/pull/1478
[@mposchl]: https://github.com/mposchl

[#1928]: https://github.com/shopsys/shopsys/pull/1928
[#1927]: https://github.com/shopsys/shopsys/pull/1927
[#1926]: https://github.com/shopsys/shopsys/pull/1926
[#1923]: https://github.com/shopsys/shopsys/pull/1923
[#1920]: https://github.com/shopsys/shopsys/pull/1920
[#1919]: https://github.com/shopsys/shopsys/pull/1919
[#1916]: https://github.com/shopsys/shopsys/pull/1916
[#1915]: https://github.com/shopsys/shopsys/pull/1915
[#1909]: https://github.com/shopsys/shopsys/pull/1909
[#1908]: https://github.com/shopsys/shopsys/pull/1908
[#1906]: https://github.com/shopsys/shopsys/pull/1906
[#1905]: https://github.com/shopsys/shopsys/pull/1905
[#1904]: https://github.com/shopsys/shopsys/pull/1904
[#1902]: https://github.com/shopsys/shopsys/pull/1902
[#1897]: https://github.com/shopsys/shopsys/pull/1897
[#1896]: https://github.com/shopsys/shopsys/pull/1896
[#1892]: https://github.com/shopsys/shopsys/pull/1892
[#1888]: https://github.com/shopsys/shopsys/pull/1888
[#1885]: https://github.com/shopsys/shopsys/pull/1885
[#1880]: https://github.com/shopsys/shopsys/pull/1880
[#1874]: https://github.com/shopsys/shopsys/pull/1874
[#1868]: https://github.com/shopsys/shopsys/pull/1868
[#1865]: https://github.com/shopsys/shopsys/pull/1865
[#1864]: https://github.com/shopsys/shopsys/pull/1864
[#1862]: https://github.com/shopsys/shopsys/pull/1862
[#1861]: https://github.com/shopsys/shopsys/pull/1861
[#1858]: https://github.com/shopsys/shopsys/pull/1858
[#1857]: https://github.com/shopsys/shopsys/pull/1857
[#1842]: https://github.com/shopsys/shopsys/pull/1842
[#1836]: https://github.com/shopsys/shopsys/pull/1836
[#1827]: https://github.com/shopsys/shopsys/pull/1827
[#1825]: https://github.com/shopsys/shopsys/pull/1825
[#1810]: https://github.com/shopsys/shopsys/pull/1810
[#1794]: https://github.com/shopsys/shopsys/pull/1794
[#1790]: https://github.com/shopsys/shopsys/pull/1790
[#1730]: https://github.com/shopsys/shopsys/pull/1730
[#1723]: https://github.com/shopsys/shopsys/pull/1723
[#1719]: https://github.com/shopsys/shopsys/pull/1719
[#1708]: https://github.com/shopsys/shopsys/pull/1708
[#1692]: https://github.com/shopsys/shopsys/pull/1692
[#1674]: https://github.com/shopsys/shopsys/pull/1674
[#1672]: https://github.com/shopsys/shopsys/pull/1672
[#1670]: https://github.com/shopsys/shopsys/pull/1670
[#1660]: https://github.com/shopsys/shopsys/pull/1660
[#1644]: https://github.com/shopsys/shopsys/pull/1644
[#1619]: https://github.com/shopsys/shopsys/pull/1619
[#1599]: https://github.com/shopsys/shopsys/pull/1599
[#1590]: https://github.com/shopsys/shopsys/pull/1590
[#1588]: https://github.com/shopsys/shopsys/pull/1588
[#1585]: https://github.com/shopsys/shopsys/pull/1585
[#1584]: https://github.com/shopsys/shopsys/pull/1584
[#1582]: https://github.com/shopsys/shopsys/pull/1582
[#1578]: https://github.com/shopsys/shopsys/pull/1578
[#1575]: https://github.com/shopsys/shopsys/pull/1575
[#1574]: https://github.com/shopsys/shopsys/pull/1574
[#1391]: https://github.com/shopsys/shopsys/pull/1391
[#1338]: https://github.com/shopsys/shopsys/pull/1338
[#771]: https://github.com/shopsys/shopsys/pull/771
[@spagr]: https://github.com/spagr
[@ondrejbohac]: https://github.com/ondrejbohac
[@milanStano]: https://github.com/milanStano
[@PetrHeinz]: https://github.com/PetrHeinz
[@MattCzerner]: https://github.com/MattCzerner
[@RostislavKreisinger]: https://github.com/RostislavKreisinger

[#2010]: https://github.com/shopsys/shopsys/pull/2010
[#2009]: https://github.com/shopsys/shopsys/pull/2009
[#2006]: https://github.com/shopsys/shopsys/pull/2006
[#2004]: https://github.com/shopsys/shopsys/pull/2004
[#2001]: https://github.com/shopsys/shopsys/pull/2001
[#2000]: https://github.com/shopsys/shopsys/pull/2000
[#1998]: https://github.com/shopsys/shopsys/pull/1998
[#1995]: https://github.com/shopsys/shopsys/pull/1995
[#1988]: https://github.com/shopsys/shopsys/pull/1988
[#1987]: https://github.com/shopsys/shopsys/pull/1987
[#1986]: https://github.com/shopsys/shopsys/pull/1986
[#1985]: https://github.com/shopsys/shopsys/pull/1985
[#1982]: https://github.com/shopsys/shopsys/pull/1982
[#1979]: https://github.com/shopsys/shopsys/pull/1979
[#1977]: https://github.com/shopsys/shopsys/pull/1977
[#1972]: https://github.com/shopsys/shopsys/pull/1972
[#1971]: https://github.com/shopsys/shopsys/pull/1971
[#1970]: https://github.com/shopsys/shopsys/pull/1970
[#1967]: https://github.com/shopsys/shopsys/pull/1967
[#1966]: https://github.com/shopsys/shopsys/pull/1966
[#1964]: https://github.com/shopsys/shopsys/pull/1964
[#1963]: https://github.com/shopsys/shopsys/pull/1963
[#1956]: https://github.com/shopsys/shopsys/pull/1956
[#1955]: https://github.com/shopsys/shopsys/pull/1955
[#1949]: https://github.com/shopsys/shopsys/pull/1949
[#1948]: https://github.com/shopsys/shopsys/pull/1948
[#1947]: https://github.com/shopsys/shopsys/pull/1947
[#1946]: https://github.com/shopsys/shopsys/pull/1946
[#1944]: https://github.com/shopsys/shopsys/pull/1944
[#1941]: https://github.com/shopsys/shopsys/pull/1941
[#1940]: https://github.com/shopsys/shopsys/pull/1940
[#1939]: https://github.com/shopsys/shopsys/pull/1939
[#1938]: https://github.com/shopsys/shopsys/pull/1938
[#1937]: https://github.com/shopsys/shopsys/pull/1937
[#1932]: https://github.com/shopsys/shopsys/pull/1932
[#1918]: https://github.com/shopsys/shopsys/pull/1918
[#1823]: https://github.com/shopsys/shopsys/pull/1823
[#1777]: https://github.com/shopsys/shopsys/pull/1777
[#1750]: https://github.com/shopsys/shopsys/pull/1750
[#2036]: https://github.com/shopsys/shopsys/pull/2036
[#2050]: https://github.com/shopsys/shopsys/pull/2050
[#2046]: https://github.com/shopsys/shopsys/pull/2046
[#2043]: https://github.com/shopsys/shopsys/pull/2043
[#2045]: https://github.com/shopsys/shopsys/pull/2045
[#2041]: https://github.com/shopsys/shopsys/pull/2041
[#2038]: https://github.com/shopsys/shopsys/pull/2038
[#2026]: https://github.com/shopsys/shopsys/pull/2026
[#2135]: https://github.com/shopsys/shopsys/pull/2135

[#2163]: https://github.com/shopsys/shopsys/pull/2163
[#2159]: https://github.com/shopsys/shopsys/pull/2159
[#2158]: https://github.com/shopsys/shopsys/pull/2158
[#2156]: https://github.com/shopsys/shopsys/pull/2156
[#2155]: https://github.com/shopsys/shopsys/pull/2155
[#2154]: https://github.com/shopsys/shopsys/pull/2154
[#2153]: https://github.com/shopsys/shopsys/pull/2153
[#2151]: https://github.com/shopsys/shopsys/pull/2151
[#2150]: https://github.com/shopsys/shopsys/pull/2150
[#2148]: https://github.com/shopsys/shopsys/pull/2148
[#2146]: https://github.com/shopsys/shopsys/pull/2146
[#2145]: https://github.com/shopsys/shopsys/pull/2145
[#2142]: https://github.com/shopsys/shopsys/pull/2142
[#2141]: https://github.com/shopsys/shopsys/pull/2141
[#2140]: https://github.com/shopsys/shopsys/pull/2140
[#2133]: https://github.com/shopsys/shopsys/pull/2133
[#2132]: https://github.com/shopsys/shopsys/pull/2132
[#2131]: https://github.com/shopsys/shopsys/pull/2131
[#2127]: https://github.com/shopsys/shopsys/pull/2127
[#2126]: https://github.com/shopsys/shopsys/pull/2126
[#2123]: https://github.com/shopsys/shopsys/pull/2123
[#2120]: https://github.com/shopsys/shopsys/pull/2120
[#2119]: https://github.com/shopsys/shopsys/pull/2119
[#2118]: https://github.com/shopsys/shopsys/pull/2118
[#2117]: https://github.com/shopsys/shopsys/pull/2117
[#2113]: https://github.com/shopsys/shopsys/pull/2113
[#2110]: https://github.com/shopsys/shopsys/pull/2110
[#2109]: https://github.com/shopsys/shopsys/pull/2109
[#2107]: https://github.com/shopsys/shopsys/pull/2107
[#2104]: https://github.com/shopsys/shopsys/pull/2104
[#2103]: https://github.com/shopsys/shopsys/pull/2103
[#2100]: https://github.com/shopsys/shopsys/pull/2100
[#2099]: https://github.com/shopsys/shopsys/pull/2099
[#2096]: https://github.com/shopsys/shopsys/pull/2096
[#2095]: https://github.com/shopsys/shopsys/pull/2095
[#2094]: https://github.com/shopsys/shopsys/pull/2094
[#2091]: https://github.com/shopsys/shopsys/pull/2091
[#2090]: https://github.com/shopsys/shopsys/pull/2090
[#2089]: https://github.com/shopsys/shopsys/pull/2089
[#2086]: https://github.com/shopsys/shopsys/pull/2086
[#2084]: https://github.com/shopsys/shopsys/pull/2084
[#2083]: https://github.com/shopsys/shopsys/pull/2083
[#2080]: https://github.com/shopsys/shopsys/pull/2080
[#2079]: https://github.com/shopsys/shopsys/pull/2079
[#2077]: https://github.com/shopsys/shopsys/pull/2077
[#2075]: https://github.com/shopsys/shopsys/pull/2075
[#2074]: https://github.com/shopsys/shopsys/pull/2074
[#2070]: https://github.com/shopsys/shopsys/pull/2070
[#2068]: https://github.com/shopsys/shopsys/pull/2068
[#2067]: https://github.com/shopsys/shopsys/pull/2067
[#2066]: https://github.com/shopsys/shopsys/pull/2066
[#2064]: https://github.com/shopsys/shopsys/pull/2064
[#2057]: https://github.com/shopsys/shopsys/pull/2057
[#2055]: https://github.com/shopsys/shopsys/pull/2055
[#2052]: https://github.com/shopsys/shopsys/pull/2052
[#2051]: https://github.com/shopsys/shopsys/pull/2051
[#2049]: https://github.com/shopsys/shopsys/pull/2049
[#2047]: https://github.com/shopsys/shopsys/pull/2047
[#2040]: https://github.com/shopsys/shopsys/pull/2040
[#2037]: https://github.com/shopsys/shopsys/pull/2037
[#2035]: https://github.com/shopsys/shopsys/pull/2035
[#2032]: https://github.com/shopsys/shopsys/pull/2032
[#2031]: https://github.com/shopsys/shopsys/pull/2031
[#2025]: https://github.com/shopsys/shopsys/pull/2025
[#2024]: https://github.com/shopsys/shopsys/pull/2024
[#2023]: https://github.com/shopsys/shopsys/pull/2023
[#2020]: https://github.com/shopsys/shopsys/pull/2020
[#2019]: https://github.com/shopsys/shopsys/pull/2019
[#2002]: https://github.com/shopsys/shopsys/pull/2002
[#1999]: https://github.com/shopsys/shopsys/pull/1999
[#1996]: https://github.com/shopsys/shopsys/pull/1996
[#1994]: https://github.com/shopsys/shopsys/pull/1994
[#1990]: https://github.com/shopsys/shopsys/pull/1990
[#1989]: https://github.com/shopsys/shopsys/pull/1989
[#1976]: https://github.com/shopsys/shopsys/pull/1976
[#1974]: https://github.com/shopsys/shopsys/pull/1974
[#1961]: https://github.com/shopsys/shopsys/pull/1961
[#1960]: https://github.com/shopsys/shopsys/pull/1960
[#1957]: https://github.com/shopsys/shopsys/pull/1957
[#1952]: https://github.com/shopsys/shopsys/pull/1952
[#1951]: https://github.com/shopsys/shopsys/pull/1951
[#1933]: https://github.com/shopsys/shopsys/pull/1933
[#1922]: https://github.com/shopsys/shopsys/pull/1922
[#1913]: https://github.com/shopsys/shopsys/pull/1913
[#1912]: https://github.com/shopsys/shopsys/pull/1912
[#1901]: https://github.com/shopsys/shopsys/pull/1901
[#1899]: https://github.com/shopsys/shopsys/pull/1899
[#1894]: https://github.com/shopsys/shopsys/pull/1894
[#1891]: https://github.com/shopsys/shopsys/pull/1891
[#1879]: https://github.com/shopsys/shopsys/pull/1879
[#1760]: https://github.com/shopsys/shopsys/pull/1760
[@CarabineSK]: https://github.com/CarabineSK
