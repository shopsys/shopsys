# Changelog for 16.0.x

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
- [shopsys/google-cloud-bundle](https://github.com/shopsys/google-cloud-bundle)
- [shopsys/s3-bridge](https://github.com/shopsys/s3-bridge)
- [shopsys/frontend-api](https://github.com/shopsys/frontend-api)
- [shopsys/php-image](https://github.com/shopsys/php-image)
- [shopsys/luigis-box](https://github.com/shopsys/luigis-box)
- [shopsys/administration](https://github.com/shopsys/administration)
- [shopsys/convertim](https://github.com/shopsys/convertim)

Packages are formatted by release version.
You can see all the changes done to the package that you carry about with this tree.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/) and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html) as explained in the [Backward Compatibility Promise](https://docs.shopsys.com/en/latest/contributing/backward-compatibility-promise/).

<!-- Add generated changelog below this line -->
<!-- Release notes generated using configuration in .github/release.yml at 16.0 -->

### :sparkles: Enhancements and features

- [framework] user is logged out after role change by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3458
- [shopsys] transport restrictions by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3397
- [shopsys] changes of banner sliders are propagated immediately by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3451
- [shopsys] added convertim package by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3439
- [project-base] demo images for complaints by @sspooky13 in https://github.com/shopsys/shopsys/pull/3434
- [shopsys] FE API: blog category now can return main image by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3475
- [framework] Remove deprecated title, caption, geo_location, license + lastmod from image sitemap by @martin-baca-shopsys in https://github.com/shopsys/shopsys/pull/3400
- [shopsys] logged user is not allowed to change their email in cart by @chlebektomas in https://github.com/shopsys/shopsys/pull/3468
- [framework] Set default ordering by last/first name in getCustomerUsersQueryBuilder by @AmpMVn in https://github.com/shopsys/shopsys/pull/3501
- [frontend-api] add variantsCount to MainVariant type by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3490
- [project-base] disabled fields in cart for B2B customers by @chlebektomas in https://github.com/shopsys/shopsys/pull/3527
- [project-base] added form validation delay by @chlebektomas in https://github.com/shopsys/shopsys/pull/3530
- [shopsys] Parameter groups refactor + editable in Admin by @AmpMVn in https://github.com/shopsys/shopsys/pull/3484
- [shopsys] Do not log out the user after changing the password by @AmpMVn in https://github.com/shopsys/shopsys/pull/3532
- [shopsys] Role ROLE_ALL_API sees all company complaints by @AmpMVn in https://github.com/shopsys/shopsys/pull/3534
- [project-base] added new email validation regex by @chlebektomas in https://github.com/shopsys/shopsys/pull/3543
- [project-base] added page change password by @chlebektomas in https://github.com/shopsys/shopsys/pull/3545
- [shopsys] product inquiries by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3465
- [shopsys] register company after order is more user friendly on b2b domain by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3546
- [shopsys] admin can limit managed domains by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3289
- [framework] admin now can select the administration locale by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3577
- [shopsys] list of complaints in personal data by @sspooky13 in https://github.com/shopsys/shopsys/pull/3433
- [project-base] GrapesJS new plugins by @TomasGottvald in https://github.com/shopsys/shopsys/pull/3464
- [framework] added missing weight attribute to Packeta by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3604
- [shopsys] Slug of Seo page is now saved as text instead of FriendlyUrl by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3608
- [shopsys] out of stock products behavior by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3587
- [shopsys] Removing administrator password settings from administration by @AmpMVn in https://github.com/shopsys/shopsys/pull/3606

- [framework] admin: sending test mail templates on click by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3639
- [shopsys] promo code for free transport and payment by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3625
- [framework] improved rendering information about eshop required settings by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3658
- [shopsys] watchdog by @janjavorek in https://github.com/shopsys/shopsys/pull/3640

### :bug: Bug Fixes

- [project-base] Fixing white screen after 5 mins for GoPay payment method by @JanMolcik in https://github.com/shopsys/shopsys/pull/3456
- [project-base] added new icons and tests by @chlebektomas in https://github.com/shopsys/shopsys/pull/3457
- [project-base] prevent white page after deploy by @chlebektomas in https://github.com/shopsys/shopsys/pull/3467
- [project-base] fixed complaints search by @chlebektomas in https://github.com/shopsys/shopsys/pull/3472
- [project-base] unified date format based on localization by @chlebektomas in https://github.com/shopsys/shopsys/pull/3471
- [framework] fixed owner of complaint sequences by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3477
- [project-base] removed link of hidden product in order detail by @chlebektomas in https://github.com/shopsys/shopsys/pull/3476
- [project-base] added cart skeleton loader by @chlebektomas in https://github.com/shopsys/shopsys/pull/3470
- [project-base] fixed empty card in header by @chlebektomas in https://github.com/shopsys/shopsys/pull/3493
- [frontend-api] registration after order refactoring by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3462
- [shopsys] Fix ordering in FE internal search by @AmpMVn in https://github.com/shopsys/shopsys/pull/3488
- [project-base] added browser sync for product lists by @chlebektomas in https://github.com/shopsys/shopsys/pull/3463
- [project-base] fixed inverted button responsive by @chlebektomas in https://github.com/shopsys/shopsys/pull/3506
- [framework] fix blog category visibility calculation by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3503
- [project-base] hidden user section for non company user by @chlebektomas in https://github.com/shopsys/shopsys/pull/3502
- [project-base] fixed console warnings and errors by @chlebektomas in https://github.com/shopsys/shopsys/pull/3479
- [project-base] Fix blurry text in popup (4k monitor) by @JanMolcik in https://github.com/shopsys/shopsys/pull/3521
- [project-base] edited required logic for form inputs: deliveryStreet, deliveryCity, deliveryPostcode by @Srnka392 in https://github.com/shopsys/shopsys/pull/3474
- [project-base] fixed gtm order create status sent twice by @chlebektomas in https://github.com/shopsys/shopsys/pull/3495
- [project-base] fixed select box responsive by @chlebektomas in https://github.com/shopsys/shopsys/pull/3504
- [framework] UploadedFileConfig get by class comparison fix by @KennyDaren in https://github.com/shopsys/shopsys/pull/3533
- [framework] extend CustomerFileNotFoundException for frontend 404 page by @AmpMVn in https://github.com/shopsys/shopsys/pull/3497
- [project-base] showed category image just on first page by @chlebektomas in https://github.com/shopsys/shopsys/pull/3522
- [framework] Activation of CSRF protection for the deleteConfirm actions in admin by @AmpMVn in https://github.com/shopsys/shopsys/pull/3513
- [project-base] removed filter panel rerender on search page by @chlebektomas in https://github.com/shopsys/shopsys/pull/3531
- [project-base] Add Luigi's box recommender identifier by @JanMolcik in https://github.com/shopsys/shopsys/pull/3520
- [project-base] fixed asset loading in helios-ag/fm-elfinder-bundle 12.6 by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3540
- [project-base] Fix unknown errors messages by @JanMolcik in https://github.com/shopsys/shopsys/pull/3517
- [project-base] fixed order detail skeleton by @chlebektomas in https://github.com/shopsys/shopsys/pull/3529
- [project-base] Fix wrong redirect after refresh by @JanMolcik in https://github.com/shopsys/shopsys/pull/3537
- [Framework] Fix parameters groups migrations by @AmpMVn in https://github.com/shopsys/shopsys/pull/3557
- [project-base] fixed product edit in development environment by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3544
- [project-base] Refactored animations for order transport and payment by @JanMolcik in https://github.com/shopsys/shopsys/pull/3538
- [project-base] reset form state after logout by @chlebektomas in https://github.com/shopsys/shopsys/pull/3523
- [project-base] fixed category image responsive by @chlebektomas in https://github.com/shopsys/shopsys/pull/3565
- [project-base] fixed change password page for users without password by @chlebektomas in https://github.com/shopsys/shopsys/pull/3576
- [project-base] removed initial tab animation on product detail page by @chlebektomas in https://github.com/shopsys/shopsys/pull/3579
- [project-base] fixed cart delivery address validation by @chlebektomas in https://github.com/shopsys/shopsys/pull/3541
- [framework] Preselected country in the delivery address creating by billing address by @AmpMVn in https://github.com/shopsys/shopsys/pull/3581
- [project-base] polished menu animations by @chlebektomas in https://github.com/shopsys/shopsys/pull/3580
- [framework] fix FormDetailExtension by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3561
- [framework] CustomerFileNotFoundException now checks existence of App\Environment so it is working correctly on splited packages by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3599
- [project-base] category without image is now rendered correctly by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3600
- [project-base] fixed product detail code and brand by @chlebektomas in https://github.com/shopsys/shopsys/pull/3596
- [project-base] Remove active filters when switching pages by @JanMolcik in https://github.com/shopsys/shopsys/pull/3592
- [shopsys] separated edit personal data mutation to allow change data for self-managed user on b2b domain by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3601
- [project-base] Fix Gitlab tests by @henzigo in https://github.com/shopsys/shopsys/pull/3618
- [project-base] fixed running jest on alpine on gitlab by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3619
- [project-base] Remove duplicate Webline for RecommendedProducts by @JanMolcik in https://github.com/shopsys/shopsys/pull/3613
- [framework] Fix github dockerbuild pipeline on 16 by @AmpMVn in https://github.com/shopsys/shopsys/pull/3615
- [project-base] updated fetcher condition by @chlebektomas in https://github.com/shopsys/shopsys/pull/3621
- [project-base] updated user navigation alignments by @chlebektomas in https://github.com/shopsys/shopsys/pull/3610
- [project-base] fixed tablet account popups by @chlebektomas in https://github.com/shopsys/shopsys/pull/3568
- [project-base] added cookies store to error page to avoid undefined userIdentifier by @chlebektomas in https://github.com/shopsys/shopsys/pull/3631
- [shopsys] Hotfix - add regenerate stage to gitlab ci by @AmpMVn in https://github.com/shopsys/shopsys/pull/3647
- [framework] fixed not nullable return value of \Shopsys\FrameworkBundle\Form\Admin\Category\CategoryFormType::getCategoryNameForPlaceholder by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3645
- [php-image] pin postgresql-client version for compatibility with the v12.1 server by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3655
- [project-base] added dynamic tab index to product detail page by @chlebektomas in https://github.com/shopsys/shopsys/pull/3644
- [Luigi's Box] fix filtering products by stock by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3652
- [frontend-api] fixed wrong type for logger in SocialNetworkFacade by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3662
- [shopsys] classes from wrong namespace are no longer used by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3651
- [project-base] fixed single banner overflowing by @chlebektomas in https://github.com/shopsys/shopsys/pull/3660
- [shopsys] fixed not working ChangePaymentMethodMutation by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3650
- [project-base] removed neccesary space in product box by @chlebektomas in https://github.com/shopsys/shopsys/pull/3632
- [framework] fixed slider on banner edit in czech language by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3656
- [project-base] removed repeat order button when product is type inquiry by @chlebektomas in https://github.com/shopsys/shopsys/pull/3668
- [framework] added missing migration to create admin reset password mail template by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3654
- [framework] ensure manually sent mail templates are wrapped with the GrapesJS body by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3664
- [project-base] fixed product slider on article page by @chlebektomas in https://github.com/shopsys/shopsys/pull/3674
- [projcet-base] Fix preselecting packetery with empty pickup point by @JanMolcik in https://github.com/shopsys/shopsys/pull/3663
- [project-base] Fix adding product to cart from search page. by @JanMolcik in https://github.com/shopsys/shopsys/pull/3671
- [framework] fixed rendering main blog category in blog article assign to category tree by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3684
- [project-base] do not restart consumer container to prevent network issues by @henzigo in https://github.com/shopsys/shopsys/pull/3681
- [coding-standards] fix composer version for coding-standards by @henzigo in https://github.com/shopsys/shopsys/pull/3697
- [framework] fixed showing error when admin doesn't fill opening hour for specific day by @sspooky13 in https://github.com/shopsys/shopsys/pull/3426
- [project-base] fix company complaints if you change domains setting by @henzigo in https://github.com/shopsys/shopsys/pull/3563
- [storefront] disable link click on product list item text select text by @TomasGottvald in https://github.com/shopsys/shopsys/pull/3593
- [storefront] product list item variant gap, regenerate screenshots by @TomasGottvald in https://github.com/shopsys/shopsys/pull/3676
- [shopsys] gopay double spend issue by @RostislavKreisinger in https://github.com/shopsys/shopsys/pull/3635
- [storefront] product select text - slider issues by @TomasGottvald in https://github.com/shopsys/shopsys/pull/3692

### :hammer: Developer experience and refactoring

- [Shopsys] Add parallel and regenerate to Gitlab pipelines by @AmpMVn in https://github.com/shopsys/shopsys/pull/3630
- [project-base] Improve DX of cypress tests by @JanMolcik in https://github.com/shopsys/shopsys/pull/3411
- [project-base] stable feed hash for alpha branch by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3509
- [project-base] unified form messages by @chlebektomas in https://github.com/shopsys/shopsys/pull/3524
- [shopsys] dependabot: ignore major version changes by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3553
- [project-base] Stabilise cypress tests locally by @JanMolcik in https://github.com/shopsys/shopsys/pull/3562
- [shopsys] move ready category seo mix from project-base to packages by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3494
- [shopsys] frontend API testing on b2b domain by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3508
- [Framework] Rename tv products for main and variants by @AmpMVn in https://github.com/shopsys/shopsys/pull/3578
- [project-base] Simplify Cypress snapshots by @JanMolcik in https://github.com/shopsys/shopsys/pull/3585
- [framework] getting position for new image is now more robust by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3421
- [project-base] fixed failing MainBlogCategoryDataSettingsTest because of incorrect domain url by @malyMiso in https://github.com/shopsys/shopsys/pull/3598
- [shopsys] upgrade to Symfony 6.4 by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3584
- [project-base] Revision of .env files by @JanMolcik in https://github.com/shopsys/shopsys/pull/3603
- [frontend-api] JwtConfiguration is now created on demand by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3616
- [framework] functional tests are now run per folder to prevent memory limits by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3642
- [shopsys] ensure graphql schema is always rendered same by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3678
- [framework] trans function now support named arguments by @henzigo in https://github.com/shopsys/shopsys/pull/3682
- [shopsys] upgraded two-factor packages to be compatible with Symfony 6 by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3478
- [framework] replace shopsys/ordered-form package with becklyn/ordered-form-bundle by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3496
- [shopsys] upgrade doctrine/persistence to ^3.3.3 by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3498
- [project-base] extended datafixtures by subcategories in the menu by @AmpMVn in https://github.com/shopsys/shopsys/pull/3489
- [storefront] Bump @urql/introspection from 1.0.3 to 1.1.0 in /project-base/storefront by @dependabot in https://github.com/shopsys/shopsys/pull/3550
- [storefront] Bump react-toastify from 10.0.4 to 10.0.6 in /project-base/storefront by @dependabot in https://github.com/shopsys/shopsys/pull/3551
- [shopsys] Upgrade Sentry package by @henzigo in https://github.com/shopsys/shopsys/pull/3539
- [shopsys] Change blog categories for real root category by @AmpMVn in https://github.com/shopsys/shopsys/pull/3595
- [project-base] updated dependencies by @chlebektomas in https://github.com/shopsys/shopsys/pull/3646
- [shopsys] Upgrade some composer dependencies by @henzigo in https://github.com/shopsys/shopsys/pull/3688
- [shopsys] add missing symfony/doctrine-messenger dependency by @henzigo in https://github.com/shopsys/shopsys/pull/3694
- [shopsys] fix build of project-base after updating overblog/dataloader-bundle by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3698

### :book: Documentation

- [docs] lost documentation from Commerce Cloud has returned by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3487
- [shopsys] docs: add info about necessary Git long paths settings for Windows installation by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3492
- [shopsys] update directories yaml file by @emmanuel-ferdman in https://github.com/shopsys/shopsys/pull/3526
- [shopsys] tweak docs about social login settings by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3515
- [shopsys] markdown unordered lists are now formatted logically by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3622
- [docs] added documentation about domain limiting by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3653
- [shopsys] docs: fix tag name for feeds by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3661

### :art: Design & appearance

- [project-base] updated typography and buttons by @chlebektomas in https://github.com/shopsys/shopsys/pull/3454
- [project-base] redesigned category page by @chlebektomas in https://github.com/shopsys/shopsys/pull/3435
- [project-base] redesigned homepage by @chlebektomas in https://github.com/shopsys/shopsys/pull/3446
- [project-base] Add and unify storefront animations by @JanMolcik in https://github.com/shopsys/shopsys/pull/3469
- [project-base] updated design of sales representative by @chlebektomas in https://github.com/shopsys/shopsys/pull/3500
- [project-base] redesigned login popup by @chlebektomas in https://github.com/shopsys/shopsys/pull/3466
- [project-base] hidden repeat order button by @chlebektomas in https://github.com/shopsys/shopsys/pull/3535
- [project-base] added brand and flag page filtration by @chlebektomas in https://github.com/shopsys/shopsys/pull/3507
- [project-base] Add animations for mobile navigation menu by @JanMolcik in https://github.com/shopsys/shopsys/pull/3569
- [Shopsys] Sliders extension by @AmpMVn in https://github.com/shopsys/shopsys/pull/3574
- [framework] improve appearence of datagrid by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3609
- [project-base] custom select by @chlebektomas in https://github.com/shopsys/shopsys/pull/3588
- [project-base] Enable deleting Spinbox's value by @JanMolcik in https://github.com/shopsys/shopsys/pull/3607
- [project-base] added users table role column by @chlebektomas in https://github.com/shopsys/shopsys/pull/3617
- [framework] Admin dropdown UI & UX improvement by @JanMolcik in https://github.com/shopsys/shopsys/pull/3636
- [project-base] removed stock availability info for products excluded from sale by @chlebektomas in https://github.com/shopsys/shopsys/pull/3667
- [administration] Hide warning about not secured CKEditor by @henzigo in https://github.com/shopsys/shopsys/pull/3677
- [storefront] Update UX for user menu by @henzigo in https://github.com/shopsys/shopsys/pull/3583
- [storefront] blog category redesign by @Srnka392 in https://github.com/shopsys/shopsys/pull/3482

### :cloud: Infrastructure

- [shopsys] upgrade shopsys/deployment package by @henzigo in https://github.com/shopsys/shopsys/pull/3525
- [shopsys] fix builds on version branches by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3516
- [shopsys] CI is now built as production by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3483
- [shopsys] Fix zero downtime deployment by @henzigo in https://github.com/shopsys/shopsys/pull/3689
- [shopsys] An attempt to speed up the build of the application, including tests by @AmpMVn in https://github.com/shopsys/shopsys/pull/3514
- [shopsys] refactor Dockerfiles for PHP-FPM by @henzigo in https://github.com/shopsys/shopsys/pull/3518
- [project-base] enable sentry performance monitoring by @henzigo in https://github.com/shopsys/shopsys/pull/3626
- [shopsys] Upgrade Redis to the newest version by @henzigo in https://github.com/shopsys/shopsys/pull/3673[project-base]
- [shopsys] updated sentry to version 8.x.x by @chlebektomas in https://github.com/shopsys/shopsys/pull/3691

### :warning: Security

- [framework] administrator email now must be unique by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3686

### :placard: Other Changes

- [monorepo] removed dependency on symplify/monorepo-builder by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3481
- [monorepo] Optimize builds for backend tests by @henzigo in https://github.com/shopsys/shopsys/pull/3519
- [shopsys] releaser: replace BitWarden mentions with 1Password by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3558
- [monorepo] improved PULL_REQUEST_TEMPLATE.md so PR will have always all steps completed by @TomasLudvik in https://github.com/shopsys/shopsys/pull/3634

## New Contributors

- @AmpMVn made their first contribution in https://github.com/shopsys/shopsys/pull/3488
- @emmanuel-ferdman made their first contribution in https://github.com/shopsys/shopsys/pull/3526
- @dependabot made their first contribution in https://github.com/shopsys/shopsys/pull/3550
- @janjavorek made their first contribution in https://github.com/shopsys/shopsys/pull/3640

**Full Changelog**: https://github.com/shopsys/shopsys/compare/v15.0.0...v16.0.0
