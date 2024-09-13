# Changelog for 14.0

All notable changes that change in some way the behavior of any of our packages are maintained by the monorepo repository.

There is a list of all the repositories maintained by the monorepo:

-   [shopsys/framework](https://github.com/shopsys/framework)
-   [shopsys/project-base](https://github.com/shopsys/project-base)
-   [shopsys/shopsys](https://github.com/shopsys/shopsys)
-   [shopsys/coding-standards](https://github.com/shopsys/coding-standards)
-   [shopsys/form-types-bundle](https://github.com/shopsys/form-types-bundle)
-   [shopsys/http-smoke-testing](https://github.com/shopsys/http-smoke-testing)
-   [shopsys/migrations](https://github.com/shopsys/migrations)
-   [shopsys/monorepo-tools](https://github.com/shopsys/monorepo-tools)
-   [shopsys/plugin-interface](https://github.com/shopsys/plugin-interface)
-   [shopsys/brand-feed-luigis-box](https://github.com/shopsys/category-feed-luigis-box)
-   [shopsys/category-feed-luigis-box](https://github.com/shopsys/category-feed-luigis-box)
-   [shopsys/product-feed-google](https://github.com/shopsys/product-feed-google)
-   [shopsys/product-feed-heureka](https://github.com/shopsys/product-feed-heureka)
-   [shopsys/product-feed-heureka-delivery](https://github.com/shopsys/product-feed-heureka-delivery)
-   [shopsys/product-feed-zbozi](https://github.com/shopsys/product-feed-zbozi)
-   [shopsys/product-feed-luigis-box](https://github.com/shopsys/product-feed-luigis-box)
-   [shopsys/article-feed-luigis-box](https://github.com/shopsys/article-feed-luigis-box)
-   [shopsys/google-cloud-bundle](https://github.com/shopsys/google-cloud-bundle)
-   [shopsys/s3-bridge](https://github.com/shopsys/s3-bridge)
-   [shopsys/frontend-api](https://github.com/shopsys/frontend-api)
-   [shopsys/php-image](https://github.com/shopsys/php-image)
-   [shopsys/luigis-box](https://github.com/shopsys/luigis-box)

Packages are formatted by release version.
You can see all the changes done to the package that you carry about with this tree.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/) and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html) as explained in the [Backward Compatibility Promise](https://docs.shopsys.com/en/latest/contributing/backward-compatibility-promise/).

<!-- Add generated changelog below this line -->
<!-- Release notes generated using configuration in .github/release.yml at 14.0 -->

## [v14.0.1](https://github.com/shopsys/shopsys/compare/v14.0.0...v14.0.1) (2024-09-13)

### :sparkles: Enhancements and features

-   [framework] improved formatting of Entity logs by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3410

### :bug: Bug Fixes

-   [shopsys] moved two attributes of ProductFormType from project-base to framework by @stanoMilan in https://github.com/shopsys/shopsys/pull/3320
-   [shopsys] moved CSRFExtension from project-base to framework by @stanoMilan in https://github.com/shopsys/shopsys/pull/3318
-   [shopsys] fixed image resolving for transport in FrontendAPI by @stanoMilan in https://github.com/shopsys/shopsys/pull/3316
-   [framework] fixed price range query by @stanoMilan in https://github.com/shopsys/shopsys/pull/3312
-   [framework] fixed query wrongly loading flags from Product instead of ProductDomain by @stanoMilan in https://github.com/shopsys/shopsys/pull/3310
-   [shopsys] fixed duplicate addDomain to query builder by @stanoMilan in https://github.com/shopsys/shopsys/pull/3338
-   [framework] annotations fixer access of nullable does not break fixer by @stanoMilan in https://github.com/shopsys/shopsys/pull/3357
-   [framework] fix create ImageTypeNotFoundException with null imageType attr by @stanoMilan in https://github.com/shopsys/shopsys/pull/3334
-   [framework] entity log no longer logs collection without change by @stanoMilan in https://github.com/shopsys/shopsys/pull/3375
-   [framework] fixed wrong columns used in migration Version20240102112523 by @stanoMilan in https://github.com/shopsys/shopsys/pull/3402
-   [framework] fixed EntityLogger to no longer empty collection that is cleared and filled on every update by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3406
-   [framework] fixed redundant log for the Money type if the scale of compared object was different by @stanoMilan in https://github.com/shopsys/shopsys/pull/3405

### :hammer: Developer experience and refactoring

-   [shopsys] moved migration of stock settings from project-base to framework by @stanoMilan in https://github.com/shopsys/shopsys/pull/3340
-   [shopsys] moved category parameters to framework package by @stanoMilan in https://github.com/shopsys/shopsys/pull/3336
-   [framework] EntityLogEventListener now handles PersistentCollection directly instead of catching Exception by @stanoMilan in https://github.com/shopsys/shopsys/pull/3374
-   [shopsys] moved friendly urls from project-base to the framework by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3368
-   [framework] flags are no longer all deleted and then recreated but are properly managed by @stanoMilan in https://github.com/shopsys/shopsys/pull/3404
-   [shopsys] moved FlagDetailFriendlyUrlDataProvider from project-base to framework by @stanoMilan in https://github.com/shopsys/shopsys/pull/3403

**Full Changelog**: https://github.com/shopsys/shopsys/compare/v14.0.0...v14.0.1

## [v14.0.0](https://github.com/shopsys/shopsys/compare/v13.0.0...v14.0.0) (2024-05-06)

### :sparkles: Enhancements and features

-   [project-base] repeat order improvements by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2876
-   [project-base] improve error handling for friendly URL pages based on API status codes by @sebaholesz in https://github.com/shopsys/shopsys/pull/2973
-   [project-base] customer's user cart is now overwritten with the incoming cart if logged in 3rd order step by @sebaholesz in https://github.com/shopsys/shopsys/pull/2978
-   [project-base] add Related Products tab section by @tvikito in https://github.com/shopsys/shopsys/pull/2885
-   [project-base] added USPs to product detail page by @sebaholesz in https://github.com/shopsys/shopsys/pull/2887
-   [project-base] added logic for ordering GTM events by @sebaholesz in https://github.com/shopsys/shopsys/pull/2921
-   [project-base] SEO categories with ignored filters by @sebaholesz in https://github.com/shopsys/shopsys/pull/2891
-   [shopsys] added ability to schedule each feed for specific time same way as crons by @TomasLudvik in https://github.com/shopsys/shopsys/pull/2922
-   [framework] annotation fixer: get property type from typehint when the annotation is missing by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/2934
-   [framework] handle image resizing by image proxy by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/2924
-   [framework] order info is now asynchronously sent to Heureka after order is created by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/2936
-   [shopsys] product recalculation and export is now done by queue by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2917
-   [project-base] more file types are now allowed to upload in wysiwyg editor by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2948
-   [frontend-api] add Category ID to CategoryHierarchyItem by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/2962
-   [framework] product recalculations priority queue by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/2981
-   [shopsys] added hreflang feature by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2970
-   [shopsys] emails are now sent via queue by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2998
-   [shopsys] moved Persoo feeds to Luigi's Box feeds by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3014
-   [project-base] improved product edit in grapesjs by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3008
-   [frontend-api] added ability to change a payment in an order by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/2952
-   [shopsys] added hreflang links to flag detail page by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3022
-   [shopsys] rewritten Persoo bundle to Luigi's Box bundle by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3037
-   [shopsys] Luigi's Box brand feed by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3045
-   [shopsys] Luigi's Box now searches through all the searchable entities by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3047
-   [shopsys] added personalization to Luigi's Box search by @tvikito in https://github.com/shopsys/shopsys/pull/3044
-   [shopsys] product filters are now provided from Luigi's Box by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3074
-   [shopsys] add customer option for Verified by Customers Heureka by @tvikito in https://github.com/shopsys/shopsys/pull/3098
-   [luigis-box] recommended products by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3099
-   [luigis-box] added parameter filter to Luigi's Box search by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3110
-   [project-base] Order summary transport & payment rounding price item by @KennyDaren in https://github.com/shopsys/shopsys/pull/2835
-   [framework] set products for export to elastic after changing quantity after completing, editing, or deleting order by @sspooky13 in https://github.com/shopsys/shopsys/pull/2587
-   [project-base] Add instant skeletons by @tvikito in https://github.com/shopsys/shopsys/pull/2863
-   [shopsys] added core for dispatch/consume system by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2907
-   [project-base] add additional skeletons by @tvikito in https://github.com/shopsys/shopsys/pull/2906
-   [shopsys] added dead letter queue by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2958
-   [project-base] add scroll to cart order pick up place popup by @tvikito in https://github.com/shopsys/shopsys/pull/2979
-   [project-base] last visited products by @TomasGottvald in https://github.com/shopsys/shopsys/pull/2716
-   [framework] entity changes log by @RostislavKreisinger in https://github.com/shopsys/shopsys/pull/2980
-   [project-base] add swipe handlers to our products slider by @tvikito in https://github.com/shopsys/shopsys/pull/2996
-   [project-base] add hreflang links to Head by @tvikito in https://github.com/shopsys/shopsys/pull/3005
-   [frontend-api] add display timezone to SettingsQuery by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/2977
-   [project-base] recommended products skeleton by @sebaholesz in https://github.com/shopsys/shopsys/pull/3138
-   [project-base] breadcrumb without url is no longer a link by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2881

### :bug: Bug Fixes

-   [framework] fixed access to nullable country by @stanoMilan in https://github.com/shopsys/shopsys/pull/2370
-   [project-base] fixed undefined window error by @sebaholesz in https://github.com/shopsys/shopsys/pull/2882
-   [project-base] improvements of product list in grapesjs by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2879
-   [project-base] fixed ProductsQuery Gatling simulation to wait for http response by @TomasLudvik in https://github.com/shopsys/shopsys/pull/2908
-   [project-base] search on search page is now not called if search query is empty by @sebaholesz in https://github.com/shopsys/shopsys/pull/2895
-   [framework] RedisClientFacade::contains() now throws exception when Redis is in multimode by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/2915
-   [shopsys] improved storefront translations cache invalidation by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2949
-   [project-base] fix broken drag and drop in GrapesJS in Safari by @sebaholesz in https://github.com/shopsys/shopsys/pull/2966
-   [framework] admin: fixed flag filter in product advanced search by @TomasLudvik in https://github.com/shopsys/shopsys/pull/2989
-   [project-base] grapesjs product catnums field is now text input by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2994
-   [project-base] resolve unwanted links and http iframe in datafixtures by @pk16011990 in https://github.com/shopsys/shopsys/pull/2751
-   [frontend-api] FE API Advert.catgories field returns visible categories only by @malyMiso in https://github.com/shopsys/shopsys/pull/2701
-   [shopsys] unset variant is now automatically exported by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3016
-   [framework] added unique constraint to cart by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3017
-   [shopsys] luigis box product feed now correctly exports all products by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3028
-   [project-base] added missing cron instances to deploy-project.sh by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3036
-   [project-base] removed duplicate update payment mutation call on order payment confirmation page by @sebaholesz in https://github.com/shopsys/shopsys/pull/3025
-   [project-base] fixed non-working sentry logging on SF by @sebaholesz in https://github.com/shopsys/shopsys/pull/3034
-   [shopsys] renamed blog article publishedAt elastic field to fix elasticsearch migration by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3038
-   [shopsys] hotfix: locked php-cs-fixer in version lower than 3.50 as new version causes errors in tests with current easy-coding-standards by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3042
-   [framework] ensure proper entity name is used within getClassMetadata call by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3068
-   [shopsys] adverts restricted by theirs display dates are now correctly displayed at specified dates by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3065
-   [project-base] fixed wrong PageType for articles in search by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3062
-   [shopsys] move migration 20200219145345 by @stanoMilan in https://github.com/shopsys/shopsys/pull/2975
-   [shopsys] unified composer conflicts by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3082
-   [framework] tweaks and fixes in moved migrations by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3097
-   [project-base] cart and product lists are not refetched while auth loading is active by @sebaholesz in https://github.com/shopsys/shopsys/pull/3096
-   [frontend-api] fixed ordering of search results by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3111
-   [shopsys] fixed saving empty article by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3113
-   [frontend-api] applied promo code is now taken into account in priceByTransportQuery and priceByPaymentQuery by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3118
-   [frontend-api] addProductToListMutation: ensure new product list is created with non-conflicting uuid by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3126
-   [frontend-api] cart is now correctly created for current customer user when carts are merged by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3112
-   [project-base] fix main variants in last visited products by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3139
-   [project-base] cart hydration fix by @sebaholesz in https://github.com/shopsys/shopsys/pull/3142
-   [framework] fix sending emails with attachments by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3146
-   [project-base] add equal spacing to the Category page by @tvikito in https://github.com/shopsys/shopsys/pull/2900
-   [project-base] fix sizes of product actions buttons by @tvikito in https://github.com/shopsys/shopsys/pull/2896
-   [project-base] fix Comparison for not logged in users by @tvikito in https://github.com/shopsys/shopsys/pull/2905
-   [project-base] fix set default delivery address country by @tvikito in https://github.com/shopsys/shopsys/pull/2902
-   [project-base] fix router server access error on PageGuard by @tvikito in https://github.com/shopsys/shopsys/pull/2909
-   [project-base] fix Cart list unit text by @tvikito in https://github.com/shopsys/shopsys/pull/2910
-   [project-base] Add display Transport and Payment description on desktop by @tvikito in https://github.com/shopsys/shopsys/pull/2930
-   [project-base] fix GrapesJS by @tvikito in https://github.com/shopsys/shopsys/pull/2927
-   [framework] fixed migration namespace by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2954
-   [framework] fixed running cron module when memory limit set to -1 by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2956
-   [shopsys] fixed feed generation during request by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2959
-   [project-base] fix Search results Blog Article link type by @tvikito in https://github.com/shopsys/shopsys/pull/2961
-   [project-base] fix Breadcrumbs navigation on customer order page by @tvikito in https://github.com/shopsys/shopsys/pull/2974
-   [project-base] fix Add to cart popup product navigation by @tvikito in https://github.com/shopsys/shopsys/pull/2976
-   [project-base] fix image sizes by @tvikito in https://github.com/shopsys/shopsys/pull/2968
-   [framework] fix of the framework package by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3029
-   [project-base] fix tests for singledomain application by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3030
-   [project-base] fix SEO page title and heading H1 14.0 by @tvikito in https://github.com/shopsys/shopsys/pull/3109
-   [framework] messenger: prevent errors when MESSENGER_TRANSPORT_DSN is empty by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3114
-   [project-base] friendly url fixes (v14.0) by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3125

### :hammer: Developer experience and refactoring

-   [project-base] Transport and payment page fetching state refactoring by @sebaholesz in https://github.com/shopsys/shopsys/pull/2807
-   [project-base] auth (loading) improvements by @sebaholesz in https://github.com/shopsys/shopsys/pull/2897
-   [project-base] category data fetching logic improvements by @sebaholesz in https://github.com/shopsys/shopsys/pull/2893
-   [project-base] cypress refactoring by @sebaholesz in https://github.com/shopsys/shopsys/pull/3023
-   [project-base] add possibility to change SF error verbosity for development by @sebaholesz in https://github.com/shopsys/shopsys/pull/2990
-   [project-base] added more verbose error messages when using logException on SF by @sebaholesz in https://github.com/shopsys/shopsys/pull/3018
-   [project-base] Improvements to Storefront typings by @sebaholesz in https://github.com/shopsys/shopsys/pull/3009
-   [shopsys] added test to keep elasticsearch converter and mapping in sync by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2880
-   [project-base] removed unnecessary default value for domainConfig by @sebaholesz in https://github.com/shopsys/shopsys/pull/2888
-   [shopsys] graphql validation errors now contain violation list in the additional data and allow change the log level by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2889
-   [project-base] Refactoring of various error-related matters on SF by @sebaholesz in https://github.com/shopsys/shopsys/pull/2871
-   [shopsys] all commands are now named via attribute by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2912
-   [shopsys] replaced custom application bootstraping with symfony/runtime by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2914
-   [framework] [frontend-api] universal product list by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/2901
-   [project-base] stores tests enhancements by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/2951
-   [shopsys] Elasticsearch: defined structure for ID by @sspooky13 in https://github.com/shopsys/shopsys/pull/2495
-   [shopsys] all relevant entities are now created with factory by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3004
-   [shopsys] fixed type annotation on collections by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3000
-   [shopsys] removed unused tsvector columns by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3007
-   [shopsys] split ElasticsearchIndexException into separate ones by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3003
-   [project-base] usps are now relevant and only on few products by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3006
-   [shopsys] removed entity's and dataobject's property typehints and typehints from getter/setter by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3001
-   [framework] change monolog error context key for cron error messages by @malyMiso in https://github.com/shopsys/shopsys/pull/2933
-   [storefront] added basic Symfony Toolbar for XHR request to easy opening profiler directly from storefront by @TomasLudvik in https://github.com/shopsys/shopsys/pull/2997
-   [framework] feed modules are now removed if appropriate feed no longer exists by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3024
-   [shopsys] removed no longer used functionality of measuring scripts by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3122
-   [shopsys] Add Prettier and ESlint plugins by @tvikito in https://github.com/shopsys/shopsys/pull/2874
-   [shopsys] markdown format ensured by prettier by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2892
-   [project-base] refactor ProductVariantsTable by @tvikito in https://github.com/shopsys/shopsys/pull/2899
-   [project-base] remove Heading component by @tvikito in https://github.com/shopsys/shopsys/pull/2894
-   [project-base] removed error logging for GTM safe push by @sebaholesz in https://github.com/shopsys/shopsys/pull/2920
-   [shopsys] stock and store management moved to shopsys/framework package by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2918
-   [shopsys] remove backend API by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/2937
-   [project-base] upgrade Node.js and PNPM by @tvikito in https://github.com/shopsys/shopsys/pull/2931
-   [project-base] package.json fix to minors by @sebaholesz in https://github.com/shopsys/shopsys/pull/2923
-   [shopsys] removed read model package by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2935
-   [shopsys] removed preorder and vendor delivery date features by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2942
-   [framework] remove constant.js by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/2969
-   [project-base] replace lightgallery with custom gallery by @tvikito in https://github.com/shopsys/shopsys/pull/2995
-   [shopsys] Upgraded to PHP 8.3 by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3002
-   [project-base] update repo dependencies by @tvikito in https://github.com/shopsys/shopsys/pull/3010
-   [framework] remove enums from the packages by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3073
-   [coding-standards] removed disallowed PHP 4 constructor type sniff by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2986
-   [project-base] fixed entity extension test by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3011
-   [shopsys] forbidden private visibility now takes into account constructor property promotion by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2944
-   [shopsys] monorepo coding standards: ensure all packages use the same cyclomatic complexity setting by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/2938

### :book: Documentation

-   [shopsys] improved upgrade instructions for read-model removal by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2947
-   [shopsys] added upgrade note for product recalculations via queue by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2955
-   [shopsys] added documentation about asynchronous processing by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2988
-   [shopsys] enhancement of the stylistics of the documentation and some texts by @pk16011990 in https://github.com/shopsys/shopsys/pull/2798
-   [shopsys] upgrade notes: add information about Docker version for image proxy by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/2957
-   [shopsys] upgrade notes: info about moved functionalities by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/2984
-   [project-base] upgrade notes: add missing info about rabbit on GitlabCI by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3063

### :rocket: Performance

-   [shopsys] increase speed of Product creation by @TomasLudvik in https://github.com/shopsys/shopsys/pull/2903
-   [framework] CategoryTreeSorting.init jquery UI performance by @malyMiso in https://github.com/shopsys/shopsys/pull/3013
-   [shospys] improved feed memory usage, iterated crons are now able to sleep before memory overflow and improved feed generation logging by @TomasLudvik in https://github.com/shopsys/shopsys/pull/2945

### :cloud: Infrastructure

-   [shopsys] added composer security check to GitHub Actions by @TomasLudvik in https://github.com/shopsys/shopsys/pull/2839
-   [shopsys] Packetery and GoPay are now enabled on review server by @TomasLudvik in https://github.com/shopsys/shopsys/pull/2869
-   [shopsys] added translations dump check by @TomasLudvik in https://github.com/shopsys/shopsys/pull/2868
-   [shopsys] test project-base with only one domain on GitHub actions by @TomasLudvik in https://github.com/shopsys/shopsys/pull/2877
-   [shopsys] project-base now checks also Storefront checks and runs Storefront with one domain in one domain check by @TomasLudvik in https://github.com/shopsys/shopsys/pull/2886
-   [shopsys] prepared infrastructure for rabbitmq by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2898
-   [project-base] added messenger configuration to deployed application by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2904
-   [shopsys] commands not used during installation, standards checking and running tests are now run on GitHub Actions to ensure all our commands are passing by @TomasLudvik in https://github.com/shopsys/shopsys/pull/2875
-   [shopsys] docker-compose: remove img proxy container name for CI review by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/2943
-   [shopsys] docker-compose: add rabbitMQ container for cypress tests on gitlab CI by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/2950
-   [project-base] messages are now consumed on gitlab ci review by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2953
-   [monorepo] alpha branch is now automatically split and deployed by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2982
-   [shopsys] GitHub actions now use default_branch set in repository instead of variable by @TomasLudvik in https://github.com/shopsys/shopsys/pull/2985
-   [project-base] fixed path to codeception logs in project-base by @TomasLudvik in https://github.com/shopsys/shopsys/pull/2991
-   [shopsys] run review job is now killed after 10 minutes by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2913
-   [releaser] UpdateUpgradeReleaseWorker: update instructions by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/2932
-   [shopsys] replace versions in alpha branch is now independent of the original version by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2992
-   [project-base] fixed consumer deployment by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3019
-   [shopsys] bump version of upload/download artifact actions by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3021

## New Contributors

-   @KennyDaren made their first contribution in https://github.com/shopsys/shopsys/pull/2835

**Full Changelog**: https://github.com/shopsys/shopsys/compare/v13.0.0...v14.0.0
