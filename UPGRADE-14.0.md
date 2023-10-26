# UPGRADING FROM 13.x to 14.0

The releases of Shopsys Platform adhere to the [Backward Compatibility Promise](https://docs.shopsys.com/en/latest/contributing/backward-compatibility-promise/) to make the upgrades to new versions easier and help long-term maintainability.

## Recommended way of upgrading

Since there are 3 possible scenarios for using Shopsys Platform, instructions are divided into these scenarios.

### You use our packages only

Follow the instructions in relevant sections, e.g. `shopsys/coding-standards` or `shopsys/http-smoke-testing`.

### You are developing a project based on the project-base repository

-   upgrade only your composer dependencies and follow the instructions in the guide below
-   upgrade locally first - after you fix all issues caused by the upgrade, commit your changes, test your application, and then continue with a deployment onto your server
-   upgrade one version at a time:
    -   start with a working application
    -   upgrade to the next version
    -   fix all the issues you encounter
    -   repeat
-   check the instructions in all sections; any of them could be relevant to you
-   the typical upgrade sequence should be:
    -   run `docker-compose down --volumes` to turn off your containers
    -   _(macOS only)_ run `mutagen-compose down --volumes` instead
    -   follow upgrade notes in the _Infrastructure_ section (related to `docker-compose.yml`, `Dockerfile`, docker containers, `nginx.conf`, `php.ini`, etc.)
    -   _(MacOS, Windows only)_ run `docker-sync start` to create volumes
    -   run `docker-compose build --no-cache --pull` to build your images without cache and with the latest version
    -   run `docker-compose up -d --force-recreate --remove-orphans` to start the application again
    -   update the `shopsys/*` dependencies in `composer.json` to the version you are upgrading to
        -   e.g., `"shopsys/framework": "v7.0.0"`
    -   follow upgrade notes in the _Composer dependencies_ section (related with `composer.json`)
    -   run `composer update shopsys/* --with-dependencies`
    -   update the `@shopsys/framework` package in your `package.json` (in "dependencies" section) to the version you are upgrading to
        -   eg. `"@shopsys/framework": "9.0.4",`
    -   run `npm install` to update the NPM dependencies
    -   follow all upgrade notes you have not done yet
    -   run `php phing clean`
    -   run `php phing db-migrations` to run the database migrations
    -   test your app locally
    -   commit your changes
    -   run `composer update` to update the rest of your dependencies, test the app again, and commit `composer.lock`
-   if any of the database migrations do not suit you, there is an option to skip it; see [our Database Migrations docs](https://docs.shopsys.com/en/latest/introduction/database-migrations/#reordering-and-skipping-migrations)
-   we may miss something even if we care a lot about these instructions. In case something doesn't work after the upgrade, you'll find more information in the [CHANGELOG](CHANGELOG.md)

<!-- Insert upgrade instructions in the following format:
- general instruction ([#<PR number>](https://github.com/shopsys/shopsys/pull/<PR number>))
    - additional instructions
    - see #project-base-diff to update your project
-->

## [Upgrade from v13.0.0 to v14.0.0-dev](https://github.com/shopsys/shopsys/compare/v13.0.0...14.0)

-   add rounded price value to order process ([#2835](https://github.com/shopsys/shopsys/pull/2835))
    -   see #project-base-diff to update your project
-   remove link from administrator and customers breadcrumb ([#2881](https://github.com/shopsys/shopsys/pull/2881))
    -   see #project-base-diff to update your project
-   add test to keep Elasticsearch converter and mapping in sync ([#2880](https://github.com/shopsys/shopsys/pull/2880))
    -   see #project-base-diff to update your project
-   set products for export to elastic after changing quantity after completing, editing, or deleting order ([#2587](https://github.com/shopsys/shopsys/pull/2587))
    -   method `Shopsys\FrameworkBundle\Model\Order\Item\OrderProductFacade::__construct()` changed its interface:
        ```diff
            public function __construct(
                protected readonly EntityManagerInterface $em,
                protected readonly ProductHiddenRecalculator $productHiddenRecalculator,
                protected readonly ProductSellingDeniedRecalculator $productSellingDeniedRecalculator,
                protected readonly ProductAvailabilityRecalculationScheduler $productAvailabilityRecalculationScheduler,
                protected readonly ProductVisibilityFacade $productVisibilityFacade,
                protected readonly ModuleFacade $moduleFacade,
        +       protected readonly ProductRepository $productRepository,
            )
        ```
-   update your project to fix problems with single domain ([#2875](https://github.com/shopsys/shopsys/pull/2875))
    -   see #project-base-diff to update your project
-   improve product lists in GrapesJS ([#2879](https://github.com/shopsys/shopsys/pull/2879))
    -   see #project-base-diff to update your project
-   add Symfony Messenger along with RabbitMQ ([#2898](https://github.com/shopsys/shopsys/pull/2898))
    -   install application to create new necessary containers (run again `./scripts/install.sh`) – this will overwrite your local `docker-compose.yml` file
    -   see #project-base-diff to update your project
-   set the custom logger to the Frontend API ([#2882](https://github.com/shopsys/shopsys/pull/2882))
    -   you can set `shopsys.frontend_api.validation_logged_as_error` parameter to `true` to log validation errors with log level ERROR instead of INFO
    -   see #project-base-diff to update your project
-   start formatting markdown files with Prettier ([#2892](https://github.com/shopsys/shopsys/pull/2892))
    -   see #project-base-diff to update your project
    -   reformat your markdown files by running `php phing standards-fix` in php-fpm container
    -   `standards(-fix)` targets runs newly added `markdown-check/markdown-fix` target, so if you have completely changed the `standards(-fix)` target, remember to add those into your `standards(-fix)` target
-   speed up Product creation in your project ([#2903](https://github.com/shopsys/shopsys/pull/2903))
    -   method `Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade::resolveUniquenessOfFriendlyUrlAndFlush()` has been renamed to `resolveUniquenessOfFriendlyUrl` as it no longer flushes
    -   method `Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPriceFacade::refresh()` has changed its visibility to `protected`
    -   method `Shopsys\FrameworkBundle\Model\Product\ProductFacade::refreshProductManualInputPrices()` has been moved to `Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPriceFacade::refreshProductManualInputPrices()` and changed its visibility to `public`
    -   method `Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPriceFacade::__construct()` changed its interface:
    ```diff
        public function __construct(
            protected readonly EntityManagerInterface $em,
            protected readonly ProductManualInputPriceRepository $productManualInputPriceRepository,
            protected readonly ProductManualInputPriceFactoryInterface $productManualInputPriceFactory,
    +       protected readonly PricingGroupRepository $pricingGroupRepository,
        )
    ```
    -   method `Shopsys\ProductFeed\GoogleBundle\Model\Product\GoogleProductDomainFacade::saveGoogleProductDomain()` changed its visibility to `protected`
    -   method `Shopsys\ProductFeed\HeurekaBundle\Model\Product\HeurekaProductDomainFacade::saveHeurekaProductDomain()` changed its visibility to `protected`
    -   method `Shopsys\ProductFeed\HeurekaBundle\Model\Product\HeurekaProductDomainFacade::saveHeurekaProductDomain()` changed its visibility to `protected`
    -   method `Shopsys\ProductFeed\ZboziBundle\Model\Product\ZboziProductDomainFacade::saveZboziProductDomain()` changed its visibility to `protected`
    -   see #project-base-diff to update your project
-   add consumers and RabbitMQ to deployed application ([#2904](https://github.com/shopsys/shopsys/pull/2904))
    -   set new environment variables `RABBITMQ_DEFAULT_USER`, `RABBITMQ_DEFAULT_PASS`, `RABBITMQ_IP_WHITELIST` in your deployment tool (with use of the default config it will be Gitlab CI)
    -   see #project-base-diff to update your project
-   re-enable phing target cron ([#2875](https://github.com/shopsys/shopsys/pull/2875))
    -   see #project-base-diff to update your project

### Storefront

-   add rounded price value to order process ([#2835](https://github.com/shopsys/shopsys/pull/2835))
    -   see #project-base-diff to update your project
-   remove unnecessary default value for domain config in zustand ([#2888](https://github.com/shopsys/shopsys/pull/2888))
    -   you probably do not need the default value as well, as we set it right at the beginning of page load
    -   see #project-base-diff to update your project
-   fixed undefined window error ([#2882](https://github.com/shopsys/shopsys/pull/2882))
    -   in one of the previous PR's, the `canUseDom` function was removed, but it caused the application to fail in some cases
    -   because of that, the function was brought back as a constant (as `isClient`) and some checks were reinstantiated
    -   in your code, you should pay attention to where you are using the window object and make sure it is available (either by checking the logic or by explicitly wrapping it in a check)
    -   also keep in mind that `canUseDom` is now `isClient` and `isServer` is now used as `!isClient`
    -   for not-discussed changes, see #project-base-diff to update your project
-   add Prettier plugin and ESlint plugins and rules ([#2874](https://github.com/shopsys/shopsys/pull/2874))
    -   see #project-base-diff to update your project
    -   to check if your repo follows the new rules run `pnpm run check` or `pnpm run check--fix` to autofix in Storefront folder or in Storefront Docker container
    -   **Prettier**
        -   added `@trivago/prettier-plugin-sort-imports` plugin
    -   **ESlint**
        -   added `eslint-plugin-no-relative-import-paths` plugin and rules enforcing rules for imports
        -   new rules
            -   no-helpers-are-exported-from-component-file
            -   react/jsx-no-useless-fragment
            -   react/self-closing-comp
            -   react/jsx-sort-props
            -   no-relative-import-paths/no-relative-import-paths
-   add Related Products tab on product detail page ([#2885](https://github.com/shopsys/shopsys/pull/2885))
    -   see #project-base-diff to update your project
-   improve product lists in GrapesJS ([#2879](https://github.com/shopsys/shopsys/pull/2879))
    -   see #project-base-diff to update your project
-   add instant page skeleton after link click ([#2863](https://github.com/shopsys/shopsys/pull/2863))
    -   before page must first load and then skeleton was shown, now we pass page type to `ExtendedLink` component which allow us to display immediately after user click on the link proper skeleton for the required page
    -   some reorganization and renaming was done to Skeletons, we basically have only two types of skeletons, for pages and modules, since it is sometimes difficult to recognise which one is which, we have added Page word, but this was not perfect in folder organization, that's why it's been added word Module as well, to organize skeletons better way
    -   added missing skeletons for Homepage and Stores
    -   adjustments to current skeletons to match the design of a page better
    -   see #project-base-diff to update your project
-   refactoring of various error-related matters on SF ([#2871](https://github.com/shopsys/shopsys/pull/2871))
    -   the goal was to shine light on some of the not-well-understood places in regard of error handling on SF
    -   for you to get the most out of this PR, you should check `error-handling.md` in SF docs, which is a direct result of this PR
    -   it contains explanations and tips on how to improve error handling in your SF code as well
    -   for not-discussed changes, see #project-base-diff to update your project
    -   see #project-base-diff to update your project
-   refactor `ProductVariantsTable` ([#2899](https://github.com/shopsys/shopsys/pull/2899))
    -   `ProductVariantsTable` component was made with table element but on smaller screens it was styled more like list. This was causing styling difficulties. That's why it has been replaced with grid with combination of flexbox.
    -   components `ProductVariantsTableRow` and `Variant` were removed
    -   component `ProductVariantsTable` was renamed to `ProductDetailVariantsTable` so it matches parent folder where it's placed
    -   see #project-base-diff to update your project
-   add equal spacing to the Category page ([#2900](https://github.com/shopsys/shopsys/pull/2900))
    -   see #project-base-diff to update your project
-   auth (loading) improvements ([#2897](https://github.com/shopsys/shopsys/pull/2897))

    -   `window.location.href` assignments were replaced with `router` operations because of unexpected behavior, where the current URL (thus also properties of the router) changed even before the transition, which caused useEffects and some other conditions to re-run and potentially fails
    -   if you manipulate `window.location` in your code, you should remove it and use `router` instead
    -   loading states for all auth operations (login, logout, registration) are now implemented, so if you have some custom states, you should also handle these
    -   optimistic display of skeletons was implemented for `logout-loading` and both `registration-loading...`states

        -   this was possible as we know that the user is going to be redirected to homepage
        -   however, since we do not know the page type of the redirect destination after login, we do not display any skeleton there
        -   if you always know the destination of redirect after login, chances are you can improve the UX by implementing the following changes:

        ```tsx
        // inside useAuth.tsx login
        updateAuthLoadingState(
            loginResult.data.Login.showCartMergeInfo ? 'login-loading-with-cart-modifications' : 'login-loading',
        );

        // add this line to start showing the skeleton before the redirect
        updatePageLoadingState({
            isPageLoading: true,
            redirectPageType: 'my-page-type',
        });

        if (rewriteUrl) {
            router.replace(rewriteUrl).then(() => router.reload());
        } else {
            router.reload();
        }
        ```

-   added USPs on product detail page ([#2887](https://github.com/shopsys/shopsys/pull/2887))
    -   see #project-base-diff to update your project
-   fix sizes of product actions buttons ([#2896](https://github.com/shopsys/shopsys/pull/2896))
    -   now we have unified sizes of add to cart buttons
    -   see #project-base-diff to update your project
-   fix Comparison for not logged in users ([#2905](https://github.com/shopsys/shopsys/pull/2905))
    -   unified code for Comparison and Wishlist
    -   refactored Zustand store to use only one store (User Store) for all cartUuid, wishlistUuid and comparisonUuid
    -   see #project-base-diff to update your project
