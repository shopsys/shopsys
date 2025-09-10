# Changelog for 17.0.x

All notable changes that change in some way the behavior of any of our packages are maintained by the monorepo repository.

There is a list of all the repositories maintained by the monorepo:

- [shopsys/framework](https://github.com/shopsys/framework)
- [shopsys/project-base](https://github.com/shopsys/project-base)
- [shopsys/shopsys](https://github.com/shopsys/shopsys)
- [shopsys/coding-standards](https://github.com/shopsys/coding-standards)
- [shopsys/form-types-bundle](https://github.com/shopsys/form-types-bundle)
- [shopsys/http-smoke-testing](https://github.com/shopsys/http-smoke-testing)
- [shopsys/migrations](https://github.com/shopsys/migrations)
- [shopsys/monorepo-tools](https://github.com/shopsys/monorepo-tools)
- [shopsys/plugin-interface](https://github.com/shopsys/plugin-interface)
- [shopsys/brand-feed-luigis-box](https://github.com/shopsys/brand-feed-luigis-box)
- [shopsys/category-feed-luigis-box](https://github.com/shopsys/category-feed-luigis-box)
- [shopsys/product-feed-google](https://github.com/shopsys/product-feed-google)
- [shopsys/product-feed-mergado](https://github.com/shopsys/product-feed-mergado)
- [shopsys/product-feed-heureka](https://github.com/shopsys/product-feed-heureka)
- [shopsys/product-feed-heureka-delivery](https://github.com/shopsys/product-feed-heureka-delivery)
- [shopsys/product-feed-zbozi](https://github.com/shopsys/product-feed-zbozi)
- [shopsys/product-feed-luigis-box](https://github.com/shopsys/product-feed-luigis-box)
- [shopsys/article-feed-luigis-box](https://github.com/shopsys/article-feed-luigis-box)
- [shopsys/biome-config](https://github.com/shopsys/biome-config)
- [shopsys/google-cloud-bundle](https://github.com/shopsys/google-cloud-bundle)
- [shopsys/s3-bridge](https://github.com/shopsys/s3-bridge)
- [shopsys/frontend-api](https://github.com/shopsys/frontend-api)
- [shopsys/php-image](https://github.com/shopsys/php-image)
- [shopsys/luigis-box](https://github.com/shopsys/luigis-box)
- [shopsys/administration](https://github.com/shopsys/administration)
- [shopsys/maker](https://github.com/shopsys/maker)

Packages are formatted by release version.
You can see all the changes done to the package that you carry about with this tree.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/) and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html) as explained in the [Backward Compatibility Promise](https://docs.shopsys.com/en/latest/contributing/backward-compatibility-promise/).

<!-- Release notes generated using configuration in .github/release.yml at 17.0 -->

## [v17.0.0](https://github.com/shopsys/shopsys/compare/v16.0.0...v17.0.0) (2025-09-10)

### :construction: Changes that require additional implementation if you are using Frontend API

- stores are ordered by position by @malyMiso in https://github.com/shopsys/shopsys/pull/3934

### :sparkles: Enhancements and features

- Add email property to Complaint by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3716
- Category automated filters by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3672
- Added time-limited price lists with product special prices by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3628
- Price list CSV import/export by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3713
- Search in administration by @RostislavKreisinger in https://github.com/shopsys/shopsys/pull/3679
- New B2B customer user roles for cart manipulation and order creation, and access all company orders by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3756
- New B2B customer user roles for complaints by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3768
- Manual complaint creation (without order) by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3784
- Added complaint resolution to Complaints by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3759
- Added deduplication for product messages in the queue, including scopes by @janjavorek in https://github.com/shopsys/shopsys/pull/3669
- Added smoke tests by @chlebektomas in https://github.com/shopsys/shopsys/pull/3816
- Notification bar validity now includes time for more narrow settings by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3805
- Order statuses are now created with domain defined locales by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3856
- Whole application now correctly reflects input price type settings by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3836
- Mail template prices now reflect input price type setting by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3865
- Optional links to social networks for email templates by @martin-baca-shopsys in https://github.com/shopsys/shopsys/pull/3841
- Filter in the administration for products type upon inquiry by @martin-baca-shopsys in https://github.com/shopsys/shopsys/pull/3843
- Site articles and link articles are now differentiated in the list by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3871
- Remove linked categories on category edit/create from administration by @martin-baca-shopsys in https://github.com/shopsys/shopsys/pull/3718
- Added selling price type by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3883
- Admin locale now can be set to any locale by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3881
- Admin menu: "identification" section is available for multidomain projects only by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3903
- Added product catnum to product list grid by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3902
- Category bestseller items use onmouseup event for gtm by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3915
- Updated list of supported currencies by intl library by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3925
- Added icons for displaying visibility of categories by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3917
- ProductVisibilityFacadeTest is now resistant to added domain without loaded data by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3961
- Gopay improvements by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3918
- Administrator without filtered domains will see new domains automatically when added by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3985
- Re-render and recalculations optimalization by @chlebektomas in https://github.com/shopsys/shopsys/pull/4001
- Navigation item route by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3968
- Updated meaningful parameters to sliders by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4041
- Added ability to enable / disable coupons by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4037
- Updated accessibility follow up by @chlebektomas in https://github.com/shopsys/shopsys/pull/4016
- Added button component, translations, styles by @TomasGottvald in https://github.com/shopsys/shopsys/pull/3908
- Updated gitlab cypress screenshots by @chlebektomas in https://github.com/shopsys/shopsys/pull/4148
- Added license check to Makefiles and CI by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4141
- Order payment method is now changed if user selected different payment method in GoPay payment gate by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4163
- Refactored grapes js by @chlebektomas in https://github.com/shopsys/shopsys/pull/4115
- Refactor RBAC by @henzigo in https://github.com/shopsys/shopsys/pull/4072
- Added Sentry Replays and Feedback with conditional lazy loading by @heca-frantisek in https://github.com/shopsys/shopsys/pull/4038
- Implement Context system by @henzigo in https://github.com/shopsys/shopsys/pull/4064
- robots.txt: tweak the Crawl-delay value for new projects by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4015
- Access control and admin menu tweaks by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3933
- PersonalDataPage API contents are not mandatory by @malyMiso in https://github.com/shopsys/shopsys/pull/3882
- Create crud skeleton by @henzigo in https://github.com/shopsys/shopsys/pull/3629
- Category special prices automated filter by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3754
- Added grapesjs three columns component by @TomasGottvald in https://github.com/shopsys/shopsys/pull/3769

### :bug: Bug Fixes

- Fix localization based on admin language for GrapesJS by @henzigo in https://github.com/shopsys/shopsys/pull/3680
- Fix migrations of dates with wrong timezone by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3729
- Order mail: hide total price for customers that are not allowed to see prices by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3719
- Updated wrapping required checkbox indicator by @chlebektomas in https://github.com/shopsys/shopsys/pull/3728
- Removed double slash in link after failed payment by @chlebektomas in https://github.com/shopsys/shopsys/pull/3685
- Repository-clean.sh pipeline id for deployments by @KennyDaren in https://github.com/shopsys/shopsys/pull/3612
- No log sentry error during maintenance on by @chlebektomas in https://github.com/shopsys/shopsys/pull/3675
- Fix tests by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3737
- Updated complaints search inputs placeholders by @chlebektomas in https://github.com/shopsys/shopsys/pull/3739
- Added stores breadcrumbs skeleton loader by @chlebektomas in https://github.com/shopsys/shopsys/pull/3740
- Remove pagination title from first page by @chlebektomas in https://github.com/shopsys/shopsys/pull/3741
- Fix MissingParamAnnotationsFixer for multiline params by @henzigo in https://github.com/shopsys/shopsys/pull/3736
- Extend copyright year blackout to whole Webline width by @JanMolcik in https://github.com/shopsys/shopsys/pull/3743
- Fixed yaml standards checking by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3781
- Updated email templates for outlook classic by @chlebektomas in https://github.com/shopsys/shopsys/pull/3779
- Administrator now can upload files without ROLE_FILES_FULL that is used for separate administration section by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3777
- Renamed contact page to contact-form page by @chlebektomas in https://github.com/shopsys/shopsys/pull/3776
- Fixed url param value with slash by @chlebektomas in https://github.com/shopsys/shopsys/pull/3786
- Fix price filtering for products with special prices by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3782
- Added missing translations for collapsible component by @chlebektomas in https://github.com/shopsys/shopsys/pull/3787
- Framework: add missing symfony/validator dependency by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3802
- Added overflow scroll to product availability popup by @chlebektomas in https://github.com/shopsys/shopsys/pull/3793
- Fix complaint detail by @chlebektomas in https://github.com/shopsys/shopsys/pull/3799
- Fixed mobile banner slide by @chlebektomas in https://github.com/shopsys/shopsys/pull/3794
- Fixed all tests to work also with CZK currency and Czech language by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3792
- Gitlab security check is now dependent on build of storefront by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3800
- ROLE_API_CUSTOMER_SELF_MANAGE tweaks by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3788
- Fixed editing of unused friendly urls by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3810
- Fix autocomplete results GTM event by @JanMolcik in https://github.com/shopsys/shopsys/pull/3790
- Fixed gitlab check for commited icons by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3817
- Fixed psr-4 compliance by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3819
- Added smoth scroll to error fields in complaint form popup by @chlebektomas in https://github.com/shopsys/shopsys/pull/3795
- Updated responsive filters based on figma design by @chlebektomas in https://github.com/shopsys/shopsys/pull/3821
- Cleared search input after submit by @chlebektomas in https://github.com/shopsys/shopsys/pull/3828
- Refactored product slider for improved responsiveness by @chlebektomas in https://github.com/shopsys/shopsys/pull/3829
- Navigation edit now respects the admin available locales by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3837
- Fixed adding non string constant to category data fixture by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3854
- Invalid locale set to admin now defaults to first allowed locale by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3855
- Fixed assigning parameter position to category by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3862
- Search by catnum is now case insensitive on storefront by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3870
- Fixed product detail skeleton with text selection by @chlebektomas in https://github.com/shopsys/shopsys/pull/3844
- Added user text styles to product detail description by @chlebektomas in https://github.com/shopsys/shopsys/pull/3864
- Add gtm payment event with storing in localStorage by @JanMolcik in https://github.com/shopsys/shopsys/pull/3814
- Fix multiple rerenders of product comparison by @heca-frantisek in https://github.com/shopsys/shopsys/pull/3852
- Fix cart & missing whishlist/comparison products after external login by @JanMolcik in https://github.com/shopsys/shopsys/pull/3822
- Fixed limited user filtering by price by @chlebektomas in https://github.com/shopsys/shopsys/pull/3850
- Improve logging by @henzigo in https://github.com/shopsys/shopsys/pull/3690
- Free transport promo code fix by @JanMolcik in https://github.com/shopsys/shopsys/pull/3878
- Fixed search by catnum exact match by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3900
- Fixed notification bar unlimited validTo by @chlebektomas in https://github.com/shopsys/shopsys/pull/3894
- Fixed user information after change by @chlebektomas in https://github.com/shopsys/shopsys/pull/3892
- Ensure store coordinates are always numeric by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3873
- Fixed attempt to login as admin without password by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3901
- Fix long product name word break by @JanMolcik in https://github.com/shopsys/shopsys/pull/3880
- Changed store detail button to primary and fixed skeletonsg by @chlebektomas in https://github.com/shopsys/shopsys/pull/3904
- Narrow articles layout by @TomasGottvald in https://github.com/shopsys/shopsys/pull/3791
- Fixed product name wrapping by @chlebektomas in https://github.com/shopsys/shopsys/pull/3921
- Fix cypress test & generate snapshots table script & makefile improvements by @JanMolcik in https://github.com/shopsys/shopsys/pull/3920
- Fixed rendering of LuigisBoxCategoryFeed by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3919
- Polished gallery thumbnails sizes by @chlebektomas in https://github.com/shopsys/shopsys/pull/3928
- Fixed GrapesJS Text with Image component by @chlebektomas in https://github.com/shopsys/shopsys/pull/3916
- Fixed limited user promo code and skeletons by @chlebektomas in https://github.com/shopsys/shopsys/pull/3927
- Enable adding related products on product creation by @malyMiso in https://github.com/shopsys/shopsys/pull/3941
- Fix disappearing RGB color of parameter values by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3924
- Fixed luigis box size in cart by @chlebektomas in https://github.com/shopsys/shopsys/pull/3943
- Do not render datagrid without defined or visibled columns by @henzigo in https://github.com/shopsys/shopsys/pull/3950
- Variants can be set as related products by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3946
- Change defer location and z-index by @chlebektomas in https://github.com/shopsys/shopsys/pull/3955
- Transport prices are now copied to newly created domain by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3960
- Updated grapes js template classes by @chlebektomas in https://github.com/shopsys/shopsys/pull/3959
- Fixed long banner button name by @chlebektomas in https://github.com/shopsys/shopsys/pull/3957
- Updated order of created data fixtures of price lists to ensure correct working of testProperSpecialPriceIsReturnedForMultipleLists by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3979
- Sending auth code without requesting email first no longer fails with 500 by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3980
- Fix contact form query by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3974
- Admin enabled domains are now correctly aplied to PriceAndVatTableByDomainsType by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3984
- Fixed searching in unused friendly urls by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3982
- DomainsForDataFixtureProvider loads domains on demand instead of allways by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3989
- Added deferred accessories to main variant detail by @chlebektomas in https://github.com/shopsys/shopsys/pull/3992
- Fixed related products slider arrows by @chlebektomas in https://github.com/shopsys/shopsys/pull/3995
- FlagDataFixture: fill flag translations for all locales by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4005
- Fixed empty page title suffix by @chlebektomas in https://github.com/shopsys/shopsys/pull/3988
- Ensure dev environment for ux:icons:lock on Gitlab by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4004
- Fix setting empty value for temporary file by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4002
- Fixed order confirmation product image size by @chlebektomas in https://github.com/shopsys/shopsys/pull/3994
- Fixed order confirmation skeleton responsive by @chlebektomas in https://github.com/shopsys/shopsys/pull/3993
- Fixed contact information address animation by @chlebektomas in https://github.com/shopsys/shopsys/pull/3996
- Fix load more limit by @JanMolcik in https://github.com/shopsys/shopsys/pull/3977
- Added prevention for duplicate parameter values by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3981
- 2FA email template is now created in DB migration instead of data fixture by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4006
- Ensure order mail can be sent from CLI by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4007
- CK editor grid layout fix by @JanMolcik in https://github.com/shopsys/shopsys/pull/4014
- Added listener for failed ProductRecalculationMessages that also removes scope settings from Redis by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4009
- Units can be deleted when associated with parameter by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4013
- Ensure that GrapesJS content is always editable by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3998
- Fixed standards by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4023
- Fix: Prevent locale flag squashing by @JanMolcik in https://github.com/shopsys/shopsys/pull/4021
- Fix disappearing content after clicking to some links by @JanMolcik in https://github.com/shopsys/shopsys/pull/4024
- Swap cypress visit test for category detail by @JanMolcik in https://github.com/shopsys/shopsys/pull/4028
- Fixed price range slider by @chlebektomas in https://github.com/shopsys/shopsys/pull/4040
- Mailer does not try to send message to non-existing email by @malyMiso in https://github.com/shopsys/shopsys/pull/4030
- Fixed responsive signpost zindex by @chlebektomas in https://github.com/shopsys/shopsys/pull/4045
- MultidomainType: multidomain options are correctly merged with domain options by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4033
- Fixed visual apperance of few email templates by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4042
- Refactored Spinbox component & Spinbox Test cover by @JanMolcik in https://github.com/shopsys/shopsys/pull/4039
- Fix `LoaderWithOverlay` component by @JanMolcik in https://github.com/shopsys/shopsys/pull/4046
- Fixed empty grapesjs template by @chlebektomas in https://github.com/shopsys/shopsys/pull/4050
- Add max height to product images in email templates by @JanMolcik in https://github.com/shopsys/shopsys/pull/4062
- Updated grapes js and list styles by @chlebektomas in https://github.com/shopsys/shopsys/pull/4067
- Updated complaint item image size by @chlebektomas in https://github.com/shopsys/shopsys/pull/4066
- AddFieldsByOrderItemType now correctly sets Product for OrderItem of type product by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4055
- Fix jquery components replacements by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4111
- Added opening hours voice over logic and format time by @chlebektomas in https://github.com/shopsys/shopsys/pull/4080
- OrderItemData::createFromOrder now sets related items correctly by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4110
- Fixed sales representative name without image by @chlebektomas in https://github.com/shopsys/shopsys/pull/4117
- Fixed product availability popup scrollbars by @chlebektomas in https://github.com/shopsys/shopsys/pull/4118
- Fixed customer info in order detail by @chlebektomas in https://github.com/shopsys/shopsys/pull/4116
- Fixed biome-config require by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4125
- OrderItem::hasProduct replaced with OrderItem::isTypeProductAndHasProduct by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4054
- Administration: side menu: Customer user role groups are now marked as superadmin menu item by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4131
- Customer social login registration: normalize empty strings to null by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4127
- Fixed range slider min max values by @chlebektomas in https://github.com/shopsys/shopsys/pull/4121
- AdminContext now includes elfinder routes by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4134
- Fixed sales representative deletion message variable by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4147
- Codeception acceptance tests now use pg_restore to dump DB from sql file by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4150
- Fix product videos association updates by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4129
- Fixed recalculation of deleted regular product by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4151
- Fix zalas/phpunit-injector dropped support for PHPUnit 10 and 11 by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4161
- SF: html lang is now properly set for all domains by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4160
- Fix hreflang links for SEO pages by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4157
- Set homepage SEO page slug to "/" to fix 404 links to non-existent "/homepage" by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4159
- Fixed order of registering extended routes by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4164
- Added utility for hiding scrollbars by @chlebektomas in https://github.com/shopsys/shopsys/pull/4156
- Fixed popup and flash message styles by @chlebektomas in https://github.com/shopsys/shopsys/pull/4168
- Remove references to admin bundle in framework tests by @henzigo in https://github.com/shopsys/shopsys/pull/4178
- Move FrontendApiContext to framework bundle by @henzigo in https://github.com/shopsys/shopsys/pull/4180
- Fixed text selection links by @chlebektomas in https://github.com/shopsys/shopsys/pull/4179
- Cypress tests: add blackout for copyright in footer by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3708
- Yaml standards: ignore kubernetes config files by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3964
- Dev dependencies fixes by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3976
- Truly available payments for a change after the order is made are now returned from the backend by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3922
- Fix cypress screenshots by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3888
- Fill delivery address data from existing entity by @malyMiso in https://github.com/shopsys/shopsys/pull/3798

### :hammer: Developer experience and refactoring

- Improved administration authentication code by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3683
- Simplify cypress snapshot names by @JanMolcik in https://github.com/shopsys/shopsys/pull/3709
- Graphql schema is now validated on generate by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3722
- Admin improvements by @RostislavKreisinger in https://github.com/shopsys/shopsys/pull/3641
- Interfaces cleanup by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3732
- Extensibility enhancements (factories and final classes) by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3724
- Replaced static methods with public ones by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3715
- NoDomainSelectedException is no longer throwed during maintenance page + additional Sentry context by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3687
- Movements of features from project-base to packages by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3735
- Replaced custom admin icons with symfony/ux-icons and tabler icon SVGs by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3765
- Removed no longer necessary command for migrating images to new proxy structure by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3801
- Simplified rendering form with sticky save button by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3789
- Administration bundle now uses new bundle directory structure by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3812
- Shopsys:domains-urls:replace now also marks appropriate entities for export to Elasticsearch by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3811
- Improve npm asset linking by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3815
- Sentry errors by @chlebektomas in https://github.com/shopsys/shopsys/pull/3825
- Added support for entity with single domain friendly URL by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3809
- Controllers are now typed by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3831
- Added condition for skipping expected user errors in development by @chlebektomas in https://github.com/shopsys/shopsys/pull/3834
- Added RequireOverrideAttributeSniff that checks existence of #[Override] attribute for overriden methods by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3858
- Category repository now uses inMemoryCache to store root category during request by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3857
- ChoiceType values are not translated by default by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3859
- Remove generated error pages by @henzigo in https://github.com/shopsys/shopsys/pull/3827
- Migrate eslint from v8.56 to v9.22 by @JanMolcik in https://github.com/shopsys/shopsys/pull/3851
- Doctrine: Migration: added functionality for ignore column from entity by attribute by @sspooky13 in https://github.com/shopsys/shopsys/pull/2368
- Dicsount order items creation tweaks by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3886
- Project-base: merge services_frontend_api.yaml into services.yaml by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3890
- Simplified conditions in mail templates twig by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3885
- Fix Load more and Show more pluralization by @JanMolcik in https://github.com/shopsys/shopsys/pull/3884
- Refactored layout styles by @chlebektomas in https://github.com/shopsys/shopsys/pull/3832
- Added new skeleton component by @chlebektomas in https://github.com/shopsys/shopsys/pull/3935
- Domain icon edit page is now standard separate page by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3942
- Code generation via custom Symfony makers by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3808
- Tweaks of code generator by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3948
- Removed unnecessary order sent page data fixtures by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3944
- Flag demo data are now created in data fixtures by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3965
- Split customer translations to separate domain by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3949
- UX icons are now imported within standards-fix(-diff) phing targets by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3969
- Various improvements by @JanMolcik in https://github.com/shopsys/shopsys/pull/3923
- Little environment cleanup by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3966
- Releaser: PackageProvider now uses version 2 of Packagist.org metadata by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3983
- Removed all not necessary position settings from framework form types by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3978
- Social login tweaks and fixes by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3963
- Access control is no longer cached in dev environment by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3997
- DatabaseSearchingHelper::getFullTextLikeSearchString argument is not nullable anymore by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4008
- Make all form classes final as they are not extended directly but via extension by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4034
- Enable dev-debug mode for storefront by @JanMolcik in https://github.com/shopsys/shopsys/pull/4011
- AddRoundingMiddleware now correctly uses OrderPriceCalculation::calculateOrderRoundingPrice as it was supposed to by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4049
- Replace jquery-ui with modern components by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4047
- Replaced eslint on backend app with biomejs by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4056
- Enable storefront offline mode by @JanMolcik in https://github.com/shopsys/shopsys/pull/4063
- GoPay: improve logging of failed payment creation + add docs by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4053
- Redesigned footer by @chlebektomas in https://github.com/shopsys/shopsys/pull/4061
- Unified biome config by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4120
- Added ability to easily add new item to OrderData that will be persisted after OrderFacade::edit by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4057
- Self documented Makefile by @florianjiri in https://github.com/shopsys/shopsys/pull/4032
- Add test coverage for filter and sort by @JanMolcik in https://github.com/shopsys/shopsys/pull/4069
- Refactor administrator login as user by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4124
- Added support for IDE URLs to the profiler and blue screen error page by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4138
- Fixed makefile in project-base by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4183
- Optimize image resizer with caching by @henzigo in https://github.com/shopsys/shopsys/pull/3907
- Improved InlineEdit and Grid administrator rights by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4022
- Maker bundle: add missing shopsys dependencies by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4027
- Add unit tests for order process util functions by @JanMolcik in https://github.com/shopsys/shopsys/pull/4060
- Enchantments for RequireOverrideAttributeSniff by @henzigo in https://github.com/shopsys/shopsys/pull/4068
- Refactor of Image sizes prop by @JanMolcik in https://github.com/shopsys/shopsys/pull/3938
- Makefile improvements + removed redundant robots.txt data fixtures by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3910
- Determining file upload type: use instanceof because of entity overriding by @thirdknown in https://github.com/shopsys/shopsys/pull/3766
- Make uniqid more unique by @henzigo in https://github.com/shopsys/shopsys/pull/3906
- Db migrations: add empty down() method into AbstractMigration by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3887
- Refactor stores and implement some features by @henzigo in https://github.com/shopsys/shopsys/pull/3413
- Fix storefront standards by @JanMolcik in https://github.com/shopsys/shopsys/pull/3762
- Removed all functionality related to DomainConfig::$stylesDirectory and DomainConfig::$designId by @martin-baca-shopsys in https://github.com/shopsys/shopsys/pull/3720

### :book: Documentation

- Improved documentation visual by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3869
- Added docs for easier switching between the projects on MacOS with Mutagen by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3986
- Update links in docs by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4010

### :art: Design & appearance

- Removed banner text when there is no description by @chlebektomas in https://github.com/shopsys/shopsys/pull/3707
- Added colors to flags demodata by @chlebektomas in https://github.com/shopsys/shopsys/pull/3721
- Add register form to payment confirmation page by @JanMolcik in https://github.com/shopsys/shopsys/pull/3670
- Redesigned product detail tabs by @chlebektomas in https://github.com/shopsys/shopsys/pull/3714
- Updated email templates by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3711
- Set footer to stick to bottom by @JanMolcik in https://github.com/shopsys/shopsys/pull/3744
- Added margin to info message on comparison page by @chlebektomas in https://github.com/shopsys/shopsys/pull/3775
- Redesigned cart navigation stepper with new logic for switching steps by @chlebektomas in https://github.com/shopsys/shopsys/pull/3767
- Redesigned cart by @chlebektomas in https://github.com/shopsys/shopsys/pull/3780
- Redesigned order confirmation by @chlebektomas in https://github.com/shopsys/shopsys/pull/3774
- Tuning the display of a group of parameters when editing a product by @martin-baca-shopsys in https://github.com/shopsys/shopsys/pull/3842
- Admin product detail: display main category full path by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3889
- Redesigned order list and user menu by @chlebektomas in https://github.com/shopsys/shopsys/pull/3897
- Unified user menu drawer for account page and header by @chlebektomas in https://github.com/shopsys/shopsys/pull/3936
- Updated responsive button in product list and spinbox by @chlebektomas in https://github.com/shopsys/shopsys/pull/3937
- Changed pagination scroll location by @chlebektomas in https://github.com/shopsys/shopsys/pull/3954
- Added input type numeric to postcode by @chlebektomas in https://github.com/shopsys/shopsys/pull/3956
- Redesign transport and payment by @chlebektomas in https://github.com/shopsys/shopsys/pull/3958
- Design system by @chlebektomas in https://github.com/shopsys/shopsys/pull/3648
- Updated accessibility by @chlebektomas in https://github.com/shopsys/shopsys/pull/3975
- Improve email allowed recipients information display by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4114
- Fixed back to overview link color by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4126
- Admin: enhance "Email allowed recipients" page by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4128
- Improved link color in mail warning bar by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4133

### :rocket: Performance

- Removed explicitly cleared setting cache by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3730
- Close db and redis connections when worker is in idle state by @henzigo in https://github.com/shopsys/shopsys/pull/3757
- Added index to friendly url to cover most used method by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3860
- Improve CLS by @JanMolcik in https://github.com/shopsys/shopsys/pull/3830
- Improve TBT by @JanMolcik in https://github.com/shopsys/shopsys/pull/4012

### :cloud: Infrastructure

- Elasticsearch on local now has limited resources to prevent excessive memory usage by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3763
- Fix docker build by @JanMolcik in https://github.com/shopsys/shopsys/pull/4000
- Update configuration for Traefik by @henzigo in https://github.com/shopsys/shopsys/pull/3742
- Remove old whitelist configuration for ingress by @henzigo in https://github.com/shopsys/shopsys/pull/3875

### :warning: Security

- Improve middleware security by @JanMolcik in https://github.com/shopsys/shopsys/pull/3971
- Update storefront dependencies (security patches) by @JanMolcik in https://github.com/shopsys/shopsys/pull/4170

### :up: Dependencies

- Bump @testing-library/react from 14.2.1 to 14.3.1 in /project-base/storefront by @dependabot[bot] in https://github.com/shopsys/shopsys/pull/3554
- Upgrade postgreSQL version to 17.4 by @henzigo in https://github.com/shopsys/shopsys/pull/3659
- Bump yup from 1.4.0 to 1.6.1 in /project-base/storefront by @dependabot[bot] in https://github.com/shopsys/shopsys/pull/3704
- Bump @floating-ui/react from 0.26.28 to 0.27.3 in /project-base/storefront by @dependabot[bot] in https://github.com/shopsys/shopsys/pull/3731
- doctrine/doctrine-migrations-bundle v3.4.0 is now marked as conflicting by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3749
- Bump prettier-plugin-tailwindcss from 0.5.14 to 0.6.11 in /project-base/storefront by @dependabot[bot] in https://github.com/shopsys/shopsys/pull/3758
- Bump @cypress/request and cypress in /project-base/storefront/cypress by @dependabot[bot] in https://github.com/shopsys/shopsys/pull/3760
- Minor dependencies fixes by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3764
- Conflicted codeception/codeception 5.2.0 by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3806
- Upgrade Tailwind CSS to v4 by @chlebektomas in https://github.com/shopsys/shopsys/pull/3820
- Updated dependencies by @chlebektomas in https://github.com/shopsys/shopsys/pull/3835
- Upgrade Elasticsearch version to 7.17.2 by @henzigo in https://github.com/shopsys/shopsys/pull/3874
- Upgrade Deployment package by @henzigo in https://github.com/shopsys/shopsys/pull/3891
- Update storefront dependencies by @JanMolcik in https://github.com/shopsys/shopsys/pull/3899
- Upgrade sentry to 9.12 and next to 15.2 by @heca-frantisek in https://github.com/shopsys/shopsys/pull/3914
- Updated tailwind to v4.1 by @chlebektomas in https://github.com/shopsys/shopsys/pull/3926
- Bump codeception versions by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3967
- Bump phpstan by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3990
- Upgrade Cypress & fixes of common errors by @JanMolcik in https://github.com/shopsys/shopsys/pull/4139
- Updated biome to 2.2.0 and fixed issues by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4143
- Upgrade rabbitMQ version by @henzigo in https://github.com/shopsys/shopsys/pull/4167

## New Contributors

- @heca-frantisek made their first contribution in https://github.com/shopsys/shopsys/pull/3852
- @florianjiri made their first contribution in https://github.com/shopsys/shopsys/pull/4032

**Full Changelog**: https://github.com/shopsys/shopsys/compare/v16.0.0...v17.0.0
