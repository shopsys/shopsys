# Changelog for 15.0.x

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
-   [shopsys/administration](https://github.com/shopsys/administration)

Packages are formatted by release version.
You can see all the changes done to the package that you carry about with this tree.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/) and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html) as explained in the [Backward Compatibility Promise](https://docs.shopsys.com/en/latest/contributing/backward-compatibility-promise/).

<!-- Add generated changelog below this line -->

<!-- Release notes generated using configuration in .github/release.yml at 15.0 -->

## What's Changed

### :construction: Changes that require additional implementation if you are using Frontend API

-   [shopsys] FE API delivery address mutations by @malyMiso in https://github.com/shopsys/shopsys/pull/3265
-   [frontend-api] reorganize GraphQL type files into subdirectories for better structure by @malyMiso in https://github.com/shopsys/shopsys/pull/3272
-   [shopsys] role `ROLE_ALL_API` sees all customer user orders from common customer by @malyMiso in https://github.com/shopsys/shopsys/pull/3296
-   [shopsys] added mutations and queries to work with customer structure by @malyMiso in https://github.com/shopsys/shopsys/pull/3286
-   [shopsys] Send mail adding new customer user to customer by @malyMiso in https://github.com/shopsys/shopsys/pull/3291
-   [shopsys] show prices for customer user with role `ROLE_API_CUSTOMER_SEES_PRICES` by @malyMiso in https://github.com/shopsys/shopsys/pull/3319

### :sparkles: Enhancements and features

-   [shopsys] product recalculations scoping by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3051
-   [frontend-api] create order with preselected delivery address by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3105
-   [framework] dispatch product stocks export after Setting::TRANSFER_DAYS_BETWEEN_STOCKS is set by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3104
-   [shopsys] port of Luigi's Box recommended products and parametric filters for search page (from v14) by @sebaholesz in https://github.com/shopsys/shopsys/pull/3136
-   [project-base] SF defer parts of DOM by @sebaholesz in https://github.com/shopsys/shopsys/pull/3089
-   [shopsys] display error messages in admin if legal conditions articles are not set by @AndrejBlaho in https://github.com/shopsys/shopsys/pull/3128
-   [framework] load products iteratively while generating image sitemaps by @AndrejBlaho in https://github.com/shopsys/shopsys/pull/3144
-   [shopsys] include domain sale exclusion in product querying more appropriately by @AndrejBlaho in https://github.com/shopsys/shopsys/pull/3141
-   [shopsys] refactored order creation by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3084
-   [framework] images are no longer processed by PHP to avoid quality decrease by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3169
-   [project-base] added option to migrate persist store by @sebaholesz in https://github.com/shopsys/shopsys/pull/3171
-   [project-base] Luigi's Box search relations fix by @sebaholesz in https://github.com/shopsys/shopsys/pull/3217
-   [shopsys] SF optimizations based on projects by @sebaholesz in https://github.com/shopsys/shopsys/pull/3222
-   [project-base] implemented new banners slider by @sebaholesz in https://github.com/shopsys/shopsys/pull/3240
-   [luigis-box] parameter filters are now ordered by theirs ordering priority by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3233
-   [shopsys] blog article now displays blog category tree by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3237
-   [shopsys] login via social media by @sspooky13 in https://github.com/shopsys/shopsys/pull/3154
-   [shopsys] related order items by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3229
-   [shopsys] Admin can manage customer structure on B2B domains by @malyMiso in https://github.com/shopsys/shopsys/pull/3261
-   [framework] slider parameter values now have mandatory parameter numeric value by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3262
-   [project-base] added create and edit customer delivery address by @chlebektomas in https://github.com/shopsys/shopsys/pull/3290
-   [project-base] Add search parameters to queries by @JanMolcik in https://github.com/shopsys/shopsys/pull/3298
-   [shopsys] product files support by @KennyDaren in https://github.com/shopsys/shopsys/pull/3288
-   [framework] admin parameter form now validates parameter name uniqueness within a given locale by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3317
-   [frontend-api] data layer: customer user login types by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3276
-   [framework] admin can manage customer user group roles by @malyMiso in https://github.com/shopsys/shopsys/pull/3323
-   [shopsys] administrator now can see list of last ten orders on customer edit page by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3365
-   [shopsys] GoPay configuration is based on domains instead of locale by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3308
-   [framework] Enable pagination and quick search in blog articles in the admin by @martin-baca-shopsys in https://github.com/shopsys/shopsys/pull/3393
-   [shopsys] complaint support by @KennyDaren in https://github.com/shopsys/shopsys/pull/3295
-   [shopsys] administration of complaints by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3354
-   [framework] edit complaint items in admin by @malyMiso in https://github.com/shopsys/shopsys/pull/3388
-   [shopsys] usersnap widget by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3408
-   [shopsys] complaint list by @KennyDaren in https://github.com/shopsys/shopsys/pull/3362
-   [project-base] Add company user administration to customer profile by @JanMolcik in https://github.com/shopsys/shopsys/pull/3353
-   [framework] limited user can't use free transport and gateway payments by @malyMiso in https://github.com/shopsys/shopsys/pull/3355
-   [frontend-api] limited user can't use filter by price and order by price by @malyMiso in https://github.com/shopsys/shopsys/pull/3356
-   [shopsys] Sales representatives by @AndrejBlaho in https://github.com/shopsys/shopsys/pull/3301
-   [framework] Heureka: send order number instead of order id by @sspooky13 in https://github.com/shopsys/shopsys/pull/3407
-   [shopsys] added complaint detail url to mail templates by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3445
-   [shopsys] product filters luigis box 15.0 by @tvikito in https://github.com/shopsys/shopsys/pull/3095
-   [shopsys] add customer option for Verified by Customers Heureka (v15.0) by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3121
-   [shopsys] remove delete button from banner slider image when editing by @AndrejBlaho in https://github.com/shopsys/shopsys/pull/3247
-   [frontend-api] social network login types config by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3277
-   [project-base] social network login: add default values for ENV client IDs and secrets by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3348
-   [shopsys] remove productList and productListMiddle advert positions by @AndrejBlaho in https://github.com/shopsys/shopsys/pull/3335
-   [framework] identify company and user by icon in customer list by @malyMiso in https://github.com/shopsys/shopsys/pull/3366
-   [project-base] limiting image size options propagated for resizing in CDN to predefined set of options by @AndrejBlaho in https://github.com/shopsys/shopsys/pull/3349
-   [framework] complaint mail templates by @AndrejBlaho in https://github.com/shopsys/shopsys/pull/3364
-   [framework] user is logged out after role change by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3459
-   [project-base] Switch from seznam maps to use google maps by @JanMolcik in https://github.com/shopsys/shopsys/pull/3268
-   [framework] change limit for description in articles and blog articles to no limit by @sspooky13 in https://github.com/shopsys/shopsys/pull/3049
-   [project-base] added privacy policy checkbox to contact form by @JanMolcik in https://github.com/shopsys/shopsys/pull/3219
-   [project-base] added more OP metatags by @JanMolcik in https://github.com/shopsys/shopsys/pull/3228

### :bug: Bug Fixes

-   [project-base] fixed promo code mass generation by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3039
-   [project-base] fixed display advert in category by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3040
-   [project-base] fixed removing promo code from cart by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3043
-   [framework] fixed situation when placeholder was not set for selected locale by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3060
-   [project-base] deploy is now not dependent only on commit but also on pipeline id by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3061
-   [shopsys] Cypress make command fix by @sebaholesz in https://github.com/shopsys/shopsys/pull/3090
-   [project-base] cypress stability fixes by @sebaholesz in https://github.com/shopsys/shopsys/pull/3093
-   [project-base] fixed incorrect keys in cache exchange config by @sebaholesz in https://github.com/shopsys/shopsys/pull/3094
-   [project-base] cart and product lists are not refetched while auth loading is active by @sebaholesz in https://github.com/shopsys/shopsys/pull/3106
-   [project-base] friendly url fixes by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3115
-   [framework] messenger: prevent errors when MESSENGER_TRANSPORT_DSN is empty (v15.0) by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3127
-   [project-base] fix main variants in last visited products (v15.0) by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3140
-   [project-base] Port of cart hydration fix to v15 by @sebaholesz in https://github.com/shopsys/shopsys/pull/3143
-   [framework] fix issues reported by phpstan by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3134
-   [frontend-api] applied promo code is now taken into account in priceByTransportQuery and priceByPaymentQuery (v15.0) by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3149
-   [framework] fix sending emails with attachments (v15.0) by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3151
-   [framework] fixed money type with null value in entity log by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3152
-   [project-base] removed duplicated price display on product detail page by @sebaholesz in https://github.com/shopsys/shopsys/pull/3150
-   [framework] changed email from which contact form is sent so emails are not catched by spam filters by @AndrejBlaho in https://github.com/shopsys/shopsys/pull/3124
-   [project-base] removed invalid cache invalidation when adding to product list by @sebaholesz in https://github.com/shopsys/shopsys/pull/3172
-   [project-base] fixed blogarticle demo data by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3182
-   [project-base] fixed translation on customer's edit profile page by @sebaholesz in https://github.com/shopsys/shopsys/pull/3179
-   [framework] fix VatDeletionCronModule by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3195
-   [framework] don't add manually added order item into repeat order cart by @AndrejBlaho in https://github.com/shopsys/shopsys/pull/3212
-   [framework] cleanup DB schema by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3223
-   [shopsys] nominal promo code discount is limited by applicable products by @AndrejBlaho in https://github.com/shopsys/shopsys/pull/3227
-   [framework] fix calculation of transport price by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3255
-   [project-base] Fixed unknown error in navigation by @JanMolcik in https://github.com/shopsys/shopsys/pull/3259
-   [framework] replaced unsupported currency SLL with new SLE by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3264
-   [luigis-box] facets are now ordered by storefront and also include applied parameter filters by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3220
-   [framework] multidomain migration trait now returns only unique locales by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3282
-   [project-base] closed day data fixture now works for domains without store by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3283
-   [project-base] fixed datafixtures for more than two domains by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3284
-   [project-base] updated filter slider apperance by @chlebektomas in https://github.com/shopsys/shopsys/pull/3300
-   [project-base] Fixed skeleton loaders while navigating through main navigation by @JanMolcik in https://github.com/shopsys/shopsys/pull/3287
-   [project-base] added unification company user form validation by @chlebektomas in https://github.com/shopsys/shopsys/pull/3299
-   [framework] fix blog category without articles by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3292
-   [project-base] updated contact info merge logic to handle empty object by @chlebektomas in https://github.com/shopsys/shopsys/pull/3307
-   [project-base] fixed issue with invalid ssrExchange initialization by @sebaholesz in https://github.com/shopsys/shopsys/pull/3321
-   [project-base] fixed responsive design in mobile menu and on cart page by @sebaholesz in https://github.com/shopsys/shopsys/pull/3324
-   [project-base] storefront cypress is now removed after cypress tests finish by @sebaholesz in https://github.com/shopsys/shopsys/pull/3339
-   [project-base] design fixes by @sebaholesz in https://github.com/shopsys/shopsys/pull/3331
-   [project-base] added skeleton for registration page by @chlebektomas in https://github.com/shopsys/shopsys/pull/3342
-   [project-base] updated load more skeleton by @chlebektomas in https://github.com/shopsys/shopsys/pull/3346
-   [project-base] packetery fixes by @sebaholesz in https://github.com/shopsys/shopsys/pull/3329
-   [framework] the price filter now takes into account the pricing group by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3361
-   [shopsys] packetery delivery address is no longer saved as delivery address for customer user by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3333
-   [shopsys] removed dependency on App from frontend-api package by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3350
-   [project-base] updated artcle product slider arrows by @chlebektomas in https://github.com/shopsys/shopsys/pull/3345
-   [shopsys] fixed image resolving for transport in FrontendAPI by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3377
-   [project-base] fix product count in autocomplete by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3370
-   [shopsys] fixed duplicate addDomain to query builder by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3384
-   [project-base] redesigned radio and inverted button hover by @chlebektomas in https://github.com/shopsys/shopsys/pull/3358
-   [framework] FileUpload: set position for the new images only by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3381
-   [framework] files picker fix and tweak by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3385
-   [project-base] data fixtures: load translations for all locales where required by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3391
-   [shopsys] moved migration of stock settings from project-base to framework by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3401
-   [framework] admin can remove an image and upload a new one by @malyMiso in https://github.com/shopsys/shopsys/pull/3166
-   [project-base] updated products list sync with open tabs by @chlebektomas in https://github.com/shopsys/shopsys/pull/3341
-   [framework] fixed wrong columns used in migration Version20240102112523 by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3415
-   [framework] fixed redundant log for the Money type if the scale of compared object was different by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3420
-   [project-base] fix usersnap env variable name by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3429
-   [monorepo] Fix cancel old reviews script by @henzigo in https://github.com/shopsys/shopsys/pull/3428
-   [project-base] make tests resistant against admin locale change by @AndrejBlaho in https://github.com/shopsys/shopsys/pull/3430
-   [project-base] fixed handling of 401 errors on dynamic page by @KennyDaren in https://github.com/shopsys/shopsys/pull/3386
-   [project-base] fixed invalid sort input by @chlebektomas in https://github.com/shopsys/shopsys/pull/3440
-   [project-base] updated store opening hours alignment by @chlebektomas in https://github.com/shopsys/shopsys/pull/3438
-   [project-base] updated responsive toast by @chlebektomas in https://github.com/shopsys/shopsys/pull/3437
-   [framework] fixed GoPay no longer failing on missing key of enabledSwifts by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3447
-   [project-base] added condition for unsupported broadcast channel by @chlebektomas in https://github.com/shopsys/shopsys/pull/3448
-   [shopsys] fixed duplicate video forms in administration product by @sspooky13 in https://github.com/shopsys/shopsys/pull/3422
-   [project-base] removed hp headlines when no data available by @chlebektomas in https://github.com/shopsys/shopsys/pull/3436
-   [project-base] added error message for edit profile duplicate company number by @chlebektomas in https://github.com/shopsys/shopsys/pull/3449
-   [framework] Fix persist on null object in create delivery address by @stanoMilan in https://github.com/shopsys/shopsys/pull/2350
-   [shopsys] rename reserved database function normalize to non-reserved name normalized by @sspooky13 in https://github.com/shopsys/shopsys/pull/3072
-   [framework] fix seo pages urls by @sspooky13 in https://github.com/shopsys/shopsys/pull/3079
-   [framework] tweaks and fixes in moved migrations (v15.0) by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3102
-   [frontend-api] addProductToListMutation: ensure new product list is created with non-conflicting uuid (v15.0) by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3129
-   [project-base] fix SEO page title and heading H1 15.0 by @tvikito in https://github.com/shopsys/shopsys/pull/3108
-   [shopsys] pinned problematic versions by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3162
-   [shopsys] TypeAdvertPosition description typo fix by @techi602 in https://github.com/shopsys/shopsys/pull/3167
-   [shopsys] fix app class used outside of project base by @AndrejBlaho in https://github.com/shopsys/shopsys/pull/3173
-   [project-base] fix slider by @tvikito in https://github.com/shopsys/shopsys/pull/3130
-   [project-base] Hydration error in wish list & product comparison page. by @JanMolcik in https://github.com/shopsys/shopsys/pull/3243
-   [shopsys] new cart items are added to the end of cart item list by @AndrejBlaho in https://github.com/shopsys/shopsys/pull/3231
-   [project-base] added wheel click condition for safari navigation loading by @chlebektomas in https://github.com/shopsys/shopsys/pull/3260
-   [shopsys] fix builds of split packages by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3273
-   [framework] exclude main variant from selling when it has no selling or visible variants by @malyMiso in https://github.com/shopsys/shopsys/pull/3303
-   [project-base] fix customer user country presentation in case of null value by @AndrejBlaho in https://github.com/shopsys/shopsys/pull/3314
-   [framework] do not show main variant price that is excluded from sale by @malyMiso in https://github.com/shopsys/shopsys/pull/3328
-   [project-base] forbidden grapesjs image resize by @chlebektomas in https://github.com/shopsys/shopsys/pull/3351
-   [project-base] fix page skeletons by @TomasGottvald in https://github.com/shopsys/shopsys/pull/3392
-   [project-base] Fix common cypress errors by @JanMolcik in https://github.com/shopsys/shopsys/pull/3424
-   [framework] fixed incorrect usage of frontend-api class in framework package by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3453
-   [project-base] fixed invalid slider input by @chlebektomas in https://github.com/shopsys/shopsys/pull/3441
-   [project-base] Fixed hiding prices for limited user by @JanMolcik in https://github.com/shopsys/shopsys/pull/3452
-   [project-base] order process fixes by @sebaholesz in https://github.com/shopsys/shopsys/pull/3032

### :hammer: Developer experience and refactoring

-   [shopsys] removed deprecated properties from product entity by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3027
-   [project-base] remove duplicate test ENV by @malyMiso in https://github.com/shopsys/shopsys/pull/3059
-   [project-base] added doctrine backtrace collecting in dev mode by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3055
-   [project-base]changed link URL for Catalog navigation element by @sspooky13 in https://github.com/shopsys/shopsys/pull/3057
-   [project-base] added visitAndWaitForStableDOM for visiting pages in cypress by @sebaholesz in https://github.com/shopsys/shopsys/pull/3071
-   [shopsys] getReference(ForDomain) methods now accept the entity name to improve the returned type awareness by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3070
-   [project-base] SF bundle size reduction changes by @sebaholesz in https://github.com/shopsys/shopsys/pull/3077
-   [project-base] GQL generated files split by @sebaholesz in https://github.com/shopsys/shopsys/pull/3080
-   [project-base] replaced UUID pools by generation from entities data by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3075
-   [project-base] SF large files split by @sebaholesz in https://github.com/shopsys/shopsys/pull/3081
-   [project-base] codegen types and values differentiation by @sebaholesz in https://github.com/shopsys/shopsys/pull/3085
-   [shopsys] Not null data in delivery address by @sspooky13 in https://github.com/shopsys/shopsys/pull/2494
-   [framework] remove enums from the packages (v15.0) by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3103
-   [shopsys] open cypress tests is now possible on WSL by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3116
-   [shopsys] move order and cart related logic from project-base to packages by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3088
-   [shopsys] removed no longer used functionality of measuring scripts by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3137
-   [shopsys] removed no longer used operator info by @AndrejBlaho in https://github.com/shopsys/shopsys/pull/3133
-   [project-base] cypress tests extra scenarios by @sebaholesz in https://github.com/shopsys/shopsys/pull/3052
-   [project-base] minor improvements to cypress tests by @sebaholesz in https://github.com/shopsys/shopsys/pull/3163
-   [shopsys] rename variable differentDeliveryAddress into isDeliveryAddressDifferentFromBilling by @AndrejBlaho in https://github.com/shopsys/shopsys/pull/3161
-   [project-base] simple navigation images are now blacked-out during cypress tests by @sebaholesz in https://github.com/shopsys/shopsys/pull/3174
-   [shopsys] added blackfire profiling configuration and instructions by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3168
-   [project-base] parameter data fixture refactoring by @AndrejBlaho in https://github.com/shopsys/shopsys/pull/3170
-   [shopsys] a little spring cleanup by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3157
-   [shopsys] upgrade easy coding standards by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3192
-   [project-base] useContext refactoring by @sebaholesz in https://github.com/shopsys/shopsys/pull/3176
-   [project-base] minor array keys fix by @sebaholesz in https://github.com/shopsys/shopsys/pull/3178
-   [shopsys] renamed cookie article setting to user consent policy by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3181
-   [project-base] product data fixture refactoring by @AndrejBlaho in https://github.com/shopsys/shopsys/pull/3187
-   [shopsys] move navigation feature from project-base to the packages by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3218
-   [project-base] order process refactoring by @sebaholesz in https://github.com/shopsys/shopsys/pull/3155
-   [project-base] refactoring of SEO categories - removed ReadyCategorySeoMixDataForForm by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3214
-   [shopsys] move some parameter attributes and filter functionality from project-base to framework and frontend-api bundles by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3221
-   [shopsys] moving features from project-base to the packages by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3210
-   [shopsys] resolved deprecations by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3209
-   [shopsys] split upgrade notes by @AndrejBlaho in https://github.com/shopsys/shopsys/pull/3194
-   [shopsys] easier mail template extending by @AndrejBlaho in https://github.com/shopsys/shopsys/pull/3232
-   [project-base] added functionality for notImplementedYet by @sebaholesz in https://github.com/shopsys/shopsys/pull/3238
-   [shopsys] update of several packages and different way to install Phing by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3213
-   [framework] annotation fixer now works for all Shopsys packages by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3230
-   [project-base] Restyle autocomplete, implement small variant of product item by @VaniaToper in https://github.com/shopsys/shopsys/pull/3254
-   [shopsys] Create components for forms and restyle forms on eshop by @VaniaToper in https://github.com/shopsys/shopsys/pull/3245
-   [shopsys] Twig templates cleanup by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3257
-   [shopsys] removed unused styleguide backing code by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3281
-   [luigis-box] added additional information to LuigisBoxClient failed requests by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3278
-   [shopsys] refactored product price form type by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3279
-   [framework] refactoring: OrderStatus::$type is now string by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3313
-   [project-base] Storefront color system implementation by @sebaholesz in https://github.com/shopsys/shopsys/pull/3311
-   [shopsys] datafixtures can be loaded only for some domains by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3293
-   [project-base] cypress improvements by @sebaholesz in https://github.com/shopsys/shopsys/pull/3337
-   [shopsys] remove unused constraint by @malyMiso in https://github.com/shopsys/shopsys/pull/3252
-   [migrations] Generate command has a new option with generate empty migrations by @stanoMilan in https://github.com/shopsys/shopsys/pull/2878
-   [framework] Image::$position is not nullable anymore by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3343
-   [shopsys] moved two attributes of ProductFormType from project-base to framework by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3376
-   [shopsys] moved category parameters to framework package by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3387
-   [project-base] Say goodbye to Yennefer of Vengerberg by @malyMiso in https://github.com/shopsys/shopsys/pull/3224
-   [shopsys] moved friendly urls from project-base to the framework by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3369
-   [framework] moved FlagDetailFriendlyUrlDataProvider from project-base to framework by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3416
-   [framework] fixed EntityLogger to no longer empty collection that is cleared and filled on every update by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3418
-   [framework] improved formatting of Entity logs by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3423
-   [framework] tweak complaint status migration by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3432
-   [shopsys] Remove transport type entity and replace it with enum class by @sspooky13 in https://github.com/shopsys/shopsys/pull/3431
-   [framework] InMemoryCache for easier managing of caches by @RostislavKreisinger in https://github.com/shopsys/shopsys/pull/3031
-   [shopsys] added query/mutation name to URL and headers by @sebaholesz in https://github.com/shopsys/shopsys/pull/3041
-   [shopsys] removed already unused OrderFlow by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3046
-   [coding-standards] attributes are now allowed after phpdoc block by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3053
-   [shopsys] removed unused front order classes by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3054
-   [project-base] refactor mobile menu by @tvikito in https://github.com/shopsys/shopsys/pull/3035
-   [shopsys] separate order item types by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3056
-   [shopsys] Elasticsearch structure vol 2.0 by @sspooky13 in https://github.com/shopsys/shopsys/pull/2567
-   [shopsys] Upgraded yaml standards package vol. 2 by @sspooky13 in https://github.com/shopsys/shopsys/pull/2278
-   [shopsys] bump versions of SF packages to fix security issues by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3191
-   [framework] improved OrderSequence types by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3206
-   [project-base] unified INTERNAL_ENDPOINT usage by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3205
-   [project-base] add nominal promo code to demo data by @AndrejBlaho in https://github.com/shopsys/shopsys/pull/3197
-   [project-base] change free transport limit in demo data by @AndrejBlaho in https://github.com/shopsys/shopsys/pull/3199
-   [project-base] add packeta type transport to demo data by @AndrejBlaho in https://github.com/shopsys/shopsys/pull/3198
-   [project-base] added cypress blackout and retries by @JanMolcik in https://github.com/shopsys/shopsys/pull/3236
-   [project-base] added dynamic year to footer copyright by @chlebektomas in https://github.com/shopsys/shopsys/pull/3248
-   [project-base] Split transport stores into separate query by @JanMolcik in https://github.com/shopsys/shopsys/pull/3251
-   [shopsys] Remove akeneo and associated features by @AndrejBlaho in https://github.com/shopsys/shopsys/pull/3258
-   [project-base] refactored form validations by @chlebektomas in https://github.com/shopsys/shopsys/pull/3263
-   [framework] removed obsolete front logout related code by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3274
-   [framework] address, name and some other user data is now nullable by @AndrejBlaho in https://github.com/shopsys/shopsys/pull/3285
-   [project-base] update yup package to v 1.4 by @TomasGottvald in https://github.com/shopsys/shopsys/pull/3367
-   [project-base] Reset cache of all queries after turning off maintenance… by @TomasGottvald in https://github.com/shopsys/shopsys/pull/3383
-   [project-base] added more verbose error messages for mutations by @sebaholesz in https://github.com/shopsys/shopsys/pull/3033
-   [project-base] cookies store smarter init by @sebaholesz in https://github.com/shopsys/shopsys/pull/3145

### :book: Documentation

-   [docs] updated the infrastructure schema by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3175

### :art: Design & appearance

-   [project-base] add developer styleguide by @tvikito in https://github.com/shopsys/shopsys/pull/3083
-   [project-base] recommended products skeleton (port to v15) by @sebaholesz in https://github.com/shopsys/shopsys/pull/3147
-   [project-base] redesign product page + some base elements by @tvikito in https://github.com/shopsys/shopsys/pull/3132
-   [shopsys] Restyle orders by @VaniaToper in https://github.com/shopsys/shopsys/pull/3123
-   [project-base] added telephone to saved delivery addresses in order 3rd step by @sebaholesz in https://github.com/shopsys/shopsys/pull/3235
-   [project-base] updated product detail open gallery image size by @chlebektomas in https://github.com/shopsys/shopsys/pull/3266
-   [project-base] added info message to main product variant by @chlebektomas in https://github.com/shopsys/shopsys/pull/3267
-   [framework] domains in admin now have colors up to 40 domains by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3280
-   [project-base] added delay to open navigation menu by @chlebektomas in https://github.com/shopsys/shopsys/pull/3270
-   [project-base] updated product detail responsive by @chlebektomas in https://github.com/shopsys/shopsys/pull/3309
-   [project-base] added opening hours to packetery selected transport label by @sebaholesz in https://github.com/shopsys/shopsys/pull/3332
-   [project-base] comparison and wishlist icon changes by @sebaholesz in https://github.com/shopsys/shopsys/pull/3360
-   [project-base] redesigned checkbox by @chlebektomas in https://github.com/shopsys/shopsys/pull/3352
-   [project-base] updated promocode button styles by @chlebektomas in https://github.com/shopsys/shopsys/pull/3302
-   [shopsys] limited item counts in sliders by @AndrejBlaho in https://github.com/shopsys/shopsys/pull/3244
-   [project-base] change gallery background by @TomasGottvald in https://github.com/shopsys/shopsys/pull/3396
-   [project-base] redesigned sort menu by @chlebektomas in https://github.com/shopsys/shopsys/pull/3390
-   [project-base] Design store detail by @henzigo in https://github.com/shopsys/shopsys/pull/3398
-   [project-base] unify z-indexes from manual added by @TomasGottvald in https://github.com/shopsys/shopsys/pull/3382
-   [shopsys] Restyle order detail by @VaniaToper in https://github.com/shopsys/shopsys/pull/3164
-   [project-base] Redesign store list by @henzigo in https://github.com/shopsys/shopsys/pull/3399
-   [project-base] refresh usermenu styling by @TomasGottvald in https://github.com/shopsys/shopsys/pull/3373
-   [project-base] added store image to order by @chlebektomas in https://github.com/shopsys/shopsys/pull/3414
-   [project-base] basket hover popup by @TomasGottvald in https://github.com/shopsys/shopsys/pull/3389

### :rocket: Performance

-   [frontend-api] main blog category URL is now available via settings query by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3087
-   [shopsys] top products are now in elasticsearch by @AndrejBlaho in https://github.com/shopsys/shopsys/pull/3242
-   [shopsys] few administration optimizations by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3246
-   [project-base] adverts query optimisation by @JanMolcik in https://github.com/shopsys/shopsys/pull/3211
-   [shopsys] performance improvement on product list/edit by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3304
-   [project-base] Disable middleware prefetch by @henzigo in https://github.com/shopsys/shopsys/pull/3325
-   [framework] Added indexes for columns which were used for order by and where in entites TransferIssue and CronModuleRun by @sspooky13 in https://github.com/shopsys/shopsys/pull/3048
-   [shopsys] restrict limit of requests how many can robot ask from eshop by @sspooky13 in https://github.com/shopsys/shopsys/pull/2820
-   [project-base] added disallow sort to robots.txt by @chlebektomas in https://github.com/shopsys/shopsys/pull/3250
-   [project-base] added redis timeouts by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3226

### :cloud: Infrastructure

-   [php-image] upgraded nodejs in php-image to version 20 by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3119
-   [project-base] deploy-project.sh: add missing SEZNAM_CLIENT_SECRET to fix Seznam social login by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3315
-   [shopsys] upgraded nginx to version 1.27 by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3347
-   [shopsys] cluster first deploy is now independent on previous state by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3201

### :warning: Security

-   [shopsys] added security headers for more safety by @sspooky13 in https://github.com/shopsys/shopsys/pull/3050
-   [project-base] Added secure to cookies on https protocol by @JanMolcik in https://github.com/shopsys/shopsys/pull/3253
-   [shopsys] immediate access token revocation by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3417
-   [shopsys] updated twig to latest version in order to resolve security issues by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3443
-   [project-base] updated repo dependencies by @chlebektomas in https://github.com/shopsys/shopsys/pull/3409

## New Contributors

-   @AndrejBlaho made their first contribution in https://github.com/shopsys/shopsys/pull/3133
-   @techi602 made their first contribution in https://github.com/shopsys/shopsys/pull/3167
-   @JanMolcik made their first contribution in https://github.com/shopsys/shopsys/pull/3219
-   @VaniaToper made their first contribution in https://github.com/shopsys/shopsys/pull/3123
-   @chlebektomas made their first contribution in https://github.com/shopsys/shopsys/pull/3248
-   @martin-baca-shopsys made their first contribution in https://github.com/shopsys/shopsys/pull/3393

**Full Changelog**: https://github.com/shopsys/shopsys/compare/v14.0.1...v15.0.0
