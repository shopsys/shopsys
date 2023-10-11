# Changelog for 13.0.x

All notable changes, that change in some way the behavior of any of our packages that are maintained by monorepo repository.

There is a list of all the repositories maintained by monorepo:

* [shopsys/framework](https://github.com/shopsys/framework)
* [shopsys/project-base](https://github.com/shopsys/project-base)
* [shopsys/shopsys](https://github.com/shopsys/shopsys)
* [shopsys/coding-standards](https://github.com/shopsys/coding-standards)
* [shopsys/form-types-bundle](https://github.com/shopsys/form-types-bundle)
* [shopsys/http-smoke-testing](https://github.com/shopsys/http-smoke-testing)
* [shopsys/migrations](https://github.com/shopsys/migrations)
* [shopsys/monorepo-tools](https://github.com/shopsys/monorepo-tools)
* [shopsys/plugin-interface](https://github.com/shopsys/plugin-interface)
* [shopsys/product-feed-google](https://github.com/shopsys/product-feed-google)
* [shopsys/product-feed-heureka](https://github.com/shopsys/product-feed-heureka)
* [shopsys/product-feed-heureka-delivery](https://github.com/shopsys/product-feed-heureka-delivery)
* [shopsys/product-feed-zbozi](https://github.com/shopsys/product-feed-zbozi)
* [shopsys/google-cloud-bundle](https://github.com/shopsys/google-cloud-bundle)
* [shopsys/s3-bridge](https://github.com/shopsys/s3-bridge)
* [shopsys/read-model](https://github.com/shopsys/read-model)
* [shopsys/frontend-api](https://github.com/shopsys/frontend-api)
* [shopsys/php-image](https://github.com/shopsys/php-image)


Packages are formatted by release version.
You can see all the changes done to package that you carry about with this tree.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/) and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html) as explained in the [Backward Compatibility Promise](https://docs.shopsys.com/en/latest/contributing/backward-compatibility-promise/).

<!-- Add generated changelog below this line -->

## [v13.0.0](https://github.com/shopsys/shopsys/compare/v12.0.0...v13.0.0) (2023-10-11)

* [shopsys] new Next.js Storefront and a lot of features in https://github.com/shopsys/shopsys/pull/2622
    * see the [Upgrade file](UPGRADE-13.0.md#you-have-three-options-for-upgrading-to-version-1300) for possible ways of upgrading

### :sparkles: Enhancements and features
* [shopsys] added detailed opening hours to stores by @TomasLudvik in https://github.com/shopsys/shopsys/pull/2660
* [shopsys] wishlist by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2640
* [shopsys] added closing days to be set for stores to inform customers that store is closed by @TomasLudvik in https://github.com/shopsys/shopsys/pull/2685
* [project-base] added iframe and image to grapejs by @sebaholesz in https://github.com/shopsys/shopsys/pull/2727
* [project-base] SEO categories are now returned even when ignored filters are set by @TomasLudvik in https://github.com/shopsys/shopsys/pull/2756
* [framework] removed misleading list of url addresses in administration by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2782
* [framework] added auto rendered uuid for entities in administration by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2781
* [shopsys] added quick search in promo codes by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2786
* [shopsys] added order filter by domain in admin by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2796
* [project-base] personal pickup transport is now a type instead of a separate field by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2852

### :construction: Changes that require additional implementation if you are using Frontend API
* Implemented SEO category functionality on storefront by @sebaholesz in https://github.com/shopsys/shopsys/pull/2654
* improvements to Urql client and query error handling by @sebaholesz in https://github.com/shopsys/shopsys/pull/2659
* [shopsys] graphql query is not in transaction by @stanoMilan in https://github.com/shopsys/shopsys/pull/2516

### :bug: Bug Fixes
* [shopsys] fix running Cypress tests locally + updated Cypress image to latest version by @TomasLudvik in https://github.com/shopsys/shopsys/pull/2647
* [shopsys] fixed split packages builds by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2662
* [project-base] fixed annotation of not extended members by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2683
* [framework] fixed robots.txt migration by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2692
* [framework] fixed bestsellers edit in admin by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2704
* [framework] fixed NotIdenticalToEmailLocalPart validator with null values by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2703
* [project-base] removed product images entity caching by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2708
* [project-base] fixed en url to personal data export in admin by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2712
* [framework] fixed editing country error by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2713
* [project-base] fixed category blog delete confirm by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2715
* [project-base] improved working with date in opening hours by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2724
* [framework] fixed order editation error due to invalid type in vat object by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2723
* [project-base] fixed url for personal detail listing from email by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2725
* [framework] fixed editing order email for newly created order status by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2726
* [framework] fixed seoRobotsTxtContent null value in settings by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2732
* [google-feed] fixed google feed availability constants by @TomasLudvik in https://github.com/shopsys/shopsys/pull/2744
* [project-base] filtering in ready category seo mix is now working with provided filters by API by @TomasLudvik in https://github.com/shopsys/shopsys/pull/2747
* [framework] fixed deleting old uploaded files cron by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2755
* [project-base] fixed NodeJS and PostgreSQL installation by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2758
* [project-base] fixed elasticsearch definition to have correct languages set by default domain languages by @TomasLudvik in https://github.com/shopsys/shopsys/pull/2757
* [project-base] fixed deployment after aws/aws-sdk-php update by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2767
* [project-base] fixed JS translations by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2779
* [framework] fixed variant creation by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2802
* [project-base] fixed GetOrdersAsAuthenticatedCustomerUserTest by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2805
* [shopsys] removed dependency on the graphql from framework by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2809
* [project-base] order is updated in GoPay only if configuration is set by @TomasLudvik in https://github.com/shopsys/shopsys/pull/2806
* [shopsys] prevent FileNotFoundException on multiple flush fileupload by @pk16011990 in https://github.com/shopsys/shopsys/pull/2655
* [project-base] fixed argument passing to productsByCatnum query by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2842
* [project-base] fixed closed day edit by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2841
* [project-base] fixed edit language constant by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2845
* [project-base] added validation to suppliers delivery time by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2843
* [project-base] admin with limited permissions now can use domain filter by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2844
* [project-base] seo category mix slug now includes trailing slash and is consistent with other slugs by @TomasLudvik in https://github.com/shopsys/shopsys/pull/2838
* [shopsys] moved migration from Google feed to framework as it was placed incorrectly by @TomasLudvik in https://github.com/shopsys/shopsys/pull/2870

### :hammer: Developer experience and refactoring
* [shopsys] frontend api tests are now separated from functional tests by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2641
* [shopsys] united language constants to English language by @TomasLudvik in https://github.com/shopsys/shopsys/pull/2672
* [project-base] changed robots.txt datafixture to avoid encouraging inappropriate practices by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2714
* [project-base] cron modules review by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2733
* [framework] tests are now multilingual by @TomasLudvik in https://github.com/shopsys/shopsys/pull/2742
* [shopsys] removed deprecations before release 12.0 by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2768
* [shopsys] introduced php-fpm base image by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2762
* [project-base] reduced image url redis cache size by @TomasLudvik in https://github.com/shopsys/shopsys/pull/2761
* [shopsys] removed unused topMenu article placement by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2776
* [shopsys] upgraded doctrine/orm to latest version by @TomasLudvik in https://github.com/shopsys/shopsys/pull/2775
* [shopsys] updated overblog/graphql-bundle to stable version 1.0.0 with dependencies by @TomasLudvik in https://github.com/shopsys/shopsys/pull/2787
* [framework] added optional option to export data to Elasticsearch only for the specified domain by @TomasLudvik in https://github.com/shopsys/shopsys/pull/2780
* [project-base] removed unused code by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2777
* [shopsys] changed default db server in adminer in local environment by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2803
* [shopsys] graphql-bundle classes are now dumped so they can use composer autoload by @TomasLudvik in https://github.com/shopsys/shopsys/pull/2830
* [framework] added optional manual readable frequency to crons.yaml by @TomasLudvik in https://github.com/shopsys/shopsys/pull/2854
* [project-base] unified constructor property modifiers order by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2866

### :book: Documentation
* [shopsys] replaced Shopsys Framework with Shopsys Platform by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2682
* [shopsys] updated open source license acknowledgements by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2709
* [project-base] minor fix in Readme file by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2737
* [docs] updated infrastructure schema by @TomasLudvik in https://github.com/shopsys/shopsys/pull/2743
* [docs] updated PHPStorm settings to use absolute paths for TypeScript by @TomasLudvik in https://github.com/shopsys/shopsys/pull/2760
* [shopsys] added general upgrade notes to upgrade to Shopsys Platform by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2817
* [shopsys] updated our LICENSE by new one for version 13.0 by @TomasLudvik in https://github.com/shopsys/shopsys/pull/2849

### :art: Design & appearance
* [project-base] product videos UX fixes by @TomasLudvik in https://github.com/shopsys/shopsys/pull/2746

### :cloud: Infrastructure
* [php-image] added cron package to base image by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2790
* [infrastructure] upgrade Kubernetes Buildpack version by @henzigo in https://github.com/shopsys/shopsys/pull/2822
* [shopsys] elasticsearch now can have a different index setting per environment by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2823
* [monorepo] split monorepo is now done by Github Actions by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2826
* [monorepo-tools] let the user decide if he wants to proceed with one repo by @tolik518 in https://github.com/shopsys/shopsys/pull/2748

### :warning: Security
* [shopsys] improved permissions handling in admin by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2847

### :convenience_store: Storefront
* refactor and fix pagination by @tvikito in https://github.com/shopsys/shopsys/pull/2663
* fix Stores map by @tvikito in https://github.com/shopsys/shopsys/pull/2694
* images are not downloadable from the product gallery by @sebaholesz in https://github.com/shopsys/shopsys/pull/2690
* improve Web Vitals metrics by @tvikito in https://github.com/shopsys/shopsys/pull/2675
* fix cart contact information by @tvikito in https://github.com/shopsys/shopsys/pull/2699
* improve Cypress setup by @tvikito in https://github.com/shopsys/shopsys/pull/2664
* refactor Sliders by @tvikito in https://github.com/shopsys/shopsys/pull/2710
* refactor product detail layout by @tvikito in https://github.com/shopsys/shopsys/pull/2711
* refactor IconSvg component by @tvikito in https://github.com/shopsys/shopsys/pull/2722
* improve UI of Brands Overview by @tvikito in https://github.com/shopsys/shopsys/pull/2721
* remove closed filter groups from DOM by @tvikito in https://github.com/shopsys/shopsys/pull/2741
* migrate SVG icons to standalone components by @tvikito in https://github.com/shopsys/shopsys/pull/2745
* migrate scripts to Next Script component by @tvikito in https://github.com/shopsys/shopsys/pull/2740
* add SubmitButton component by @tvikito in https://github.com/shopsys/shopsys/pull/2739
* refactor LabelWrapper to use CSS for checkbox and radiobox by @tvikito in https://github.com/shopsys/shopsys/pull/2752
* improve web vitals part 2 by @tvikito in https://github.com/shopsys/shopsys/pull/2750
* reduce DOM size by @tvikito in https://github.com/shopsys/shopsys/pull/2749
* fix LabelWrapper by @tvikito in https://github.com/shopsys/shopsys/pull/2766
* fix SimpleNavigation slider by @tvikito in https://github.com/shopsys/shopsys/pull/2765
* fix email field validation in Contact Information by @tvikito in https://github.com/shopsys/shopsys/pull/2764
* close menu popup after click on link inside by @tvikito in https://github.com/shopsys/shopsys/pull/2785
* move mediaQueries from components to helpers folder by @tvikito in https://github.com/shopsys/shopsys/pull/2773
* add redirect on variant to main variant by @tvikito in https://github.com/shopsys/shopsys/pull/2778
* fix Filter parameter slider selected value by @tvikito in https://github.com/shopsys/shopsys/pull/2784
* fix delivery address on Edit Profile page by @tvikito in https://github.com/shopsys/shopsys/pull/2789
* add Storefront documentation by @tvikito in https://github.com/shopsys/shopsys/pull/2771
* fix Empty cart icon height by @tvikito in https://github.com/shopsys/shopsys/pull/2814
* fix SelectBox dropdown overlap by @tvikito in https://github.com/shopsys/shopsys/pull/2815
* fix duplicated first name in order detail by @tvikito in https://github.com/shopsys/shopsys/pull/2816
* Remove useResizeWidthEffect by @tvikito in https://github.com/shopsys/shopsys/pull/2808
* fix product variant redirect by @tvikito in https://github.com/shopsys/shopsys/pull/2827
* fix Variant image display by @tvikito in https://github.com/shopsys/shopsys/pull/2832
* add rich text to Store description by @tvikito in https://github.com/shopsys/shopsys/pull/2831
* add new gallery layout by @tvikito in https://github.com/shopsys/shopsys/pull/2824
* fix console log errors by @tvikito in https://github.com/shopsys/shopsys/pull/2818
* fix wishlist functionality for not logged user by @tvikito in https://github.com/shopsys/shopsys/pull/2836
* refactor Canonical URL generator by @tvikito in https://github.com/shopsys/shopsys/pull/2813
* fix GrapesJS render with no products and add skeleton loader by @tvikito in https://github.com/shopsys/shopsys/pull/2848
* fix Note label overlap by @tvikito in https://github.com/shopsys/shopsys/pull/2856
* fix MenuIconic alignment by @tvikito in https://github.com/shopsys/shopsys/pull/2853
* fix category Adverts position by @tvikito in https://github.com/shopsys/shopsys/pull/2857
* fix switch custom delivery address validation by @tvikito in https://github.com/shopsys/shopsys/pull/2858
* fix Zásilkovna order delivery by @tvikito in https://github.com/shopsys/shopsys/pull/2851
* reimplemented friendly url component resolving with skeletons by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2650
* added lightgallery license key by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2670
* errors on SF can now have different verbosity by @sebaholesz in https://github.com/shopsys/shopsys/pull/2673
* wishlist and comparison for product variant cards by @sebaholesz in https://github.com/shopsys/shopsys/pull/2697
* load more feature by @sebaholesz in https://github.com/shopsys/shopsys/pull/2695
* fixed dynamic robots.txt on storefront by @sebaholesz in https://github.com/shopsys/shopsys/pull/2720
* cart can now be prefilled based on a previous order by @sebaholesz in https://github.com/shopsys/shopsys/pull/2707
* added block with bestsellers to category detail by @sebaholesz in https://github.com/shopsys/shopsys/pull/2730
* brands SEO sensitive config by @sebaholesz in https://github.com/shopsys/shopsys/pull/2794
* blog article detail page now uses SEO H1 as heading when available by @sebaholesz in https://github.com/shopsys/shopsys/pull/2661
* fix storefront file permissions for user with uid different from 1000 by @grossmannmartin in https://github.com/shopsys/shopsys/pull/2669
* fixed incorrect button margin in newsletter form by @sebaholesz in https://github.com/shopsys/shopsys/pull/2688
* router.push and router.replace now navigate correctly between dynamic… by @sebaholesz in https://github.com/shopsys/shopsys/pull/2689
* error popup is now shown after the first invalid form submission by @sebaholesz in https://github.com/shopsys/shopsys/pull/2700
* refactored menu iconic for a better DX by @sebaholesz in https://github.com/shopsys/shopsys/pull/2687
* logged-in user wishlist and comparison fix by @sebaholesz in https://github.com/shopsys/shopsys/pull/2696
* forms are now validated after manual value setting by @sebaholesz in https://github.com/shopsys/shopsys/pull/2728
* registration after order now uses data from URL by @sebaholesz in https://github.com/shopsys/shopsys/pull/2738
* added more info about error types to SF middleware by @sebaholesz in https://github.com/shopsys/shopsys/pull/2753
* comparison popup is now visible after adding a product from product detail by @sebaholesz in https://github.com/shopsys/shopsys/pull/2828
* Fixes regarding default delivery address by @sebaholesz in https://github.com/shopsys/shopsys/pull/2829
* moved login popup outside of the contact information form by @sebaholesz in https://github.com/shopsys/shopsys/pull/2837
* fixed redis cache on storefront by @sebaholesz in https://github.com/shopsys/shopsys/pull/2840
* fix console warnings by @tvikito in https://github.com/shopsys/shopsys/pull/2652
* update of core storefront packages by @sebaholesz in https://github.com/shopsys/shopsys/pull/2665
* storefront unit tests config and initial examples by @sebaholesz in https://github.com/shopsys/shopsys/pull/2686
* storefront restructuring by @sebaholesz in https://github.com/shopsys/shopsys/pull/2717
* removed useTypedTranslationFunction from storefront by @sebaholesz in https://github.com/shopsys/shopsys/pull/2736
* changed images and icons to free variants by @sebaholesz in https://github.com/shopsys/shopsys/pull/2698
* added total price to order detail by @sebaholesz in https://github.com/shopsys/shopsys/pull/2702
* multiple tabs logged out user behavior by @tvikito in https://github.com/shopsys/shopsys/pull/2855
* transport and payment page fetching state refactoring by @sebaholesz in https://github.com/shopsys/shopsys/pull/2807

## New Contributors
* @tolik518 made their first contribution in https://github.com/shopsys/shopsys/pull/2748
