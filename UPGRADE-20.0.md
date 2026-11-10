# UPGRADING FROM 19.x to 20.0

The releases of Shopsys Platform adhere to the [Backward Compatibility Promise](https://docs.shopsys.com/en/latest/contributing/backward-compatibility-promise/) to make the upgrades to new versions easier and help long-term maintainability.

## Recommended way of upgrading

- upgrade only your composer dependencies and follow the instructions in the guide below
- upgrade locally first - after you fix all issues caused by the upgrade, commit your changes, test your application, and then continue with a deployment onto your server
- upgrade one version at a time:
    - start with a working application
    - upgrade to the next version
    - fix all the issues you encounter
    - repeat
- when upgrading to a new major or minor version, always upgrade to the latest patch version of that major/minor first (e.g. upgrade to the latest `19.0.x` before moving on to `20.0`); patch releases contain fixes — including database migration fixes — that make the subsequent upgrade safe
- check the instructions in all sections; any of them could be relevant to you
- the typical upgrade sequence should be:
    - run `docker compose down --volumes` to turn off your containers
    - _(macOS only)_ run `mutagen project terminate` first, then `docker compose down --volumes`
    - follow upgrade notes in the _Infrastructure_ section (related to `docker-compose.yml`, `Dockerfile`, docker containers, `nginx.conf`, `php.ini`, etc.)
    - _(MacOS, Windows only)_ run `docker-sync start` to create volumes
    - run `docker compose build --no-cache --pull` to build your images without cache and with the latest version
    - run `docker compose up -d --force-recreate --remove-orphans` to start the application again
    - update the `shopsys/*` dependencies in `composer.json` to the version you are upgrading to
        - e.g., `"shopsys/framework": "v7.0.0"`
    - follow upgrade notes in the _Composer dependencies_ section (related with `composer.json`)
    - run `composer update shopsys/* --with-dependencies`
    - run `npm install` to update the NPM dependencies
    - follow all upgrade notes you have not done yet
    - run `php phing clean`
    - run `php phing db-migrations` to run the database migrations
    - test your app locally
    - commit your changes
    - run `composer update` to update the rest of your dependencies, test the app again, and commit `composer.lock`
- if any of the database migrations do not suit you, there is an option to skip it; see [our Database Migrations docs](https://docs.shopsys.com/en/latest/introduction/database-migrations/#reordering-and-skipping-migrations)
- we may miss something even if we care a lot about these instructions. In case something doesn't work after the upgrade, you'll find more information in the [CHANGELOG](CHANGELOG.md)

#### Movement of features from project-base to packages

- in this version, there are a few features that have been moved from `project-base` to the packages, mostly to the `framework` and the `frontend-api` package
- each section in the upgrade guide contains a link to the `project-base` diff and besides the particular upgrade instructions, there is also a list of the moved features you should be aware of (if there are any)
- if your project was originally not developed from the Commerce Cloud version, or it was developed on a version lower than `v13.0.0`, these feature movements should not affect you during the upgrade
- otherwise, you might need to adjust your project to the changes:
- if you had no custom changes in the moved features, you should be fine, you can safely remove the features from your project and use the ones from the packages (project-base diff in each section will help you with that)
- if you had custom changes in the moved features, you will need to adjust your project to the changes
- you should remove everything that was not modified in your project and keep just the custom changes using the recommended ways of the [framework extensibility](https://docs.shopsys.com/en/latest/extensibility/)
- one way or another, you should pay a special attention to the database migrations that were added with the feature movement. If they suit your needs, you should keep them and remove the original migrations from your project, otherwise, you should skip the newly added migrations.

#### Introduction of strict types

- with each change, we are updating most classes that have been altered by that change to use strict types
- this means that you will need to update your project to use strict types as well
- we do not see writing upgrade notes for such changes as beneficial, as it would mean for you to check every single change manually even if only a few occurrences would apply to your project
- we are currently not aware of easy way to automate this process, so you will need to do it manually
- probably the easiest way is to run `composer install`, `php phing standards-fix` and `php phing phpstan` commands, which will fail on errors caused by incompatibility strict types and fix those errors manually

#### GraphQL performance improvements ([#4561](https://github.com/shopsys/shopsys/pull/4561))

- if you override `getSlug` in any of the following classes, use `Shopsys\FrontendApiBundle\Model\FriendlyUrl\FriendlyUrlSlugBatchLoader` instead:
    - `Shopsys\FrontendApiBundle\Model\Resolver\Brand\BrandResolverMap`
    - `Shopsys\FrontendApiBundle\Model\Flag\FlagResolverMap`
    - `Shopsys\FrontendApiBundle\Model\Resolver\Category\CategoryResolverMap`
    - `Shopsys\FrontendApiBundle\Model\Resolver\Store\StoreResolverMap`
- if you extend `Shopsys\FrontendApiBundle\Model\Resolver\Products\PromotedProductsQuery`, use `Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory::createPromotedOnDomainFilter()` instead of removed properties `$topProductFacade`, `$domain`, `$currentCustomerUser`, and `$productFacade`
- run full product export after deploying to populate new Elasticsearch fields `is_promoted` and `top_product_position`
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/8ae58d0a3e436e641911d07f18bec09fbbb89fd5) to update your project

#### Upgrade backend npm dependencies ([#4614](https://github.com/shopsys/shopsys/pull/4614))

- backend/admin npm dependencies were upgraded to current versions so the root `npm audit` passes without security vulnerabilities
- admin assets were adjusted for major upgrades of Chart.js, Glob, Stylelint, and GrapesJS
- GrapesJS block icons are now defined explicitly through SVG `media` values because the old internal GrapesJS icon font is no longer available
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/805a0043df4af759f51ab6739904d033d32e03b8) to update your project

#### Add Zbozi.cz category to GTM page metadata ([#4600](https://github.com/shopsys/shopsys/pull/4600))

- `Shopsys\ProductFeed\ZboziBundle\Model\FeedItem\ZboziFeedItem` constructor now expects `?string $categoryText` as the fifth parameter instead of `array $pathToMainCategory`
- update custom overrides of `Shopsys\ProductFeed\ZboziBundle\Model\FeedItem\ZboziFeedItemFactory::create()` to accept the new optional `?string $zboziCategoryText = null` parameter
- protected method `Shopsys\ProductFeed\ZboziBundle\Model\FeedItem\ZboziFeedItemFactory::getBrandName()` was removed; inline the brand access in your custom factory if you extend it
- protected method `Shopsys\ProductFeed\ZboziBundle\Model\FeedItem\ZboziFeedItemFactory::getPathToMainCategory()` was removed; provide category text before creating the feed item
- `Shopsys\ProductFeed\ZboziBundle\Model\FeedItem\ZboziFeedItem::CATEGORY_PATH_SEPARATOR` was removed; pass the final category text to the feed item instead
- `Shopsys\ProductFeed\ZboziBundle\DependencyInjection\ShopsysProductFeedZboziExtension` is now final; remove custom inheritance if your project extends it
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/0f4cf3717017268f682f3eb98235d79b3116f1ec) to update your project

#### Improve administration order detail editing ([#4637](https://github.com/shopsys/shopsys/pull/4637))

- if you extend or reference `Shopsys\FrameworkBundle\Controller\Admin\OrderController`, update your code to use the new controllers:
    - use `Shopsys\AdministrationBundle\Controller\OrderListController` for order list, preview, and delete actions
    - use `Shopsys\AdministrationBundle\Controller\OrderDetailController` for order detail, status change, and withdrawal status actions
- if you reference the removed route `admin_order_addproduct`, use the order items Live Component actions instead
- if you override administration order detail templates, replace overrides of `@ShopsysAdministration/content/order/edit.html.twig`, `@ShopsysAdministration/content/order/addProduct.html.twig`, or `@ShopsysAdministration/form/content/PaymentTransactionsType.html.twig` with overrides of the new templates under `@ShopsysAdministration/content/order/detail/`
- if your `Shopsys\FrameworkBundle\Form\Admin\Order\OrderFormType` extension or template override uses fields or groups removed from the order form (`id`, `orderDetail`, `domainIcon`, `orderNumber`, `dateOfCreation`, `status`, `createdAsAdministrator`, `user`, `company`, `heurekaAgreement`, `origin`, `payment`, `gopayStatus`, `transport`, `promoCode`, `orderItems`, `actionBar`, or `paymentTransactionsGroup`), rewrite those customizations for the new order detail sections
    - keep editable custom fields in an extension of `Shopsys\FrameworkBundle\Form\Admin\Order\OrderFormType` and render them in matching section form templates under `@ShopsysAdministration/content/order/detail/sections/`
    - move display-only custom data from form fields to section view templates or custom tabs so it can use dedicated markup and layout
- if you customize order detail tabs or sections, register them through `Shopsys\AdministrationBundle\Component\OrderDetail\OrderDetailTabProviderInterface` or `Shopsys\AdministrationBundle\Component\OrderDetail\OrderDetailSectionProviderInterface`
- method `Shopsys\FrameworkBundle\Model\Order\Item\OrderItemFacade::addProductToOrder()` was removed, use `Shopsys\FrameworkBundle\Model\Order\Item\OrderItemFacade::createProductOrderItemData()` instead
- method `Shopsys\FrameworkBundle\Model\Order\OrderFacade::handleRefundTransactions()` was removed, use `Shopsys\FrameworkBundle\Model\Payment\Transaction\Refund\PaymentTransactionRefundFacade::changeManualRefundedAmount()` or `Shopsys\FrameworkBundle\Model\Payment\Transaction\Refund\PaymentTransactionRefundFacade::executeOnlineRefund()` instead
- property `Shopsys\FrameworkBundle\Model\Order\OrderData::$paymentTransactionRefunds` was removed, use `Shopsys\FrameworkBundle\Model\Payment\Transaction\Refund\PaymentTransactionRefundFacade::changeManualRefundedAmount()` or `Shopsys\FrameworkBundle\Model\Payment\Transaction\Refund\PaymentTransactionRefundFacade::executeOnlineRefund()` instead
- form types `Shopsys\FrameworkBundle\Form\Admin\PaymentTransaction\PaymentTransactionType` and `Shopsys\FrameworkBundle\Form\Admin\PaymentTransaction\PaymentTransactionsType` were removed, use `Shopsys\FrameworkBundle\Form\Admin\PaymentTransaction\PaymentTransactionRefundType` instead
- property `Shopsys\FrameworkBundle\Model\Payment\Transaction\Refund\PaymentTransactionRefundData::$executeRefund` was removed, use `Shopsys\FrameworkBundle\Model\Payment\Transaction\Refund\PaymentTransactionRefundFacade::executeOnlineRefund()` instead
- `Shopsys\FrameworkBundle\Model\Payment\Service\PaymentServiceFacade::refundTransaction()` now returns `bool` and can throw `Shopsys\FrameworkBundle\Model\Payment\Transaction\Refund\Exception\PaymentTransactionRefundFailedException`, update custom callers accordingly
- if you use `Shopsys\FrameworkBundle\Component\FlashMessage\FlashMessageTrait` outside `Shopsys\FrameworkBundle\Controller\Admin\AdminBaseController`, inject `Shopsys\FrameworkBundle\Component\FlashMessage\FlashMessageService`
- if you call protected methods `addFlashMessage()`, `renderStringTwigTemplate()`, `getMessages()`, or `getSession()` from `Shopsys\FrameworkBundle\Component\FlashMessage\FlashMessageTrait`, replace those calls with `Shopsys\FrameworkBundle\Component\FlashMessage\FlashMessageService` or direct `Twig\Environment`/`Symfony\Component\HttpFoundation\RequestStack` dependencies
- if you render `Shopsys\FrameworkBundle\Controller\Admin\FlashMessageController::indexAction`, use `{{ component('Admin:FlashMessages') }}` instead
- if you override or include administration flash message templates, replace usages of `@ShopsysAdministration/partial/flash_message/index.html.twig` or `@ShopsysAdministration/partial/flash_message/_messages.html.twig` with `{{ component('Admin:FlashMessages') }}` or overrides of `@ShopsysAdministration/partial/flash_message/all_messages.html.twig` and `@ShopsysAdministration/partial/flash_message/_toasts.html.twig`
- use the [Extending order detail page](https://docs.shopsys.com/en/latest/cookbook/extending-order-detail-page/) cookbook when rewriting custom order detail fields, tabs, or sections
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/f29712efb1c8fdcc3d87098f8b47f26019e2c786) to update your project and also [project-base diff](https://www.github.com/shopsys/project-base/commit/f29712efb1c8fdcc3d87098f8b47f26019e2c786)-4692 to update the symfony.lock file

#### Allow disabling MCP in production by omitting database credentials ([#4640](https://github.com/shopsys/shopsys/pull/4640))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/7a44d94068137de3ef90b70131007c58940ab052) to update your project

#### Add customer account order and complaint filtering ([#4641](https://github.com/shopsys/shopsys/pull/4641))

- if your project customizes Frontend API GraphQL types for customer complaints or orders, update your project with the new `ComplaintFilterInput`, `ComplaintStatus`, `ComplaintStatusCount`, `complaintStatuses`, `complaintStatusCounts`, and extended `OrderFilterInput` definitions
- check `Version20260608120000` before running migrations if your project changed default order or complaint status IDs, because the migration assigns stable codes to known default statuses by their IDs
- update custom customer order and complaint date filters to pass `createdAfter` and `createdBefore` as UTC `DateTime` values
- if your storefront uses custom customer account GraphQL queries, update them to use the new complaint and order filter inputs instead of adding custom search parameters
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/3b38d6417dfb59bf9d61828f792dc7dbc3077286) to update your project

#### Unify order confirmation and payment return flow ([#4642](https://github.com/shopsys/shopsys/pull/4642))

- replace usages of payment status page validity hash on `Shopsys\FrameworkBundle\Model\Order\Order` with `Shopsys\FrameworkBundle\Model\Payment\ReturnHash\PaymentReturnHashFacade`
    - methods `getOrderPaymentStatusPageValidFrom()`, `setOrderPaymentStatusPageValidFromNow()`, `getOrderPaymentStatusPageValidityHash()`, `setOrderPaymentStatusPageValidityHashToNull()`, `resetOrderPaymentStatusPageValidityHash()`, and `setOrderPaymentStatusPageValidityHash()` were removed
- method `setOrderPaymentStatusPageValidFromNow()` in `Shopsys\FrameworkBundle\Model\Order\OrderFacade` was removed
- replace usages of `Shopsys\FrontendApiBundle\Model\Order\PaymentContentPage\PaymentContentPage` with `Shopsys\FrontendApiBundle\Model\Order\ConfirmationPageContent\ConfirmationPageContent`
- replace usages of `Shopsys\FrontendApiBundle\Model\Order\PaymentContentPage\PaymentContentPageStatusEnum` with `Shopsys\FrontendApiBundle\Model\Order\ConfirmationPageContent\ConfirmationPageContentStatusEnum`
- replace usages of `Shopsys\FrontendApiBundle\Model\Order\PaymentContentPage\PaymentContentPageFactory` with `Shopsys\FrontendApiBundle\Model\Order\ConfirmationPageContent\ConfirmationPageContentFactory`
- replace usages of `Shopsys\FrontendApiBundle\Model\Resolver\Order\OrderSentPageContentQuery` with `Shopsys\FrontendApiBundle\Model\Resolver\Order\OrderConfirmationPageContentQuery`
- remove usages of `Shopsys\FrontendApiBundle\Model\Mutation\OrderPaymentStatusPage\OrderPaymentStatusPageMutation`
- remove usages of `Shopsys\FrontendApiBundle\Model\Resolver\Order\Exception\OrderSentPageNotAvailableUserError`
- Frontend API mutation `SetOrderPaymentStatusPageValidityHashMutation` was removed
- Frontend API mutation `UpdatePaymentStatus` no longer accepts argument `orderPaymentStatusPageValidityHash`
- Frontend API queries `orderSentPageContent` and `orderPaymentPageContent` were removed, use field `Order#confirmationPageContent` instead
- Frontend API type `OrderPaymentPageContent` was replaced by `OrderConfirmationPageContent`
- Frontend API enum `PaymentContentPageStatusEnum` was replaced by `OrderConfirmationPageContentStatusEnum`
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/942b9c14520eb6917e906f13171f4535f561d4e2) to update your project

#### Frontend API now accepts slug queries with a leading slash ([#4649](https://github.com/shopsys/shopsys/pull/4649))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/8a8e65c0e22b40d9e314efc8e4462c491364436e) to update your project

#### Add optional Sentry cron monitoring (check-ins) for cron modules ([#4654](https://github.com/shopsys/shopsys/pull/4654))

- cron modules can opt into [Sentry Cron Monitoring](https://docs.sentry.io/product/crons/) per module via new `shopsys.cron` tag attributes; see the [cron documentation](https://docs.shopsys.com/en/latest/introduction/cron/) for details and configuration
- `Shopsys\FrameworkBundle\Component\Cron\CronFacade::__construct()` now expects `Shopsys\FrameworkBundle\Component\Cron\SentryCronMonitorFacade $sentryCronMonitorFacade` as the last parameter
- `Shopsys\FrameworkBundle\Component\Cron\CronModuleFacade::__construct()` now expects `Shopsys\FrameworkBundle\Component\Cron\SentryCronMonitorFacade $sentryCronMonitorFacade` as the last parameter
- `Shopsys\FrameworkBundle\Component\Cron\CronModuleRunnerFacade::__construct()` now expects `Shopsys\FrameworkBundle\Component\Cron\SentryCronMonitorFacade $sentryCronMonitorFacade` as the last parameter
- `Shopsys\FrameworkBundle\Component\Cron\Config\CronConfig::__construct()` now expects `Shopsys\FrameworkBundle\Component\String\TransformStringHelper $transformStringHelper` as the last parameter
- `Shopsys\FrameworkBundle\Component\Cron\Config\CronConfig::registerCronModuleInstance()` accepts new optional Sentry monitoring parameters (`bool $sentryMonitoring`, `?int $sentryMaxRuntime`, `?int $sentryCheckinMargin`, `?int $sentryFailureThreshold`, `?int $sentryRecoveryThreshold`)
- `Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig::__construct()` accepts a new optional `?Shopsys\FrameworkBundle\Component\Cron\Config\SentryMonitorConfig $sentryMonitorConfig = null` as the last parameter
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/8d19fafb2077ebdb034f3eaa05c73f87e5c6d949) to update your project

#### [SSP-4095] store filter improvement ([#4650](https://github.com/shopsys/shopsys/pull/4650))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/9fc69aa9df4582938bf20fea66f5c276fd18a8e2) to update your project

#### stop reporting 405 MethodNotAllowedHttpException to Sentry ([#4646](https://github.com/shopsys/shopsys/pull/4646))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/ed1bbd15603383bbfc9cfc6ea310d761268eb07d) to update your project

#### Separate Luigi's Box search requests ([#4652](https://github.com/shopsys/shopsys/pull/4652))

- if your project extends `Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxClient`, update overrides or calls of `addSearchSpecificParametersToUrl()` to remove the `$limitsByType` argument
- direct callers can use new `Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxClient::getDataForMultiple()` to load multiple independent Luigi's Box requests while keeping request failures isolated
- replace usages of removed methods `Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxClient::getQuicksearchTypesWithLimits()`, `Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxClient::getMainType()`, and `Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxClient::getMainTypeLimit()` with per-type Luigi's Box search request handling
- if your project extends `Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxBatchLoader`, replace usages of removed property `$mainBatchLoadData` and removed methods `getMainBatchLoadData()` and `getMainType()` with handling based on each `Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxBatchLoadData` item separately
- direct `LuigisBoxBatchLoader` consumers now receive `Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxBatchLoadResult`; use `getData()` for mapped results, `getTotalCount()` for total count, and `getFacets()` for Luigi's Box facets
- replace usages of removed methods `Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxBatchLoader::getTotalByType()`, `getTotalByMetadataKey()`, `getFacets()`, `getFacetsByMetadataKey()`, and `getMetadataKey()` with metadata read from `LuigisBoxBatchLoadResult`

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/541935d7039ec68f67b09f6ba1d69985770dfb65) to update your project

#### Limit GraphQL batch request operations ([#4656](https://github.com/shopsys/shopsys/pull/4656))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/34ae50e9b4837f3a5e032de3816aedb7aec39500) to update your project

#### add Elasticsearch data overview for superadmin ([#4644](https://github.com/shopsys/shopsys/pull/4644))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/40eb8cf0bd2b5c487851fecd1d1406d4491248d2) to update your project

#### Redesign storefront navigation and mobile controls ([#4657](https://github.com/shopsys/shopsys/pull/4657))

- set `NavigationItemData::$type` to `NavigationItemTypeEnum::CATEGORIES` for navigation items with categories created in custom code or fixtures
- update custom usages of `Shopsys\FrameworkBundle\Model\Navigation\NavigationItem::getUrl()`, as it now returns `null` for category dropdown navigation items
- double check `Version20260615115000` migration whether it suits your needs
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/cf1c718cb075407f01caea3a38a092d23a31f1b7) to update your project

#### nginx: uploaded files now download as attachments ([#4660](https://github.com/shopsys/shopsys/pull/4660))

- if your project overrides the `Shopsys\FrameworkBundle\Component\FileUpload\FileUpload` service argument or registers custom file classes, replace `$directoriesByFileClass` with `$filesConfigByFileClass`
    - each file class config now requires `directory` and `visibility` keys
    - use `League\Flysystem\Visibility::PUBLIC` for `Shopsys\FrameworkBundle\Component\Image\Image` and `Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile`
    - use `League\Flysystem\Visibility::PRIVATE` for `Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFile`
- if you instantiate `Shopsys\FrameworkBundle\Component\FileUpload\FileUpload` manually, update the second constructor argument to the new `$filesConfigByFileClass` shape
- replace references to `Shopsys\FrameworkBundle\Component\FileUpload\Exception\MissingFileClassDirectoryMappingException` with `Shopsys\FrameworkBundle\Component\FileUpload\Exception\MissingFileConfigByFileClassException`
- check if your anonymous access to the S3 storage allows only access to the files in the `content` directory
    - customer uploaded files must not be publicly accessible, so the S3 bucket must be configured to allow access only to the `content` directory
    - the policy may look like this (replace `<bucket-name>` with your S3 bucket name and verify your configuration):
    ```json
    {
        "Statement": [
            {
                "Action": ["s3:GetObject"],
                "Effect": "Allow",
                "Principal": { "AWS": ["*"] },
                "Resource": ["arn:aws:s3:::<bucket-name>/web/content/*"]
            }
        ],
        "Version": "2012-10-17"
    }
    ```
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/e97a5dfed5b85a000f75f78eafb84d0886f3461c) to update your project

#### added form for customer questions about products to product detail ([#4661](https://github.com/shopsys/shopsys/pull/4661))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/35fb6fbdde6338f83d3707b618ef81b596670c9d) to update your project

#### Add grouped transport selection to checkout ([#4662](https://github.com/shopsys/shopsys/pull/4662))

- if you create transports in custom data fixtures or imports, set `Shopsys\FrameworkBundle\Model\Transport\TransportData::$group` to an instance of `Shopsys\FrameworkBundle\Model\Transport\TransportGroup` when the transport should be displayed inside a transport group
- if you keep custom image data fixtures, add support for images with entity name `transportGroup` and use it for transport group icons
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/5e5c338fcf579a841bf77c145eea18a3f740c746) to update your project

#### Fix trusted proxies configuration ([#4663](https://github.com/shopsys/shopsys/pull/4663))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/5548bc55e900f851846b2d2ff4e796c3dabb1613) to update your project

#### Limit GraphQL query depth ([#4664](https://github.com/shopsys/shopsys/pull/4664))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/b79218ddefb1ee4d8ed07d30920ab764847627f3) to update your project

#### Count nested fields in GraphQL pagination complexity ([#4665](https://github.com/shopsys/shopsys/pull/4665))

- check your custom Frontend API queries using paginated fields
    - if they newly exceed `query_max_complexity`, reduce nested selections
    - or intentionally raise the configured complexity limit
- remove usages of `@=dynamicPaginationComplexity(...)` from custom Frontend API `*Connection` fields
    - connection fields without custom complexity are now counted automatically
    - if you need custom complexity, define it as a whole-field override
- remove usages of `Shopsys\FrontendApiBundle\Component\ExpressionLanguage\DynamicPaginationComplexityExpressionFunction` and `Shopsys\FrontendApiBundle\Component\ExpressionLanguage\ComplexityCalculator`
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/83c4e81f6c14245be844f1f22311e21ac72626a5) to update your project

#### Admin routes without access rules are denied by default ([#4666](https://github.com/shopsys/shopsys/pull/4666))

- check your custom administration routes with `php bin/console shopsys:admin:access-control --check` and add explicit security attributes to routes without access-control rules
- use `#[ForRole]` together with `#[CanView]`, `#[CanEdit]`, `#[CanCreate]`, or `#[CanDelete]`, or use `#[RequireRole]`, `#[RequirePermission]`, `#[SuperAdminOnly]`, or `#[PublicAccess]` directly
- if a custom administration route is intentionally protected by another security layer and should stay outside route-level access control, add its route name to `shopsys_administration.access_control.additional_excluded_route_names`

#### Add make targets to switch the whole application between dev and production ([#4667](https://github.com/shopsys/shopsys/pull/4667))

- update your local `docker-compose` file from the corresponding `dist` file
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/62697612b2dfb19d19b5ae8d7f74bf8538214729) to update your project

#### Unify CRUD controller page titles, breadcrumbs and pretitles ([#4672](https://github.com/shopsys/shopsys/pull/4672))

- `Shopsys\AdministrationBundle\Component\Config\CrudConfig::setTitle()` was removed. CRUD page titles, breadcrumbs and pretitles are now composed automatically from the entity name (and the concrete record name on edit/detail pages), so per-action custom titles are no longer configurable this way. Remove your `setTitle()` calls; to override the auto-derived entity name, use the new `CrudConfig::setEntityNameSingular()` / `CrudConfig::setEntityNamePlural()` methods and wrap the value in `t()`:
    ```diff
    - $config->setTitle(ActionType::LIST, t('All orders'));
    + $config->setEntityNamePlural(t('Orders'));
    ```

#### Deduplicate parallel refresh token requests ([#4674](https://github.com/shopsys/shopsys/pull/4674))

- refresh token rotation now allows a short 10-second reuse grace period for the same consumed refresh token so almost parallel refresh requests, for example from multiple browser tabs, reuse the token pair generated by the first request instead of logging the customer out
    - after this grace period expires, the consumed refresh token is rejected normally
- update your `\App\FrontendApi\Mutation\Login\RefreshTokensMutation` class to include the new `\Shopsys\FrontendApiBundle\Model\Security\RefreshTokenRotationFacade` constructor dependency
    - if your project only extends the package class without a custom constructor, copy the updated `@method __construct(...)` annotation from [project-base diff](https://www.github.com/shopsys/project-base/commit/4845d3cf6ebc36804a003b70634ba32e10b1d9c9)
    - if your project overrides the constructor or service definition manually, pass `\Shopsys\FrontendApiBundle\Model\Security\RefreshTokenRotationFacade` to the parent constructor
    - if your project overrides `refreshTokensMutation()` or refresh token rotation logic, delegate the refresh token rotation to `RefreshTokenRotationFacade::refreshTokens()` so parallel refresh requests reuse the same generated token pair
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/4845d3cf6ebc36804a003b70634ba32e10b1d9c9) to update your project

#### zbozi tests now select domains by locale ([#4677](https://github.com/shopsys/shopsys/pull/4677))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/ad000f9c029484427be5207b396ff3d12d266e96) to update your project

#### Blog article authors and unified single-value entity picker ([#4682](https://github.com/shopsys/shopsys/pull/4682))

- the `enableRemove` option of `Shopsys\FrameworkBundle\Form\ProductType` was renamed to `enable_remove`
- `Shopsys\FrameworkBundle\Form\ProductType` now uses `Shopsys\FrameworkBundle\Form\SinglePickerType` as its parent type and renders through the shared `single_picker` blocks in `@ShopsysAdministration/form/type/SinglePickerType.html.twig`; the `product_widget` and `product_row` blocks together with `@ShopsysAdministration/form/type/ProductType.html.twig` were removed
- the `ProductPicker` admin JS component was removed; use `SinglePicker` instead (`new SinglePicker($element, onSelectCallback)`)
- select buttons in single-entity picker windows now use the `js-single-picker-window-select` class with `data-picker-id`, `data-picker-name`, and `data-picker-instance-id` attributes; the previous `js-product-picker-select` class with `data-product-id`, `data-product-name`, and `data-instance-id` attributes was removed
- the single file picker JS now registers in `window.PickerInstances`; the `window.FilePickerInstances` global was removed
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/b6edd322f2394819c8bff2a39222527d77235642) to update your project

#### allow restricting products to personal pickup only ([#4671](https://github.com/shopsys/shopsys/pull/4671))

- the native SQL query for transports excluded by cart products was replaced by `Shopsys\FrameworkBundle\Model\Transport\TransportRepository::getProductIdsIndexedByExcludedTransportId()`; move your customizations there if you extended the query in `Shopsys\FrameworkBundle\Model\Transport\TransportVisibilityCalculation::getExcludedTransportIdsByProductsInCart()`
- `Shopsys\FrameworkBundle\Model\Transport\TransportFacade::getVisibleOnCurrentDomainWithEagerLoadedDomainsAndTranslations()` and the `transports` GraphQL query no longer filter out transports excluded by products in the cart; such transports are now returned with the new `Transport.productsBlockingSelectionInCart` field filled and are ordered last (selecting them is still rejected server-side) — if your project relies on these transports being absent from the result, filter out transports with a non-empty `productsBlockingSelectionInCart`
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/ba3a234ac6794b862b7aad321e8101069dc28b52) to update your project

#### Fix coding standard violations ([#4686](https://github.com/shopsys/shopsys/pull/4686))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/d60c935be500d61e20cef26f1a6de35060ae09fa) to update your project

#### Fix minor issues in administration ([#4681](https://github.com/shopsys/shopsys/pull/4681))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/e21faa7d25ffe50fe1b0f6ba08a318649f7ee3de) to update your project

#### payment and order-payment GraphQL operations now require proof of order ownership ([#4693](https://github.com/shopsys/shopsys/pull/4693))

- pass the order's `urlHash` in the new `orderUrlHash` argument of `PayOrder`, `UpdatePaymentStatus`, and `orderPayments` (or the new `orderUrlHash` field of `ChangePaymentInOrderInput`), or call these operations as the logged-in owner of the order, otherwise they now fail
- update your `UpdatePaymentStatus` mutation selections to the new `UpdatePaymentStatusResult` return type (`isPaid`, `lastPaymentStatus`, `hasPaymentInProcess`, `lastExternalPaymentUrl`, `paymentTransactionsCount`, `orderNumber`, `paymentName`, `confirmationPageContent`) instead of the `Order` type
- use the new `Shopsys\FrontendApiBundle\Model\Order\OrderApiFacade::getAuthorizedOrder()` to fetch an order in your custom customer-facing operations wherever possible, `Shopsys\FrontendApiBundle\Model\Order\OrderApiFacade::getByUuid()` performs no ownership check and is meant only for situations where access to the order is already authorized separately (e.g. by a voter)
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/41ab5ecd5160f4efaf891a21f8aa5d6cf5b0f879) to update your project

#### Fix GitLab storefront checks on current Node runtime ([#4690](https://github.com/shopsys/shopsys/pull/4690))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/a55736bad73a2518bbb89860ae7d7dd896beb9dd) to update your project

#### Stop monorepo npm from creating project-base vendor ([#4691](https://github.com/shopsys/shopsys/pull/4691))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/60f3d9d1bca4b45705489a8b7317a58220e6e69b) to update your project

#### Gift vouchers – purchase, generation, redemption ([#4695](https://github.com/shopsys/shopsys/pull/4695))

- a new transport of type `email` is created by migration on all domains (zero price, lowest VAT rate, enabled, no payments) – assign payment methods and adjust its name, texts, and visibility in administration after the upgrade
- a new `gift_voucher` mail template is seeded for all domains – review and translate its subject and body in administration
- the value of a generated gift voucher is the unit price with VAT the customer paid for the voucher product in the order – override `Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucherGenerationFacade::getGiftVoucherValueWithVat()` if your project needs a different value, e.g. a static face value for voucher products sold at a discounted price
- `Shopsys\FrameworkBundle\Model\Order\Order::isPaid()` now returns the order paid flag (set manually in administration, by a paid GoPay transaction, or by full gift voucher coverage; once set, it is never unset) – if you rely on the GoPay-only semantics, use `Shopsys\FrameworkBundle\Model\Order\Order::hasPaidPaymentTransaction()` instead
- the migration `Shopsys\FrameworkBundle\Migrations\Version20260710071411` backfills the new `orders.paid` flag from `PAID` GoPay transactions and `done` order statuses, leaving `paid_at` `NULL` – if you have a more accurate source of truth (e.g. an ERP), run your own `UPDATE orders SET paid = TRUE WHERE ...` after the migration
- the amount sent to GoPay is now `Shopsys\FrameworkBundle\Model\Order\Order::getRemainingAmountToPay()` instead of `Order::getTotalPriceWithVat()` – if you extend `Shopsys\FrameworkBundle\Model\GoPay\GoPayOrderMapper` or `Shopsys\FrameworkBundle\Model\Payment\Service\PaymentServiceFacade`, review your overrides
- the `AvailabilityStatusEnum` GraphQL enum has a new `Digital` value: electronic gift voucher products report it instead of `InStock` (with `Sent by email after payment` as the availability text, `null` `availableStoresCount`, and no store availabilities) – handle the new value in API consumers and in overrides of `Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade`
- the `ApplyPromoCodeToCart` and `RemovePromoCodeFromCart` mutations were removed – use the new unified `ApplyCodeToCart` / `RemoveCodeFromCart` mutations, which resolve on the backend whether the code is a discount coupon or a gift voucher; `ApplyCodeToCart` is guarded by the `apply_code_to_cart` rate limiter (`too-many-code-application-attempts` user error) and reports gift voucher violations via the `GiftVoucher` constraint codes (`INVALID_ERROR`, `NO_LONGER_VALID_ERROR`, `ALREADY_APPLIED_GIFT_VOUCHER_ERROR`, `GIFT_VOUCHER_NOT_REDEEMABLE_ERROR`) at `input.giftVoucherCode` and promo code violations at `input.promoCode`, with an unknown code reported as an invalid promo code
- classes `Shopsys\FrontendApiBundle\Model\Mutation\Cart\PromoCodeMutation` and its mutation methods were removed – extend `Shopsys\FrontendApiBundle\Model\Mutation\Cart\CodeMutation` instead
- demo order UUIDs changed because the email transport shifts transport IDs on fresh installs – update tests that hardcode demo order UUIDs and regenerate Cypress screenshots
- a new `ROLE_GIFT_VOUCHER` administrator role guards the gift voucher administration – grant it to existing administrator role groups that should manage gift vouchers
- `Shopsys\FrameworkBundle\Model\Order\PlaceOrderFacade::placeOrder()` now runs in a database transaction – if you extend the order placement flow, make sure your customizations are safe to roll back together with the order
- `Shopsys\FrameworkBundle\Model\Mail\MailTemplateBuilder` newly receives the mail images location as the `$mailImagesUrlPrefix` constructor argument instead of hardcoding `/public/frontend/mail/` – review your overrides of the constructor or `getMailImageSrc()`
- a payment of the new type `giftVoucher` ("Voucher", zero price, lowest VAT rate, enabled, assigned to all existing transports) is created by migration on all domains – review its transport assignment, name, texts, and visibility in administration after the upgrade; it cannot be deleted and no payment can be created with or changed to this type (exactly one must always exist)
- the gift-voucher-type payment is the only valid payment when applied gift vouchers cover the whole remaining amount, and it is rejected otherwise (`UNAVAILABLE_PAYMENT_ERROR` on `ChangePaymentInCart`/`CreateOrder`, cart modification `paymentUnavailable` when the remaining amount changes later) – adjust API order flows to select it in that case, and handle the new `giftVoucher` value of the `PaymentTypeEnum` GraphQL enum, which also appears in `payments` and transport `payments` responses
- changing the payment of an existing order never offers the gift-voucher-type payment – review overrides of `Shopsys\FrontendApiBundle\Model\Order\OrderPaymentsConfigFactory`
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/00964a1de0b224277ccf4bcbd3766aa91c7ebd6b) to update your project
- see also project-base diff of [#4852](https://github.com/shopsys/shopsys/pull/4852) with additional fixes

#### Allow B2B user managers to access customer management ([#4697](https://github.com/shopsys/shopsys/pull/4697))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/4a5711f662280c525575d4b617d718e2a26e5c87) to update your project

#### AI agent guidance in project-base ([#4700](https://github.com/shopsys/shopsys/pull/4700))

- project-base now ships AI agent documentation — `AGENTS.md`, `CLAUDE.md`, and `.agents/skills/`
- if your project has no AI documentation yet, you can adopt these files as-is
- if you already have your own `AGENTS.md`/`CLAUDE.md` or skills, use them as inspiration and merge what is useful or ignore these changes
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/3f784de712a55e76ed629fc761817b03f8e8bd6d) to update your project

#### Protect storefront refresh tokens with HttpOnly cookies ([#4705](https://github.com/shopsys/shopsys/pull/4705))

- if you override `@ShopsysFrontendApi/SocialLogin/loginAsCustomerUser.html.twig`, remove usages of the no longer available `tokens` variable and do not create authentication cookies in JavaScript
    - if you override `Shopsys\FrontendApiBundle\Controller\SocialNetworkController::loginAction()`, call `Shopsys\FrontendApiBundle\Controller\SocialNetworkController::setTokenCookies()` on the response
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/5b7c7b55b2fbd5f662050c3e7bdbc4f970c50c41) to update your project

#### Enable native media swiping in product galleries ([#4710](https://github.com/shopsys/shopsys/pull/4710))

- add the `imagesCount` field to `Product.config.fields` in `app/config/graphql/types/ModelType/Product/Product.types.yaml`
    ```yaml
    imagesCount:
        type: 'Int!'
        description: 'Number of product images'
        resolve: '@=query("imagesCountByProductPromiseQuery", value, args["type"])'
        args:
            type:
                type: 'String'
                defaultValue: null
    ```
- register the image-count data loader under `overblog_dataloader.loaders` in `app/config/packages/overblog_dataloader.yaml`
    ```yaml
    imagesCountBatchLoader:
        alias: 'images_count_batch_loader'
        batch_load_fn: "@Shopsys\\FrontendApiBundle\\Component\\Image\\ImagesCountBatchLoader:loadByBatchData"
    ```
- run `make generate-schema` to validate and regenerate the GraphQL schema and synchronized storefront artifacts
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/2941727b470dcb7db93e585b1cc384c529bc15f9) to update your project

#### remove no longer necessary uploaded files migration ([#4711](https://github.com/shopsys/shopsys/pull/4711))

- remove `Version20240730113802` migration from your `migrations-lock.yaml`
- `Shopsys\FrameworkBundle\Command\CheckMigrateUploadedFilesCommand` has been removed
- `Shopsys\FrameworkBundle\Command\MigrateUploadedFilesCommand` has been removed
- `Shopsys\FrameworkBundle\Component\Setting\Setting::FILE_STRUCTURE_MIGRATED_FOR_RELATIONS` has been removed
- `check-migrate-uploaded-files`, `migrate-uploaded-files`, and `migrate-uploaded-files-force` phing targets have been removed
    - if you reference any of them in your custom deploy process, remove the references
- if you are upgrading from Shopsys Platform 15.0 or newer, you do not need to do any other steps
- if you are upgrading from Shopsys Platform 14.0 or older, you need to check [#3288](https://github.com/shopsys/shopsys/pull/3288) and do the migration of the uploaded files structure manually
    - note: in version 15.0 we have changed the uploaded files folder structure and introduced a migration command to easily switch to this new structure, after several versions we are removing this command as it was a one time action and is no longer necessary
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/2947578aff948d231de336f31faef930d152b521) to update your project

#### Additional services offered with products ([#4716](https://github.com/shopsys/shopsys/pull/4716))

- method `replaceVatInPaymentsAndTransportsForVatsMarkedForDeletion()` in `Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade` and `Shopsys\FrameworkBundle\Model\Pricing\Vat\VatRepository` was renamed to `replaceVatForVatsMarkedForDeletion()`
- transport and payment prices and the free transport threshold are now calculated from the new `Shopsys\FrameworkBundle\Model\Order\OrderData::getProductsAndAdditionalServicesTotalPriceAfterAppliedDiscounts()` method instead of `getProductsTotalPriceAfterAppliedDiscounts()` — update your customized order processing middlewares accordingly
- if you have overridden the `Mail/Order/products.html.twig` template, update it to render order items of the new `additionalService` type (see the template in the framework package)
- the "Save changes in order" button of drag-and-drop datagrids is now rendered in the shared fixed bar — if you need the original card-footer placement in a custom datagrid, override the new `grid_drag_and_drop_save_button` block of `@ShopsysAdministration/datagrid/grid.html.twig` (see `payment/listGrid.html.twig` for an example)
- if you restrict administrators with role groups, grant the new `ROLE_ADDITIONAL_SERVICE` permission to the groups that should manage additional services
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/83ad8b643a8f02878e2348e92817c29c6e152dc3) to update your project

#### product expected restocking date ([#4715](https://github.com/shopsys/shopsys/pull/4715))

- Frontend API fields `Transport#daysUntilDelivery` and `Transport#isPersonalPickup` have been removed
    - use `Transport#expectedDeliveryDate` instead of `daysUntilDelivery`
    - use `Transport#transportTypeCode` instead of `isPersonalPickup`
    - if your project still needs the removed fields, re-add them to the `Transport` type in your project's Frontend API schema
- [features moved](#movement-of-features-from-project-base-to-packages) from project-base to the frontend-api package:
    - `stores` and `mainImage` field definitions of the `Transport` type (`TransportDecorator.types.yaml`) - remove the duplicated definitions from your project's `Transport.types.yaml`
- Frontend API enum `AvailabilityStatusEnum` has a new value `ExpectedRestock` (`Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityStatusEnum::EXPECTED_RESTOCK`) - make sure all consumers of `Availability#status` handle the third value
- method `getProductAvailabilityDaysForFeedsByDomainId` in `Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade` is now protected - use `getProductAvailabilityDaysOrDateForFeedsByDomainId` instead (returns either the number of days or the expected restocking date)
- methods `getProductAvailabilityInformationByDomainId` and `getProductAvailabilityStatusByDomainId` in `Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade` were removed - use `createProductAvailabilityInfo()` that returns a `ProductAvailabilityInfo` object with both the availability text and the status at once
- method `isWeekend` in `Shopsys\FrameworkBundle\Component\DateTimeHelper\BusinessDayCalculation` was removed - use `Shopsys\FrameworkBundle\Component\DateTimeHelper\DateTimeHelper::isWeekend()` instead
- protected method `getVariantsForDefaultPricingGroup` in `Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductExportRepository` was extracted into the new `Shopsys\FrameworkBundle\Model\Product\ProductSellableVariantsProvider` service (including the request-scoped cache) - the availability of a main variant is now derived from its variants using the same service
- feed items are instantiated manually in their factories, if you extend them, update the `new *FeedItem()` calls:
    - `Shopsys\ProductFeed\GoogleBundle\Model\FeedItem\GoogleFeedItem` constructor parameter `bool $sellingDenied` was replaced by `string $availability` and new optional `?DateTimeImmutable $availabilityDate`
    - `Shopsys\ProductFeed\ZboziBundle\Model\FeedItem\ZboziFeedItem` constructor parameter `?int $availabilityDispatchTime` was replaced by `int|string|null $deliveryDate`
    - `Shopsys\ProductFeed\MergadoBundle\Model\FeedItem\MergadoFeedItem` constructor parameter `$deliveryDays` was widened to `int|string`
- Elasticsearch product index no longer contains the `availability` and `availability_status` fields - the availability is composed at read time from `in_stock` and `expected_restocking_date` (see `Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade::createProductAvailabilityInfo()`)
    - constants `AVAILABILITY` and `AVAILABILITY_STATUS` in `Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportFieldProvider` were removed
- recreate the product index and run full product export after deploying - the product definition gained the `expected_restocking_date` field and dropped the `availability` and `availability_status` fields
- the transports now have a new delivery days setting driving the new `Transport.expectedDeliveryDate` calculation. Check the default values set in `Shopsys\FrameworkBundle\Migrations\Version20260720192055`
    - by default, all transports are set to deliver on working days only (not on weekends, public holidays, nor the internal closed days)
    - adjust your production transports in the administration (or via a custom migration) if necessary
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/6837d52933d8ee978a0bd05460fe99077d0c2bba) to update your project

#### Fix admin assets build failing with webpack 5.109.0 ([#4720](https://github.com/shopsys/shopsys/pull/4720))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/ddb50896704e0920b66cf3098c7709b8a85b17f2) to update your project

#### MCP OAuth client registrations now survive deploys and cache cleaning ([#4719](https://github.com/shopsys/shopsys/pull/4719))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/43b10a0598b1516bab93d564b6a55aeb2d47a765) to update your project

#### add missing factory for AdministratorRoleGroupData ([#4722](https://github.com/shopsys/shopsys/pull/4722))

- `AdministratorRoleGroupData` objects are now created via the new `Shopsys\FrameworkBundle\Model\Administrator\RoleGroup\AdministratorRoleGroupDataFactory`
    - if your project extends `AdministratorRoleGroupData`, override the factory's `createInstance()` method to return your class
- `AdministratorRoleGroupData::fillFromEntity()` was removed, use `AdministratorRoleGroupDataFactory::createFromAdministratorRoleGroup()` instead
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/f1bbe80adf68c4677a39b949b62a16f565a1dc81) to update your project

#### set max upload size in Elfinder and disable chunked uploads ([#4724](https://github.com/shopsys/shopsys/pull/4724))

- Elfinder uploads are now limited to 30 MB (`upload_max_size`), intentionally slightly below the 32 MB PHP (`upload_max_filesize`, `post_max_size`) and nginx (`client_max_body_size`) limits, so the whole multipart request still fits within them and too large files are rejected with a clear message instead of a server error
    - if your project uses different PHP/webserver limits, adjust `upload_max_size` in `app/config/packages/fm_elfinder.yaml` accordingly (keep it slightly below the PHP/webserver limits)
- chunked uploads are disabled (`upload_max_conn: -1`) because chunking does not work with multiple application replicas
    - as a side effect, multiple files selected at once are uploaded one by one instead of in parallel
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/ff54924faae2e466d357362c4b61abd64b82019f) to update your project

#### IndexRepository: find methods now return null instead of throwing an exception ([#4726](https://github.com/shopsys/shopsys/pull/4726))

- `Shopsys\FrameworkBundle\Component\Elasticsearch\IndexRepository::findCurrentIndexNameForAlias()` now returns `null` instead of throwing `ElasticsearchAliasNotFoundException` when the alias does not exist (return type changed from `string` to `?string`)
- `Shopsys\FrameworkBundle\Component\Elasticsearch\IndexRepository::findIndexNamesForAlias()` now returns an empty array instead of throwing `ElasticsearchAliasNotFoundException`
- `Shopsys\FrameworkBundle\Component\Elasticsearch\IndexFacade::resolveExistingIndexName()` return type changed from `string` to `?string`
- `Shopsys\FrameworkBundle\Component\Elasticsearch\IndexFacade::isIndexUpToDate()` now returns `false` instead of throwing `ElasticsearchAliasNotFoundException` when the alias does not exist
- class `Shopsys\FrameworkBundle\Component\Elasticsearch\Exception\ElasticsearchAliasNotFoundException` was removed
- if your project catches `ElasticsearchAliasNotFoundException` around these calls, replace the try-catch with a `null` (or empty array) check

#### links with target="\_blank" get rel="noopener" automatically ([#4732](https://github.com/shopsys/shopsys/pull/4732))

- add `'contains_html' => true` to your own textarea fields holding HTML
- coding standards now collect `*.html.twig` templates, so the new `Shopsys\CodingStandards\CsFixer\MissingLinkRelNoopenerFixer` and the existing `MissingButtonTypeFixer` and `ForbiddenDumpFixer` finally reach them
    - add your `templates` directory to `->withPaths()` in your `ecs.php`
    - run `php phing standards-fix` and expect it to add `rel="noopener"` and `type="button"` in your templates
    - a link whose target comes from a variable cannot be fixed automatically, add the attribute conditionally yourself (see `DisplayOnlyUrlType.html.twig` for an example)
- if your project adds a GrapesJS component with a `target` trait, apply `addRelNoopenerToComponentWithBlankTarget()` in it the way the `link` plugin does
- existing links in your database are not migrated, run an SQL update per wysiwyg column to fix them in one go:

    ```sql
    UPDATE articles
    SET text = regexp_replace(text, '<a((?![^>]*\yrel=)[^>]*\ytarget\s*=\s*"_blank"[^>]*)>', '<a\1 rel="noopener">', 'gi')
    WHERE text ~* '<a(?![^>]*\yrel=)[^>]*\ytarget\s*=\s*"_blank"';
    ```

    (adjust the table and column names and verify on a copy of production data first)

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/45a36b3a25273c27b47912169e8dc356424ab3d0) to update your project

#### remove unused GTM settings from homepage banners ([#4736](https://github.com/shopsys/shopsys/pull/4736))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/d3a50138dd1e5271e18ec8d7a32aa88f7235d6d2) to update your project

#### Upload validation errors now identify the invalid file ([#4721](https://github.com/shopsys/shopsys/pull/4721))

- constraint `Shopsys\FrameworkBundle\Form\Constraints\FileAllowedExtension` (with its validator) and the `extensions` option of `ImageUploadType` were removed in favor of the native `Symfony\Component\Validator\Constraints\File` constraint
    - in `file_constraints` of `Shopsys\FrameworkBundle\Form\ImageUploadType` in your form types, remove the `mimeTypes`/`mimeTypesMessage` settings and restrict the allowed formats via the `extensions` option of the native `Symfony\Component\Validator\Constraints\File` constraint instead (see `Shopsys\FrameworkBundle\Form\Admin\Domain\DomainFormType` for an example)
    - when your `file_constraints` do not restrict the formats, the `File` constraint with `ImageProcessor::SUPPORTED_EXTENSIONS` is added automatically
- constant `Shopsys\FrameworkBundle\Component\Image\Processing\ImageProcessor::SUPPORTED_IMAGE_MIME_TYPES` was removed, the `accept` attribute of the image upload input is now derived from the `extensions` of the effective `File` constraint, so the browser's file picker offers only the formats the validation allows
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/b7438b3b5ecbf94cf0ce8fdff3dfb1172d8c99f7) to update your project

#### enable dead code detection and remove dead code ([#4744](https://github.com/shopsys/shopsys/pull/4744))

- **drop the `#[Override]` attribute from your implementations of these interface methods before upgrading** — the methods no longer exist on the interface and PHP fails with a fatal error at class-declaration time:
    - `Shopsys\AdministrationBundle\Component\Security\AccessControl\AccessControlDataProviderInterface::clearCache()`
    - `Shopsys\Cli\Config\ConfigSectionInterface::getPriority()` — also remove the method from your own `ConfigSectionInterface` implementations
    - `Shopsys\FormTypesBundle\Domain\DomainIdsProviderInterface::getAllIds()`
- `Shopsys\FrameworkBundle\Component\Paginator\PaginatorInterface` was removed and `Shopsys\FrameworkBundle\Component\Paginator\QueryPaginator` no longer implements it
    - replace `PaginatorInterface` type hints with `QueryPaginator`
    - if you have your own paginator implementing this interface, remove the `implements` clause and the `#[Override]` attributes on `getResult()` and `getTotalCount()`
- many autowired services lost unused constructor parameters — if you extend such a service, remove the corresponding arguments from your constructor and its `parent::__construct()` call; the largest changes are `Shopsys\FrameworkBundle\Model\Order\OrderFacade` (31 to 19 parameters), `Shopsys\FrontendApiBundle\Model\Mutation\Login\LoginMutation`, `Shopsys\FrontendApiBundle\Model\SocialNetwork\SocialNetworkFacade`, `Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainElasticFacade`, `Shopsys\FrameworkBundle\Model\Product\ProductVariantFacade`, `Shopsys\FrameworkBundle\Model\Order\OrderDataFactory`, `Shopsys\FrameworkBundle\Component\Domain\DomainFacade`, `Shopsys\FrameworkBundle\Model\Category\CategoryFacade`, `Shopsys\FrameworkBundle\Model\Category\TopCategory\TopCategoryRepository`, `Shopsys\FrameworkBundle\Model\CategorySeo\CategorySeoFacade`, `Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifierFactory`, `Shopsys\FrameworkBundle\Model\Advert\AdvertFacade`, `Shopsys\FrameworkBundle\Model\Product\TopProduct\TopProductRepository`, `Shopsys\FrontendApiBundle\Component\Image\ImageApiFacade` and `Shopsys\FrontendApiBundle\Model\Token\JwtConfigurationProvider`
- the promoted constructor parameters of `Shopsys\FrontendApiBundle\Model\Resolver\Article\Search\ArticlesSearchResultsProviderResolver`, `Shopsys\FrontendApiBundle\Model\Resolver\Brand\Search\BrandSearchResultsProviderResolver`, `Shopsys\FrontendApiBundle\Model\Resolver\Category\Search\CategoriesSearchResultsProviderResolver` and `Shopsys\FrontendApiBundle\Model\Resolver\Products\Search\ProductSearchResultsProviderResolver` are no longer stored as properties — if your subclass reads e.g. `$this->productSearchResultsProviders`, read the parent's `$this->searchResultsProviders` instead
- **audit every `new` call on these classes — their parameters are positional, so a stale call silently passes wrong values instead of failing:**
    - `new Shopsys\ProductFeed\LuigisBoxBundle\Model\FeedItem\LuigisBoxProductFeedItem(...)` — 3rd argument `$catalogNumber` and `$mainCategoryName` are gone
    - `new Shopsys\ProductFeed\MergadoBundle\Model\FeedItem\MergadoFeedItem(...)` — the `$highProductPrice` argument is gone, shifting `$flags` and every later argument
    - `new Shopsys\FrameworkBundle\Model\Feed\FeedRenderer(...)` — pass only the `TemplateWrapper`, drop the leading `Environment`
    - `new Shopsys\FrontendApiBundle\Component\Constraints\TransportInOrder(...)`, `PaymentInOrder(...)`, `CustomerUserRoleGroupAllowEdit(...)` — the removed message arguments shift the position of `$groups` and `$payload`
    - `new Shopsys\AdministrationBundle\Component\Crud\CrudRegistryItem(...)` — drop the removed named arguments
    - `Shopsys\Cli\Config\CoreProjectConfig::fromArray()` no longer takes `$projectPath`, `Shopsys\Cli\Input\YamlConfigLoader::load()` no longer takes `$projectPath`, and `Shopsys\Cli\Input\InteractiveInputCollector::collect()` now takes only `SymfonyStyle $io`
- `new Shopsys\AdministrationBundle\Component\Router\CrudRouteItem(...)` no longer takes the `$controller` and `$pageType` arguments — drop them (a stale call fails with a `TypeError`)

- these classes were removed — remove all their usages and type hints:

    | removed class                                                                                                                                                            | note                                                                                                                                                                                                                                                                                                                                                                            |
    | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
    | `Shopsys\FrameworkBundle\Model\Product\ProductQueryParams`                                                                                                               | never used, no replacement                                                                                                                                                                                                                                                                                                                                                      |
    | `Shopsys\FrameworkBundle\Model\Product\Search\ProductIdsResult`                                                                                                          | use `Shopsys\FrameworkBundle\Model\Product\Search\ProductsResult` returned by `ProductElasticsearchRepository::getSortedProductsResultByFilterQuery()`                                                                                                                                                                                                                          |
    | `Shopsys\FrameworkBundle\Component\Paginator\PaginatorInterface`                                                                                                         | use the concrete `Shopsys\FrameworkBundle\Component\Paginator\QueryPaginator`                                                                                                                                                                                                                                                                                                   |
    | `Shopsys\FrameworkBundle\Model\CategorySeo\CategorySeoMix`                                                                                                               | removed together with `CategorySeoFacade::getCategorySeoMixes()`, no replacement                                                                                                                                                                                                                                                                                                |
    | `Shopsys\FrameworkBundle\Model\Category\CategoryWithLazyLoadedVisibleChildren` and `Shopsys\FrameworkBundle\Model\Category\CategoryWithLazyLoadedVisibleChildrenFactory` | removed together with `CategoryFacade::getCategoriesWithLazyLoadedVisibleChildrenForParent()`, no replacement                                                                                                                                                                                                                                                                   |
    | `Shopsys\FrameworkBundle\Form\Admin\AdvancedSearch\AdvancedSearchFilterTranslation`                                                                                      | no replacement                                                                                                                                                                                                                                                                                                                                                                  |
    | `Shopsys\FrameworkBundle\Component\EntityLog\Model\Grid\EntityLogGridFactory`                                                                                            | removed with the `@ShopsysAdministration/content/entityLog/listGrid.html.twig` template; render entity logs with the `component('Admin:EntityLogTimeline', {...})` Twig component instead (see [the entity log documentation](https://docs.shopsys.com/en/latest/model/log-entity-changes/)) — or copy the factory and the template into your project if you need the grid form |
    | `Shopsys\FrameworkBundle\Model\Advert\Exception\AdvertPositionNotKnownException`                                                                                         | removed with `AdvertPositionRegistry::assertPositionNameIsKnown()`                                                                                                                                                                                                                                                                                                              |
    | `Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterChoiceRepository`                                                                                           | the production filter path builds parameter filter choices via `Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade::getParameterFilterChoicesByIds()`                                                                                                                                                                                                              |
    | `Shopsys\FrameworkBundle\Component\Form\MultipleFormSetting`                                                                                                             | no replacement — also remove the `globalMultipleFormSetting` twig global from your `config/packages/twig.yaml`, otherwise the container fails to build                                                                                                                                                                                                                          |
    | `Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleTransitionEnum`                                                                                                   | the transition names are defined by the `blog_article_publishing` workflow in your `config/packages/workflow.yaml`, use the literal names                                                                                                                                                                                                                                       |

- these methods were removed — replace the usages listed:

    | removed method                                                                                                                                              | replacement                                                                                                            |
    | ----------------------------------------------------------------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------- |
    | `Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryRepository::getAllOfferedAccessoriesByProduct()`                                           | use `getOfferedAccessories()`                                                                                          |
    | `Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchRepository::getSortedProductIdsByFilterQuery()`                                           | use `getSortedProductsResultByFilterQuery()`                                                                           |
    | `Shopsys\ProductFeed\LuigisBoxBundle\Model\FeedItem\LuigisBoxProductFeedItem::getSku()`                                                                     | use `getProductCode()` — note it returns `?string`, not `string`                                                       |
    | `Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade::getAll()` and `Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeRepository::getAll()` | use `Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeRepository::getAllQueryBuilder()->getQuery()->getResult()` |
    | `Shopsys\FrontendApiBundle\Model\User\FrontendApiUserProvider::loadUserByUsername()`                                                                        | use `loadUserByIdentifier()` or `loadUserByToken()`                                                                    |
    | `Shopsys\FrontendApiBundle\Model\User\FrontendApiUser::getUsername()`                                                                                       | use `getUserIdentifier()`                                                                                              |

- these methods were removed with no replacement — delete the calls, and delete your overrides of them:
    - framework — `Shopsys\FrameworkBundle\Component\AttributeRouteControllerLoader::getRouteName()`, `Shopsys\FrameworkBundle\Component\Collector\ShopsysFrameworkDataCollector::getDocsVersion()`, `Shopsys\FrameworkBundle\Component\Cron\CronModule::setStatusFailed()`, `Shopsys\FrameworkBundle\Component\DataFixture\PersistentReference::getReferenceName()`, `Shopsys\FrameworkBundle\Component\Domain\DomainFacade::existsDomainIcon()`, `Shopsys\FrameworkBundle\Component\Error\ExceptionListener::getLastThrowable()`, `Shopsys\FrameworkBundle\Component\Translation\ConstraintMessageExtractor::isMessageArgName()`, `Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileRepository::getAllUploadedFilesByEntity()`, `Shopsys\FrameworkBundle\Migrations\DataModifiers\CountryDataModifierVersion20190121094400::getGroupedByCode()`
    - framework advert/article/blog — `Shopsys\FrameworkBundle\Model\Advert\AdvertFacade::findRandomAdvertByPositionOnCurrentDomain()`, `Shopsys\FrameworkBundle\Model\Advert\AdvertRepository::findRandomAdvertByPosition()`, `Shopsys\FrameworkBundle\Model\Advert\AdvertPositionRegistry::assertPositionNameIsKnown()`, `Shopsys\FrameworkBundle\Model\Article\ArticleRepository::getVisibleArticlesForPlacement()` and `getVisibleArticlesByDomainIdAndPlacementSortedByPositionQueryBuilder()`, `Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleFacade::getAllArticlesCountByDomainId()` and `getAllVisibleOnDomain()`, `Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleRepository::getBlogArticlesByDomainIdAndLocaleQueryBuilderIfInBlogCategory()`, `getAllBlogArticlesCountByDomainId()` and `getAllVisibleOnDomain()`, `Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryFacade::findVisibleMainBlogCategoryIdOnCurrentDomain()` and `Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryRepository::findVisibleMainBlogCategoryIdOnDomain()` (use `findVisibleMainBlogCategoryOnCurrentDomain()` / `findVisibleMainBlogCategoryOnDomain()` and read the id from the returned entity)
    - framework cart/category — `Shopsys\FrameworkBundle\Model\Cart\Cart::changeQuantities()` and `getItemById()`, `Shopsys\FrameworkBundle\Model\Cart\CartFacade::getProductByCartItemId()` and `findCartOfCurrentCustomerUser()`, `Shopsys\FrameworkBundle\Model\Category\CategoryFacade::getCategoriesWithLazyLoadedVisibleChildrenForParent()`, `Shopsys\FrameworkBundle\Model\Category\CategoryRepository::getTranslatedVisibleSubcategoriesByDomain()`, `getCategoriesWithVisibleChildren()` and `getCategoriesWithVisibleChildrenQueryBuilder()`, `Shopsys\FrameworkBundle\Model\Category\TopCategory\TopCategoryFacade::getVisibleCategoriesByDomainId()`, `Shopsys\FrameworkBundle\Model\Category\TopCategory\TopCategoryRepository::getVisibleByDomainId()`, `Shopsys\FrameworkBundle\Model\CategorySeo\CategorySeoFacade::getCategorySeoMixes()` (with the protected `getSeoCategoryMixesFromParameters()`, `getSeoCategoryMixesFromFlags()`, `getSeoCategoryMixesFromOrderings()` and `getNewSeoCategoryMixes()`), `Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix::setOrdering()`
    - framework customer/mail/order — `Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier::getObjectHash()`, `Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifierFactory::get()`, `Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainRepository::findCustomersTokenChains()`, `Shopsys\FrameworkBundle\Model\Mail\MailTemplateDataFactory::createFromOrderStatuses()`, `Shopsys\FrameworkBundle\Model\Mail\MailTemplateRepository::getAllByDomainId()`, `Shopsys\FrameworkBundle\Model\Order\Mail\OrderMail::findMailTemplateForOrderStatus()`, `Shopsys\FrameworkBundle\Model\Order\Item\OrderItem::checkTypeDiscount()`, `checkTypeRounding()` and `checkTypePromotion()`, `Shopsys\FrameworkBundle\Model\Order\OrderFacade::getByOrderNumberAndUser()`, `Shopsys\FrameworkBundle\Model\Order\OrderRepository::getOrdersByCustomerUserId()`, `findLastByCustomerUserId()` and `getByOrderNumberAndCustomerUser()`, `Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlagRepository::deleteByPromoCodeId()`, `Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimitRepository::deleteByPromoCodeId()`
    - framework product/pricing/stock — `Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryRepository::findByProductAndAccessory()`, `Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductExportRepository::extractDetailSlug()`, `Shopsys\FrameworkBundle\Model\Product\Filter\PriceRangeRepository::getPriceRangeInCategory()`, `Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade::getAll()`, `Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository::getAll()`, `getParameterValuesUsedByProductsInCategoryByParameter()` and `getProductParameterValueRepository()`, `Shopsys\FrameworkBundle\Model\Product\ProductFacade::getOneByCatnumExcludeMainVariants()`, `Shopsys\FrameworkBundle\Model\Product\ProductRepository::getListableByIds()` and `getOneByCatnumExcludeMainVariants()`, `Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchRepository::addRelevance()`, `Shopsys\FrameworkBundle\Model\Product\TopProduct\TopProductFacade::getOfferedProducts()`, `Shopsys\FrameworkBundle\Model\Product\TopProduct\TopProductRepository::getOfferedProductsForTopProductsOnDomain()`, `Shopsys\FrameworkBundle\Model\Slider\SliderItemRepository::getAll()`, `Shopsys\FrameworkBundle\Model\Stock\ProductStockFacade::getProductStocksByProductAndDomainId()`, `Shopsys\FrameworkBundle\Model\Stock\ProductStockRepository::getProductStocksByProductAndDomainId()` and `findProductStockByStockAndProduct()`
    - framework heureka/feed — `Shopsys\FrameworkBundle\Model\Heureka\HeurekaFacade::isHeurekaWidgetActivated()`, `Shopsys\FrameworkBundle\Model\Heureka\HeurekaSetting::isHeurekaWidgetActivated()`, `Shopsys\FrameworkBundle\Model\Feed\FeedExportCreationDataQueue::getCurrentFeedModule()`
    - frontend-api — `Shopsys\FrontendApiBundle\Component\Image\ImageApiFacade::getImagesByEntityIdAndNameIndexedById()`, `Shopsys\FrontendApiBundle\Model\Order\OrderRepository::findByUuidAndUrlHash()`, `Shopsys\FrontendApiBundle\Model\Product\BatchLoad\ProductsBatchLoader::loadSellableCountInCategoryByIds()`, `Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnectionFactory::createConnectionForBrand()`, `Shopsys\FrontendApiBundle\Model\Product\ProductFacade::getSellableProductsByIds()`, `Shopsys\FrontendApiBundle\Model\Security\LoginAsUserExchangeTokenRepository::remove()`, `Shopsys\FrontendApiBundle\Model\User\FrontendApiUser::getPassword()` and `getSalt()`
    - administration — `Shopsys\AdministrationBundle\Component\Router\CrudRouteItem::getController()` and `getPageType()`, `Shopsys\AdministrationBundle\Component\Security\AccessControl\RouteAccessControlDataProvider::clearCache()`
    - product-feed-mergado — `Shopsys\ProductFeed\MergadoBundle\Model\FeedItem\MergadoFeedItem::getHighProductPrice()`

- these constants were removed — replace the usages:

    | removed constant                                                                               | replacement                                                                      |
    | ---------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------- |
    | `Shopsys\FrontendApiBundle\Model\Customer\User\LoginType\CustomerUserLoginType::TYPE_WEB`      | use `Shopsys\FrontendApiBundle\Model\Customer\User\LoginType\LoginTypeEnum::WEB` |
    | `Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroup::GEDMO_SORTABLE_LAST_POSITION` | no replacement                                                                   |
    | `Shopsys\ProductFeed\ZboziBundle\Model\ZboziCategory\ZboziCategoryDownloader::SOURCE_ENCODING` | no replacement                                                                   |

- `Shopsys\FrameworkBundle\Model\Stock\StockEvent::CREATE` and `Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroupEvent::CREATE` are now actually dispatched by `StockFacade::create()` and `ParameterGroupFacade::create()` — if you have listeners registered on these events, review them, they never fired before and will start now

- Frontend API validation error codes were removed from the constraints — if your storefront or client maps these codes, drop that handling, and remove the matching named arguments from any validation configuration:
    - `Shopsys\FrontendApiBundle\Component\Constraints\TransportInOrder` — `CHANGED_TRANSPORT_PRICE_ERROR`, `PICKUP_PLACE_UNAVAILABLE_ERROR`, `WEIGHT_LIMIT_EXCEEDED_ERROR` and the `$changedTransportPriceMessage`, `$pickupPlaceUnavailableMessage`, `$weightLimitExceeded` messages
    - `Shopsys\FrontendApiBundle\Component\Constraints\PaymentInOrder` — `CHANGED_PAYMENT_PRICE_ERROR` and the `$changedPaymentPriceMessage` message
    - `Shopsys\FrontendApiBundle\Component\Constraints\CustomerUserRoleGroupAllowEdit` — `LAST_CUSTOMER_USER_ROLE_GROUP_CANNOT_BE_CHANGED` and the `$messageForLastCustomerUser` message

- these properties were removed — stop reading and writing them:
    - `Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition::$index`
    - `Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLogData::$logCollectionNumber` and `$createdAt`
    - `Shopsys\FrameworkBundle\Component\Messenger\AbstractMessageDispatcher::$transportDsn`
    - `Shopsys\FrameworkBundle\Component\Messenger\MessageDispatcherDependency::$transportDsn` (was `public readonly`)
    - `Shopsys\FrameworkBundle\Model\Administrator\Exception\InvalidGridLimitValueException::$limit` and its `getLimit()` getter — the invalid value is still in the exception message
    - `Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainData::$uuid` — `CustomerUserRefreshTokenChain` now always generates a new UUID, so if you relied on prefilling this property to force a specific UUID, generate the entity differently
    - `Shopsys\McpBundle\Model\Administrator\McpToken\AdministratorMcpTokenData::$lastUsedAt` and `$revokedAt` — `AdministratorMcpToken` no longer copies them from the data object; set them through the entity's dedicated methods instead
- the `$transportDsn` and `$allowedAdminLocales` arguments were dropped from the `Shopsys\FrameworkBundle\Component\Messenger\MessageDispatcherDependency` and `Shopsys\FrameworkBundle\Model\Localization\Localization` service definitions — remove the same wiring (`%env(MESSENGER_TRANSPORT_DSN)%`, `%shopsys.allowed_admin_locales%`) if you redefine these services in your project
- the explicit `Shopsys\FrontendApiBundle\Model\Resolver\Order\OrderItemResolverMap` service definition was removed; the class is now registered by the `*Map.php` resolver-map glob — remove your own explicit definition if you only added it to pass `$firstImageBatchLoader`
- update your test infrastructure:
    - `Tests\FrontendApiBundle\Test\GraphQlTestCase::$vatFacade` (`@inject`) was removed — add the `@inject` property to your own test base class if your tests use it
- run `php bin/console shopsys:extended-classes:annotations` after upgrading — the `@method` annotations on your extended classes still reference the removed methods and parameters
- your `phing phpstan` run now also reports dead code (`shipmonk/dead-code-detector` is enabled in the project `phpstan.neon`) — remove newly reported dead members, or declare intentionally kept ones in `phpstan-dead-code.neon` (a whole-class `ignoreErrors` entry for api classes, an exact-message entry with a reason comment for single members with hidden callers)
- your `phing standards` run now fails on unused and useless local variables and on unused variables inherited into closures (`UnusedVariableSniff`, `UselessVariableSniff`, `UnusedInheritedVariablePassedToClosureSniff`) — run `phing standards-fix` and resolve the remaining reports manually
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/c2392ee1970874ec5154bfd3a9279da8b5b47a6e) to update your project

#### Modernize and expand the demo storefront ([#4745](https://github.com/shopsys/shopsys/pull/4745))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/1857fbbe0ec2d67015a03393a75297619ed42b13) to update your project

#### upgrade symfony/mcp-bundle to ^0.12 to fix MCP session save error ([#4749](https://github.com/shopsys/shopsys/pull/4749))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/cc565c6be7043d751c732425f2e1a563631bfbb2) to update your project

#### heureka: removed unused widget code setting ([#4752](https://github.com/shopsys/shopsys/pull/4752))

- the unused `heurekaWidgetCode` setting has been removed, its values are deleted from the `setting_values` table automatically by a migration
- the following methods and constants have been removed, if you use any of them, reimplement them in your project:
    - `Shopsys\FrameworkBundle\Model\Heureka\HeurekaSetting::getHeurekaShopCertificationWidgetByDomainId()`
    - `Shopsys\FrameworkBundle\Model\Heureka\HeurekaSetting::setHeurekaShopCertificationWidgetForDomain()`
    - `Shopsys\FrameworkBundle\Model\Heureka\HeurekaSetting::isHeurekaWidgetActivated()`
    - `Shopsys\FrameworkBundle\Model\Heureka\HeurekaSetting::HEUREKA_WIDGET_CODE`
    - `Shopsys\FrameworkBundle\Model\Heureka\HeurekaFacade::isHeurekaWidgetActivated()`
- the `heurekaWidgetCode` field has been removed from `Shopsys\FrameworkBundle\Form\Admin\Heureka\HeurekaShopCertificationFormType`, if you extend the form and rely on the field, add it in your project

#### Add AI acceptance criteria check running Playwright against the review environment ([#4769](https://github.com/shopsys/shopsys/pull/4769))

- if you want the new `ai:acceptance-criteria` pipeline job, set two new CI/CD variables — the job is silently skipped while either is missing:
    - `JIRA_SITE_URL` with your Jira site root (e.g. `https://yourcompany.atlassian.net`) — the existing `JIRA_BASE_URL` is a REST API base and cannot be reused
    - `CLAUDE_CODE_OAUTH_TOKEN` with a Claude Code OAuth token (generate it with `claude setup-token`)
- if you enable that job, mark `JIRA_SITE_URL`, `JIRA_ACCOUNT`, `JIRA_TOKEN`, `API_TOKEN` and `CLAUDE_CODE_OAUTH_TOKEN` as **masked** CI/CD variables — the job runs the merge request's own checked-out pipeline and wrapper script while holding them, so masking keeps a tampered script from printing them into the job log
- if your review pipelines run only from branches you control, additionally mark those variables **protected** so an untrusted merge request pipeline never receives them; do not protect them if you deploy review environments from ordinary contributor branches, because a protected variable is withheld from such pipelines and the job would then skip
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/b2ace0d59e8f6ac5569ee4baa0df1febc51bbb49) to update your project

#### admin: CRUD lists now support domain controls ([#4741](https://github.com/shopsys/shopsys/pull/4741))

- use `CrudConfig::setListDomainControl()` to display a quick domain filter or the selected-domain switcher above a CRUD list, see [CRUD controller reference](https://docs.shopsys.com/en/latest/administration/crud-controller/reference/crud-controller/) for details
    - the quick domain filter stores its selection under a namespace generated from the controller name; override `AbstractCrudController::getListDomainFilterNamespace()` to share the selection with another list
- new `\Shopsys\FrameworkBundle\Component\Domain\Entity\DomainSeparatedEntityInterface` marks an entity whose every row belongs to exactly one domain — standalone framework entities with a `domainId` field now implement it
    - implement it on your standalone project entities with a `domainId` field to benefit from the automatic domain filtering; per-domain satellite entities of a multi-domain entity (e.g. `ProductDomain`) must not implement it — filter such lists with `addListDomainIdsCondition()` in `configureQuery()`
- CRUD lists with `setListDomainControl()` filter the list query by domain automatically when the entity implements `DomainSeparatedEntityInterface`
    - remove manual domain conditions from `configureQuery()` in your CRUD controllers, or override `applyListDomainFilter()` to customize or opt out
- `AdminDomainFilterTabsFacade::getSelectedDomainId()` and `getSelectedDomainConfig()` have a new optional `$allowedDomainIds` parameter — if you override these methods, add the parameter to your signature; a stored selection outside the allowed IDs is cleared and `null` is returned
- `DomainFilterController::domainFilterTabsAction()` has a new optional `$domainIds` parameter restricting the rendered domain tabs — if you override this method, add the parameter to your signature
- if you have overridden `@ShopsysAdministration/partial/quick_domain_filter.html.twig`, change its wrapping condition from `{% if isMultidomain() %}` to `{% if domainConfigs|length > 1 %}` so a restricted filter hides correctly

#### withdrawal from contract on guest orders requires email confirmation ([#4782](https://github.com/shopsys/shopsys/pull/4782))

- the `OrderWithdrawalRequest` mutation no longer creates a confirmed withdrawal request for guest orders — it stores the request as unconfirmed (new `$confirmed` and `$confirmationHash` properties on `Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequest`) and sends a confirmation email with a 24-hour link to the order email, the request is confirmed only by the new `ConfirmOrderWithdrawalRequest` mutation that consumes the hash and returns the order `urlHash` (orders of registered customers keep the direct confirmed creation), update your custom API clients accordingly
- `Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestFacade::findByOrder()` and `getByOrder()` were renamed to `findConfirmedByOrder()` and `getConfirmedByOrder()` and return only confirmed withdrawal requests (same on `WithdrawalRequestRepository`) — use the new `findIncludingUnconfirmedByOrder()` where your customizations need to see unconfirmed ones (the `Order.withdrawalRequest` field returns unconfirmed requests too, distinguish them by the new `confirmed` field)
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/0b16f1dc9c32d97ab3093592d1b7de5dcb19b01a) to update your project

#### Protect CSV exports against formula injection ([#4777](https://github.com/shopsys/shopsys/pull/4777))

- `Shopsys\FrameworkBundle\Component\HttpFoundation\CsvResponse` now prefixes exported values starting with `=`, `-`, `+`, `@`, tab, CR or LF with an apostrophe
    - if you re-import such an export, strip the apostrophe from the imported values with `Shopsys\FrameworkBundle\Component\Csv\CsvHelper::unescapeFormula()`
    - only values are escaped, not column names, so escape the column names yourself if they can contain user input
    - in your own CSV exports that do not use `CsvResponse`, add `Symfony\Component\Serializer\Encoder\CsvEncoder::ESCAPE_FORMULAS_KEY => true` to the encoder context, or use the new `Shopsys\FrameworkBundle\Component\HttpFoundation\StreamedCsvResponse` for large exports
- `CsvResponse` now respects the passed `$csvHeaders` that were ignored before because of an inverted condition — check the output of your exports that pass custom headers, their column set and order now follow the passed headers
- if you override `Shopsys\FrameworkBundle\Model\PriceList\PriceListFacade::preProcessCsvRow()`, call the parent implementation or unescape the values with `CsvHelper::unescapeFormula()` yourself, otherwise the price list import keeps the escaping apostrophes
- `Shopsys\FrameworkBundle\Controller\Admin\NewsletterController::streamCsvExport()` was removed and the newsletter export is newly streamed by `StreamedCsvResponse` — if you have overridden it, move your changes into your own `StreamedCsvResponse` descendant
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/ceec89b0012e59afc235d288bfcbabfe5475edb1) to update your project

#### Add product reviews ([#4747](https://github.com/shopsys/shopsys/pull/4747))

- enable reviews for selected domains using the `product.reviews.enabled_domain_ids` option in your `config/packages/shopsys_framework.yaml`
- migration `Version20260730120000` adds the `product_reviews_allowed` column to `order_statuses` and sets it to `true` for the `done` order status, so reviews of delivered orders are verified out of the box
    - if this default does not suit you, change the flag in the administration (_Orders → Order statuses_) or write your own migration to set it differently
- bestselling products are now calculated only from the orders of the given domain
    - check your custom queries if this fixed behavior suits you
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/469eee79ce0046e5cb757048a780462c1828a902) to update your project

#### Add product ratings to product lists, structured data and GTM ([#4775](https://github.com/shopsys/shopsys/pull/4775))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/d55e8bf6ef6d4a4f5fe52aaa31fd63871fee6d4d) to update your project

#### Protect entity log change set against XSS via translation parameters ([#4778](https://github.com/shopsys/shopsys/pull/4778))

- entity log change-set formatters now pass only the escaped `%oldReadableValue%` and `%newReadableValue%` placeholders to the translator — the raw `oldValue`, `newValue`, and `dataType` values are no longer available as translation parameters
    - the `from oldReadableValue to newReadableValue` message was renamed to `from %oldReadableValue% to %newReadableValue%` so it is obvious the values are placeholders — move your project override to the new message and use only these two placeholders in its text, any other placeholder now renders as literal text

#### Fix duplicate watchdog button on unavailable product variants ([#4779](https://github.com/shopsys/shopsys/pull/4779))

- two variants of the `32PFL4400` demo product have no stock now - `9176578` does not allow selling below zero stock so it cannot be purchased at all, and `91765782` has an expected restocking date and allows selling below zero stock so it can still be purchased - regenerate your demo data and update the tests that rely on the availability of these products
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/f85f88bc522c4aba2ec5175d607b475ddee68351) to update your project

#### product detail: show delivery options ([#4780](https://github.com/shopsys/shopsys/pull/4780))

- Frontend API field `Product.storeAvailabilities` and the `StoreAvailability` type were removed
    - use `Product.availableStoresCount` for the number of stores where the product is available and the new `productDeliveryStores(productUuid, transportUuid)` query for the per-store pickup dates of a single product
- new Frontend API queries `productDeliveryOptions(productUuid)` and `productDeliveryStores(productUuid, transportUuid)` return the transports usable for a single piece of a product together with their prices and expected delivery dates (types `ProductDeliveryOption`, `ProductDeliveryStore`, and `ProductDeliveryStoreConnection`) — the delivery options popup on the product detail page is built on them
- recreate the product index and run full product export after deploying — the `store_availabilities_information` field was dropped, remove it from your `src/Resources/definition/product/*.json` files
    - constant `STORE_AVAILABILITIES_INFORMATION` in `Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportFieldProvider` was removed, along with the protected method `extractStoreAvailabilitiesInformation` in `Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductExportRepository`
- classes removed without replacement — the per-store availability texts are superseded by the calculated per-store expected delivery dates (`productDeliveryStores` query):
    - `Shopsys\FrameworkBundle\Model\Product\Availability\ProductStoreAvailabilityInformation`
    - `Shopsys\FrontendApiBundle\Model\Resolver\Store\StoreAvailabilityResolverMap`
    - `Shopsys\FrontendApiBundle\Model\Store\StoresBatchLoader` (including the `stores_batch_loader` dataloader)
- methods `getProductStoresAvailabilitiesInformationByDomainIdIndexedByStoreId` and `calculateDaysToWeeks` (with protected `getTransferWeeksByDomainId`, `getWeeksAvailabilityMessageByWeeks`, and constant `DAYS_IN_WEEK`) in `Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade` were removed without replacement — the "Available in X weeks" message no longer exists
- method `getStoreAvailabilities` in `Shopsys\FrontendApiBundle\Model\Resolver\Products\DataMapper\ProductEntityFieldMapper` and `Shopsys\FrontendApiBundle\Model\Resolver\Products\DataMapper\ProductArrayFieldMapper` was removed
- method `getStoresByIds` in `Shopsys\FrameworkBundle\Model\Store\StoreFacade` and `Shopsys\FrameworkBundle\Model\Store\StoreRepository` was removed without replacement — the removed `StoresBatchLoader` was its only caller
- `Shopsys\FrontendApiBundle\Model\Product\ProductFacade::getSellableByUuid()` is now memoized per request and throws `Shopsys\FrontendApiBundle\Model\Resolver\Products\Exception\ProductNotFoundUserError` when the product is not found instead of letting `Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException` propagate — update your catch blocks accordingly (see `Shopsys\FrontendApiBundle\Model\Cart\CartApiFacade::addProductByUuidToCart()`)
- method `getAllWithEagerLoadedDomainsAndTranslations()` in `Shopsys\FrameworkBundle\Model\Payment\PaymentRepository` was renamed to `getAllWithEagerLoadedTransportsAndDomainsAndTranslations()` as it now eager loads the payment transports too
- method `getSellableByUuid()` in `Shopsys\FrontendApiBundle\Model\Product\ProductRepository` was renamed to `getSellableByUuidWithEagerLoadedDomains()` as it now eager loads the product domains
- review the "Days for transfer between warehouses" value in the stock settings on each domain (`Setting::TRANSFER_DAYS_BETWEEN_STOCKS`) — it no longer produces the removed "Available in X weeks" texts and instead postpones the calculated per-store expected pickup dates when the store stock cannot cover the requested quantity
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/e04b6644dccf5d3ee1c41179429e489fbc952c0d) to update your project

#### Add product review photos ([#4781](https://github.com/shopsys/shopsys/pull/4781))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/03404e4c190e650a99d4e5127e0bf51d2fd44767) to update your project
- the custom `FileUpload` constraint in the frontend API was removed in favor of Symfony `All` + `File` constraints
    - `Shopsys\FrontendApiBundle\Component\Constraints\FileUpload`, `FileUploadValidator` and `Exception\FileUploadValidationException` were removed
    - validation error codes changed from `FileUpload::TOO_BIG_ERROR`, `MIMETYPE_ERROR` and `UPLOAD_ERROR` to the codes of `Symfony\Component\Validator\Constraints\File`, the messages keep their wording but use the `{{ name }}` placeholder instead of `{{ fileName }}`
    - if you use the constraint in your own GraphQL types, replace it with `All: [File: {...}]`

#### Selenium/Codeception acceptance tests were removed ([#4784](https://github.com/shopsys/shopsys/pull/4784))

- the Codeception acceptance test suite and all its infrastructure were removed from the platform
    - Phing targets `tests-acceptance`, `tests-acceptance-single`, `tests-acceptance-build`, `selenium-run`, `test-db-dump`, and `acceptance-elasticsearch-export` no longer exist — remove them from your CI pipelines and scripts
    - remove the `selenium-server` service from your `docker-compose.yml` and CI compose files
    - remove the dev dependencies `codeception/codeception`, `codeception/module-db`, `codeception/module-webdriver`, `codeception/phpunit-wrapper`, and `ext-pgsql` from your `composer.json` (`ext-zip` stays — Composer needs it to extract downloaded packages)
    - remove `.env.acc`, `build/codeception.yml`, `config/packages/acc/`, `config/routes/acc/`, `tests/App/Acceptance/`, and `tests/App/Test/Codeception/` from your project, together with the `/ACCEPTANCE` entry in `.gitignore` and codeception-related entries in `phpstan.neon` and `ecs-skip-rules.php`
- the `acc` Symfony environment no longer exists — `EnvironmentType::ACCEPTANCE` and `services_acc.yaml` (with the `OVERWRITE_DOMAIN_URL` env variable and `DomainFactoryOverwritingDomainUrl`) were removed, and the `ACCEPTANCE` toggle file is no longer recognized when resolving the environment; if your project uses its own `acc` configuration, migrate it to another environment
- the `pgsql` PHP extension is no longer installed in the `shopsys/php-image` Docker image (nothing in the platform uses `pg_*` functions anymore) — if your project code relies on them, install the extension in your own `Dockerfile`
- `test-*` CSS classes and the unused `js-grid-column-*`, `js-order-detail-tab-pane`, and `js-advanced-search-rules-box` hooks were removed from administration templates; if your own tests target these selectors, switch them to different ones
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/5e8f15df6b434434be052951f27e1c72eda3b41e) to update your project

#### Remove dead lorem ipsum overwrite flow from the article demo fixture ([#4787](https://github.com/shopsys/shopsys/pull/4787))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/5f67f2771029a9afa8672efabec80c51f1c97571) to update your project

#### Mass generated promo code batch now downloads as a CSV file ([#4790](https://github.com/shopsys/shopsys/pull/4790))

- `Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade::findByMassBatchId()` was removed
    - use `getCodesByMassBatchId()` returning `string[]` of promo codes
- `Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeRepository`
    - method `findByMassBatchId()` was removed; use `getPromoCodeCodes($batchId)` to get codes of a single batch
    - method `getAllPromoCodeCodes()` was renamed to `getPromoCodeCodes()` that now allows filtering by batch ID
- `Shopsys\FrameworkBundle\Controller\Admin\PromoCodeController`
    - method `generateCsvFromPromoCodeFromBatchId()` was removed without replacement; `downloadMassGenerateBatchAction()` now do the CSV generation and download
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/2f7dd581103ee2a812e130d94f01c39ee35112f8) to update your project

#### Improved customer return from the payment gateway ([#4789](https://github.com/shopsys/shopsys/pull/4789))

- the payment return hash is now valid for 60 minutes instead of 30 — if your project needs a different validity window, override `\Shopsys\FrameworkBundle\Model\Payment\ReturnHash\PaymentReturnHashFacade::HASH_TTL_MODIFIER`

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/1351308c9faa8da0faf5cb3ad4c7e8e3aaa2e1c0) to update your project

#### CRUD controller actions are final now ([#4795](https://github.com/shopsys/shopsys/pull/4795))

- `listAction()`, `detailAction()`, `createAction()`, `editAction()` and `deleteAction()` of `Shopsys\AdministrationBundle\Controller\AbstractCrudController` are `final` and cannot be overridden anymore
    - if you overrode an action, move your customization into the dedicated extension points instead:
        - `configureQuery()` for the list query, `configureDatagrid()` for the list columns
        - `configureForm()` for the create and edit form
        - `$config->setTemplate()` and `configureTemplateParameters()` for the rendered template and its variables
        - `CrudCreateHookExtensionInterface`, `CrudEditHookExtensionInterface` or `CrudDeleteHookExtensionInterface` for logic running before, after or on error of the operation
    - if your flow cannot be expressed by these, disable the action with `$config->disableAction(ActionType::EDIT)` in `configure()` and register your own action with its own route, so you fully own it instead of silently breaking the guarantees of the built-in one
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/46bdb01c048004cf0ce8db2fef7c3303d259b502) to update your project

#### Rate limit password recovery requests by IP and e-mail ([#4785](https://github.com/shopsys/shopsys/pull/4785))

- [features moved](#movement-of-features-from-project-base-to-packages) from project-base to the frontend-api package:
    - `App\FrontendApi\Mutation\Customer\User\CustomerUserPasswordRecoveryMutation` → `Shopsys\FrontendApiBundle\Model\Mutation\Customer\User\CustomerUserPasswordRecoveryMutation`
- if you override `recoverPasswordMutation()`, consume `$this->passwordRecoveryIpRateLimiter` before `$validator->validate()`, otherwise the `ExistingEmail` constraint keeps revealing whether the account exists once the limit is reached
- set `TRUSTED_PROXIES` on every environment to your proxies, otherwise all customers share the IP budget of the proxy
- the `RequestPasswordRecovery` and `RecoverPassword` mutations can now fail with the `too-many-password-recovery-attempts` user error code, handle it in your Frontend API consumers
- decide whether the default limits (10 requests per e-mail and 40 per IP per hour, `RecoverPassword` counts towards the IP limit too) fit your traffic before going live
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/2eb18435effa7e069cc29d60f275639f36e23d03) to update your project

#### Public form mutations can be protected against spam bots by FormSpamProtectionFacade ([#4800](https://github.com/shopsys/shopsys/pull/4800))

- check that `framework.trusted_proxies` (the `TRUSTED_PROXIES` environment variable) matches your CDN or load balancer, otherwise the per-IP limit of the mutation either counts all visitors as one address or can be bypassed by spoofing `X-Forwarded-For`
- remove your own service definition of any `*Enum.php` class of the `Shopsys\FrontendApiBundle` namespace, the service resource pattern of `ShopsysFrontendApiBundle` registers them now
- pass `REMOTE_ADDR` in the client options of a functional test that needs a particular client address, `\Tests\App\Test\ApplicationTestCase` gives every test method a random one now
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/0c969d526bbaad3abc01ea336f186bab727d4363) to update your project

#### Deduplicate product recalculation messages in web tests ([#4797](https://github.com/shopsys/shopsys/pull/4797))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/e8d81f1e34d527755e0ba5a868f34162e59ee7c1) to update your project

#### Quick order status filters on the admin order list ([#4803](https://github.com/shopsys/shopsys/pull/4803))

- `@ShopsysAdministration/content/order/list.html.twig` renders both quick filters as dropdowns in one card next to the search card and no longer renders `DomainFilterController::domainFilterTabsAction`
    - if you override this template, render `DomainFilterController::domainFilterSelectAction` and `OrderStatusFilterController::orderStatusFilterSelectAction` instead
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/fbc4886c857bbe69453471d8d2ef12d7d78da3c5) to update your project

#### Improve custom actions and custom routes in CRUD controllers ([#4794](https://github.com/shopsys/shopsys/pull/4794))

- `Shopsys\AdministrationBundle\Component\Config\CrudConfig::setCustomRoleConstant()` was removed. Declare the custom role with the `#[ForRole]` attribute on the CRUD controller class instead (it can also be placed on a CRUD controller extension class to override the extended controller's role); `CrudConfigData::getCustomRoleConstant()` keeps returning it:
    ```diff
     #[CrudController(TransportGroup::class)]
    +#[ForRole(AdminRoleConstant::ROLE_TRANSPORT_AND_PAYMENT)]
     class TransportGroupController extends AbstractCrudController
     {
         public function configure(CrudConfig $config): void
         {
             $config
                 ->setRoutePrefix('/transport-and-payment')
    -            ->setCustomRoleConstant(AdminRoleConstant::ROLE_TRANSPORT_AND_PAYMENT)
                 ->registerHandler(TransportGroupCrudHandler::class);
         }
    ```
- `#[ForRole]` on a CRUD controller now sets the role of the whole controller: the built-in CRUD actions and permission attributes on custom routes are all guarded by that role, and the controller no longer registers its own generated role in the administrator role matrix. If you used `#[ForRole]` on a CRUD controller only to provide a default role for permission attributes on custom routes, remove it (the controller's own role constant is now used automatically), or keep it if the whole controller should be guarded by that role
- permission attributes (`#[CanView]`, `#[CanEdit]`, ...) on custom routes in CRUD controllers no longer require an explicit role — they fall back to the controller's role constant, the same role the built-in CRUD actions use
- the custom role of a CRUD controller (declared by `#[ForRole]`) is resolved at compile time by the new `ResolveCrudRoleConstantsCompilerPass` and read from the new `Shopsys\AdministrationBundle\Component\Crud\CrudRoleConstantProvider` service; `CrudConfig` constructor accepts the resolved custom role as a new optional `$customRoleConstant` parameter, and `CrudControllerRegistry` and `Component\Security\Attribute\AttributeProcessor` constructors accept a new `CrudRoleConstantProvider` dependency
- `shopsys.admin.crud_controllers_extensions` container parameter is now sorted by ascending extension priority

#### Link type articles no longer get a friendly URL ([#4805](https://github.com/shopsys/shopsys/pull/4805))

- these methods were renamed and now match only articles of the site type:
    - `Shopsys\FrameworkBundle\Model\Article\Elasticsearch\ArticleElasticsearchFacade::getBySlug()` was renamed to `getSiteArticleBySlug()`
    - `Shopsys\FrameworkBundle\Model\Article\Elasticsearch\ArticleElasticsearchRepository::getBySlug()` was renamed to `getSiteArticleBySlug()`
    - `Shopsys\FrameworkBundle\Model\Article\Elasticsearch\ArticleElasticsearchRepository::findBySlug()` was renamed to `findSiteArticleBySlug()`
- these methods of `Shopsys\FrameworkBundle\Model\Article\Elasticsearch\ArticleExportRepository` were renamed because they return visible articles of all types, articles of the link type included:
    - `getVisibleArticleSitesCountByDomainId()` was renamed to `getVisibleArticlesCountByDomainId()` and no longer excludes articles of the link type, so the count matches the articles the other two methods return
    - `getAllVisibleArticleSitesByDomainId()` was renamed to `getAllVisibleArticlesByDomainId()`
    - `getVisibleArticleSitesByDomainIdAndArticleIds()` was renamed to `getVisibleArticlesByDomainIdAndArticleIds()`
- friendly URLs of articles switched to the link type are kept in the database, but they no longer resolve
    - if your project links to such articles by their friendly URL, link to the article target URL instead
- if you override `Shopsys\FrameworkBundle\Model\Article\ArticleFacade::create()` or `::edit()`, keep them from creating a friendly URL for an article of the link type, and let an article switched back to the site type get its friendly URL created even when its name did not change
- if you override `Shopsys\FrameworkBundle\Model\Article\Elasticsearch\ArticleExportRepository::extractArticle()`, do not read the main friendly URL for an article of the link type - it has none, export empty `slug`, `mainSlug` and `breadcrumb` for it instead
- if you override `Shopsys\FrameworkBundle\Model\Article\ArticleDetailFriendlyUrlDataProvider` or `Shopsys\FrameworkBundle\Model\Sitemap\SitemapRepository`, filter out articles of the link type there as well
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/1a8387b7342594b8512c61cb9351c2f80d7acdff) to update your project

#### Do not change the main friendly URL when an entity is renamed ([#4810](https://github.com/shopsys/shopsys/pull/4810))

- `Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade::createFriendlyUrls()` and `createFriendlyUrlForDomain()` no longer create anything for a domain where the entity already has a main friendly URL, so renaming an entity keeps its main URL unchanged
    - if your project relies on the previous automatic regeneration of the main URL on rename, override the new protected method `Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade::shouldCreateFriendlyUrl()` to return `true`
- protected methods `createFriendlyUrlsWhenRenamed()` and `getChangedNamesByLocale()` were removed from `Shopsys\FrameworkBundle\Model\Product\ProductFacade` and `Shopsys\FrameworkBundle\Model\Category\CategoryFacade`
    - if you call or override them, call `Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade::createFriendlyUrls()` with the current entity names instead
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/57f44f94ca3260790475bb1662260a2865da4dd4) to update your project

#### Fix standalone PHPStan builds of framework and project-base ([#4811](https://github.com/shopsys/shopsys/pull/4811))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/574de7603a4322351052f8c37e40bf1aa517ca58) to update your project

#### Disabled ProductsType no longer renders editing controls ([#4804](https://github.com/shopsys/shopsys/pull/4804))

- `ProductsType` now honours Symfony's `disabled` option in its template — a disabled products list is rendered without the "Add product" button, the delete action, the drag handle, the `js-products-picker` hook class and the `data-products-picker-*` attributes
    - if you override `@ShopsysAdministration/form/type/ProductsType.html.twig`, apply the same conditions in your template, otherwise a disabled field keeps showing controls that do nothing

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/3ffe3046c15c92b3e3a9588c781debdc733dd89e) to update your project

#### Webalize uploaded file names to prevent issues with special characters ([#4812](https://github.com/shopsys/shopsys/pull/4812))

- the `$name` property of `Shopsys\FrameworkBundle\Component\AbstractUploadedFile\AbstractUploadedFile` is now sanitized by a PHP 8.4 property hook (`Nette\Utils\Strings::webalize()` with a `file` fallback for empty results) on every write, including direct `$this->name = ...` assignments in subclasses
    - if you redeclare the `$name` property in your project entities, you will remove this sanitization — re-apply the hook in your redeclaration if you want to keep it
- names of already existing uploaded files (`uploaded_files` and `customer_uploaded_files`) will be rewritten by the one-time post-deploy task `webalize_uploaded_file_names` on your next deploy
    - if you need to keep the original names, disable the task in your `shopsys_framework.post_deploy.tasks` configuration by setting `run: never` before deploying

#### VAT rate deletion now runs synchronously within the request ([#4815](https://github.com/shopsys/shopsys/pull/4815))

- deleting a VAT rate with a replacement no longer marks it for deletion for the `VatDeletionCronModule` — `VatFacade::deleteById()` now replaces the rate in products, payments, and transports, dispatches the product recalculation, and deletes the rate immediately
- remove the `Shopsys\FrameworkBundle\Model\Pricing\Vat\VatDeletionCronModule` registration from your `config/cron.yaml` — the class was removed
- the whole "marked for deletion" state was removed, adjust your project if you relied on it:
    - `Vat::$replaceWith` with `getReplaceWith()`, `markForDeletion()`, and `isMarkedAsDeleted()` were removed (a migration drops the `vats.replace_with_id` column)
    - `VatFacade::replaceVatInPaymentsAndTransportsForVatsMarkedForDeletion()` and `VatFacade::deleteAllReplacedVats()` were removed
    - `VatRepository::existsVatToBeReplacedWith()`, `VatRepository::getVatsMarkedForDeletionWithoutReferences()`, and `VatRepository::replaceVatInPaymentsAndTransportsForVatsMarkedForDeletion()` were removed
    - `VatRepository::replaceVat()` now replaces the VAT in products too
    - `ProductInputPriceFacade::replaceBatchVatAndRecalculateInputPrices()` and `ProductRepository::getProductIteratorForReplaceVat()` were removed
    - the exceptions `VatMarkedAsDeletedDeleteException` and `VatWithReplacedDeleteException` were removed
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/2e2eb1384179165b128f7c813c9f0dff5c6f94a3) to update your project

#### Replace Webpack Encore with Rsbuild and Symfony Reprise ([#4818](https://github.com/shopsys/shopsys/pull/4818))

- replace `encore_entry_link_tags()` / `encore_entry_script_tags()` with `reprise_entry_link_tags()` / `reprise_entry_script_tags()` in your own templates (e.g. an overridden `@ShopsysAdministration/layout/base.html.twig`), and remove the `defer` attribute and the `//localhost:35729/livereload.js` script there
- add `import $ from 'jquery';` to every JavaScript module of yours that uses `$` or `jQuery` — Encore's `autoProvidejQuery()` was removed, `window.$` / `window.jQuery` (set by `assets/js/jQuery/registerJquery.js`) remain only for third-party plugins and exist only after that module is executed
- rewrite inline `<script>` blocks in your Twig templates that use `$` / `jQuery` to plain DOM APIs — the admin bundles are ES modules and run after inline scripts, see `Shopsys\FrameworkBundle\Component\CKEditor\CKEditorRendererDecorator::renderWidget()` and `@ShopsysAdministration/content/promoCode/listMassGeneratedBatch.html.twig` for the pattern
- add the `?raw` query to your SVG imports that relied on the webpack `asset/source` rule (`import Icon from 'icons/tabler/check.svg?raw';`)
- `select2` and `jquery-hoverintent` were removed from the `@shopsys/framework` npm package dependencies — add them to your `package.json` if your code uses them
- `js/common/plugins/jquery.fix.clone.js` was removed from the `@shopsys/framework` npm package — copy the plugin into your project if you imported it
- if you customized `webpack.config.js`, port your customizations to `rsbuild.config.ts` — entries, aliases, and static copies have direct equivalents, webpack loaders and plugins do not carry over
- if you override `Shopsys\FrameworkBundle\Component\HttpFoundation\SecurityHeadersResponseListener::getDevelopmentCspAppendices()`, add `http://localhost:35729` and `ws://localhost:35729` to `default-src`, `style-src`, and `connect-src` so the Rsbuild dev server and its HMR websocket are allowed in development
- if your PHP code reads `web/build/entrypoints.json` itself, use `Symfony\Reprise\Asset\EntrypointsLookupInterface` instead — the file format is now owned by Reprise and has changed
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/f0e465f8045e1ae9aabbfec03dfc0b610e0f4c1e) to update your project

#### VAT selects in admin now show the rate percentage ([#4813](https://github.com/shopsys/shopsys/pull/4813))

- `VatFacade::getAllForDomainIncludingMarkedForDeletion()` and `VatRepository::getAllForDomainIncludingMarkedForDeletion()` were removed
    - their implementation has been identical to `getAllForDomain()`, so use `getAllForDomain()` instead, or implement the distinction yourself if your project needs it
- for VAT selection in the admin, new `VatChoiceType` was introduced. Check your custom forms for such selections and replace them with `VatChoiceType` to keep the UX uniformity.

#### Cancelled order no longer offers to repeat the online payment ([#4820](https://github.com/shopsys/shopsys/pull/4820))

- `PayOrder` mutation now returns the `order-cancelled` user error for a cancelled order, handle it if your storefront has custom payment error handling
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/ce198266b2005a10212cd30b60fde7bc1d394ce7) to update your project

#### Sanitize, validate and default entity properties with property hooks ([#4821](https://github.com/shopsys/shopsys/pull/4821))

- if your project relies on whitespace-only or padded values in `$namePrefix` and `$nameSuffix` of `Shopsys\FrameworkBundle\Model\Product\ProductTranslation`, `$trackingInstruction` of `Shopsys\FrameworkBundle\Model\Transport\TransportTranslation`, `$name` of `Shopsys\FrameworkBundle\Model\Product\Unit\UnitTranslation`, `Shopsys\FrameworkBundle\Model\Product\Flag\FlagTranslation`, `Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusTranslation`, `Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusTranslation`, `Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupTranslation`, `Shopsys\FrameworkBundle\Model\Country\CountryTranslation`, `Shopsys\FrameworkBundle\Component\Image\ImageTranslation`, `Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileTranslation`, `$name`, `$description` and `$perex` of `Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleTranslation`, or `$name` and `$description` of `Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryTranslation`, override the `set` hook of the property in your entity, see [Property hooks](https://docs.shopsys.com/en/latest/model/entities/#property-hooks)
- make sure your imports and data fixtures fill `Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueData::$numericValue` with a numeric string or `null`, other values are now rejected with `InvalidArgumentException`
    - replace calls of the removed `Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue::setNumericValue()` with assigning `ParameterValueData::$numericValue` and calling `edit()`
    - if you override `convertStringToNumericValue()` in `Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueConversionDataFactory`, return a numeric string (`'0'` for a text without a number), an empty string is rejected
- if your project assigns `$product` of `Shopsys\FrameworkBundle\Model\Order\Item\OrderItem` on an item of another type than product or product gift, override the `set` hook of the property in your entity, it now throws `WrongItemTypeException`, see [Property hooks](https://docs.shopsys.com/en/latest/model/entities/#property-hooks)
- remove the `$slug` argument from your `new UploadedFile(...)` and `new CustomerUploadedFile(...)` calls (`Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile`, `Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFile`) and replace calls of the removed `Shopsys\FrameworkBundle\Component\AbstractUploadedFile\AbstractUploadedFile::setSlug()` with `setName()`, the slug is derived from the name
- if you override `toAscii()` in `Shopsys\FrameworkBundle\Component\String\TransformStringHelper`, make it `protected static`
- replace calls and overrides of the removed `stringToFriendlyUrlSlug()` in `Shopsys\FrameworkBundle\Component\String\TransformStringHelper` with the static `createFriendlyUrlSlug()`, the framework calls it on your class through `Shopsys\FrameworkBundle\Component\ClassExtension\ExtendedClassNameResolver`
- if you override the removed `setRoundingType()` in `Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency`, move the logic into the `set` hook of `$roundingType` or extend `getRoundingTypes()`
- if you override the removed `getSerializedChangeSet()` in `Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLog`, move the logic into the `set` hook of `$changeSet`
- `Shopsys\FrameworkBundle\DependencyInjection\Compiler\RegisterProjectShopsysClassExtensionsCompilerPass` and `Shopsys\FrameworkBundle\Component\ClassExtension\ClassExtensionRegistry` were removed, the map of extended classes is the `shopsys.class_extension.extended_class_names` parameter built by `Shopsys\FrameworkBundle\DependencyInjection\Compiler\RegisterExtendedClassNamesCompilerPass`, if you override the registry or call the removed `addExtendedService()`, move your additions into that parameter in a compiler pass
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/6bd3f73a22463e54b0fbf15abfac3c1e40121671) to update your project

#### Unify image ALT texts and fallback behavior ([#4825](https://github.com/shopsys/shopsys/pull/4825))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/7757c1f6818be51b2562d7b204877dd1b7d12d18) to update your project

#### Send delayed messages of successful handlers even when a sibling handler fails ([#4823](https://github.com/shopsys/shopsys/pull/4823))

- handlers of `messenger.bus.default` are wrapped by `Shopsys\FrameworkBundle\Component\Messenger\DelayedEnvelope\SegmentedHandlersLocator`, so the handler names stored in `Symfony\Component\Messenger\Stamp\HandledStamp` change from `App\FooHandler::__invoke` to `Closure@App\FooHandler::__invoke`
    - messages waiting in a retry delay or in the failure transport at the time of the deployment carry the old names, so their already successful handlers run once more when the messages are retried; drain the retry and failure queues before deploying if a duplicate side effect is not acceptable
- if your project defines additional message buses, decorate their handlers locator (`<bus id>.messenger.handlers_locator`) with `SegmentedHandlersLocator` the same way as in the framework `services.yaml`, otherwise messages dispatched by successful handlers of those buses are still lost when a sibling handler fails

#### Complete structured data for organizations, products and articles ([#4826](https://github.com/shopsys/shopsys/pull/4826))

- move any company details previously hardcoded in your storefront's `LogoMetadata` component into **Settings → SEO → SEO → Organization** for each domain, and upload the organization logo there
- review the organization tax identifiers in **Settings → SEO → SEO → Organization** for each domain: the existing tax number is retained and now supplies JSON-LD `taxID`; enter the VAT identification number, including its country prefix, separately to supply `vatID` — the new field is empty by default and is not derived from the tax number
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/406bdd60e04a9cc8c64204f905de117b3b3e3a07) to update your project

#### Adapt to doctrine/orm 3.7 with SortDirection enum ordering ([#4827](https://github.com/shopsys/shopsys/pull/4827))

- run `php bin/console doctrine:schema:validate --skip-sync` and fix the reported mappings
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/5da49fa4d0e5f4c97491cb5af1bef5c03487de07) to update your project

#### Redesign product comparison and preserve product order ([#4828](https://github.com/shopsys/shopsys/pull/4828))

- run the framework migration `Shopsys\FrameworkBundle\Migrations\Version20260908120000` to add the non-nullable `position` column to `product_list_items`
    - existing items receive zero-based positions within each list, preserving their previous `created_at DESC, id DESC` order; the `created_at` column is retained
- if you use custom queries to order product list items, replace `created_at DESC, id DESC` with `position ASC, id DESC` to respect the saved product order
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/98c0b0273acf3bde3e06507a8b42bf64895e65f8) to update your project

#### Datagrid default order now uses the native `SortDirection` enum ([#4840](https://github.com/shopsys/shopsys/pull/4840))

- `Shopsys\AdministrationBundle\Component\Datagrid\OrderingEnum` was removed, use the native PHP `\SortDirection` enum instead (polyfilled by `symfony/polyfill-php86` on older PHP versions)
    - `Shopsys\AdministrationBundle\Component\Datagrid\Datagrid::setDefaultOrder()` now expects `\SortDirection` as the second argument
    - replace `OrderingEnum::ASC` with `SortDirection::Ascending` and `OrderingEnum::DESC` with `SortDirection::Descending` in your CRUD controllers

#### Default pricing group can no longer be deleted without a replacement ([#4833](https://github.com/shopsys/shopsys/pull/4833))

- `PricingGroupFacade::delete()` no longer accepts the third `$selectedDomain` argument, the domain is now taken from the deleted pricing group itself
    - the method throws `PricingGroupIsUsedException` when the pricing group is used (default on its domain or assigned to customers) and no replacement is given
    - the method throws `InvalidPricingGroupReplacementException` when the replacement is the deleted pricing group itself or belongs to another domain
- methods in `PricingGroupSettingFacade` no longer accept the `DomainConfig` argument, the domain is now taken from the pricing group itself
    - `isPricingGroupUsedOnDomain()` was renamed to `isPricingGroupUsed()`
    - `isPricingGroupDefaultOnDomain()` was renamed to `isPricingGroupSetAsDefault()`
    - `setDefaultPricingGroupForDomain()` was renamed to `setPricingGroupAsDefault()`

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/da27d8989dd80c164d49d6c401ced6174f394cbe) to update your project

#### PHPStan now understands `Webmozart\Assert\Assert` calls ([#4842](https://github.com/shopsys/shopsys/pull/4842))

- `phpstan/phpstan-webmozart-assert` was added as a dev dependency and enabled in `phpstan.neon`, so PHPStan narrows types after `Assert::isInstanceOf()` and similar calls
- run `composer update phpstan/phpstan-webmozart-assert` in your project
- the extension may report `Assert::*()` calls that always evaluate to true - fix or remove such asserts in your project (e.g. `Assert::allNotNull()` on untyped data object properties was replaced by separate `Assert::notNull()` calls in `OrderFacade`)
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/f4073f2ca4a2273a2f940ff6390346a8ab58ae3e) to update your project

#### Cart and order processing performance optimizations ([#4890](https://github.com/shopsys/shopsys/pull/4890))

- method `fillOrderDataFromCart()` in `Shopsys\FrameworkBundle\Model\Order\OrderFacade` was removed - use `Shopsys\FrameworkBundle\Model\Order\OrderFacade::createOrderDataFromCart()` instead, or create the order input with `Shopsys\FrameworkBundle\Model\Order\Processing\OrderInputFactory::createFromCart()` and pass it to `Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessor::processMemoized()`, or to `Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessor::process()` together with your own pre-filled `OrderData`
- if your project extends `Shopsys\FrameworkBundle\Model\Transport\TransportPriceProvider` or `Shopsys\FrameworkBundle\Model\Payment\PaymentPriceProvider` and uses the `$this->orderDataFactory` property, use `$this->orderProcessor->processMemoized($orderInput)` instead - the property was removed from the parent constructors
- do not modify the `OrderData` returned from `Shopsys\FrameworkBundle\Model\Order\OrderFacade::createOrderDataFromCart()` or `Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessor::processMemoized()` - it is memoized in the request-scoped `Shopsys\FrameworkBundle\Component\Cache\InMemoryCache` and shared by every caller with the same order input, so a modification would leak into the transport and payment prices, the cart watcher and the created order
    - if you need to modify the result, call `Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessor::process()` with your own `OrderData` instead
- if your project adds a property to `Shopsys\FrameworkBundle\Model\Order\Processing\OrderInput` or `Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct`, add it to `getFingerprintData()` of that class as well - the processed order data is memoized by `Shopsys\FrameworkBundle\Model\Order\Processing\OrderInput::getFingerprint()` and the `Tests\App\Functional\Model\Order\Processing\OrderInputFingerprintCoverageTest` functional test fails otherwise
    - values stored in the additional data must be JSON-encodable (scalars, arrays, enums, dates) - store entities by their ids or override `getFingerprintData()` to convert them
- method `cleanAdditionalData()` in `Shopsys\FrameworkBundle\Model\Order\Processing\OrderInput` was removed - create a new order input without the value instead
- `Shopsys\FrameworkBundle\Model\Transport\TransportPriceProvider` and `Shopsys\FrameworkBundle\Model\Payment\PaymentPriceProvider` no longer run the order processor with the requested transport or payment set - they price the transport or payment with `Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation::calculatePriceForProcessedOrder()` or `Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation::calculatePriceForProcessedOrder()` from the order data processed for the cart as it is, the same way `Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\AddTransportMiddleware` and `Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\AddPaymentMiddleware` do
    - if your project changes the transport or payment price in an order processing middleware instead of in the price calculations, or if a middleware before `AddTransportMiddleware` depends on the selected transport or payment, override `Shopsys\FrameworkBundle\Model\Transport\TransportPriceProvider::resolveTransportPrice()` and `Shopsys\FrameworkBundle\Model\Payment\PaymentPriceProvider::getPaymentPrice()` so the prices shown for the offered transports and payments match the created order
    - move `Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\AddProductGiftsMiddleware` before `Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\FreeTransportAndPaymentInformationMiddleware` in `shopsys_framework.order.processing_middlewares` - priced product gifts now count toward the free transport and payment limit, and the offered transport and payment prices (calculated from the fully processed order) would otherwise differ from the created order when a gift price reaches the limit
- if your project extends `Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\AddPaymentMiddleware` and uses the `$this->currencyFacade` property, use `Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation::calculatePriceForProcessedOrder()` to price the payment or inject `Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade` into your subclass - the property was removed from the parent constructor
- class `Shopsys\FrameworkBundle\Model\Transport\TransportPriceRepository` was removed - use `Shopsys\FrameworkBundle\Model\Transport\TransportPriceFacade::getTransportPriceOnDomainByTransportAndClosestWeight()` instead, it selects the price from the prices already loaded on the transport
    - if your project extends `Shopsys\FrameworkBundle\Model\Transport\TransportPriceFacade`, remove the repository from your `parent::__construct()` call - the facade has no constructor arguments anymore
- methods `checkTransportPaymentRelation()` in `Shopsys\FrontendApiBundle\Model\Transport\TransportValidationFacade` and `checkPaymentTransportRelation()` in `Shopsys\FrontendApiBundle\Model\Payment\PaymentValidationFacade` now accept `Shopsys\FrameworkBundle\Model\Cart\Cart` instead of the cart UUID - resolve the cart once with `Shopsys\FrontendApiBundle\Model\Cart\CartApiFacade::getCartCreateIfNotExists()` and pass it
- method `checkPaymentSuitabilityForRemainingAmountToPayInCart()` in `Shopsys\FrontendApiBundle\Model\Payment\PaymentValidationFacade` was removed - use `Shopsys\FrontendApiBundle\Model\Payment\PaymentValidationFacade::checkPaymentSuitabilityForRemainingAmountToPay()` with the resolved cart instead
- if your project overrides the protected `check*()` methods of `Shopsys\FrontendApiBundle\Component\Constraints\TransportInCartValidator` or `Shopsys\FrontendApiBundle\Component\Constraints\PaymentInCartValidator`, update their signatures - they now receive the resolved `Shopsys\FrameworkBundle\Model\Cart\Cart` instead of the cart UUID
- if your project extends `Shopsys\FrontendApiBundle\Model\Transport\TransportValidationFacade` or `Shopsys\FrontendApiBundle\Model\Payment\PaymentValidationFacade` and uses the `$this->currentCustomerUser` or `$this->cartApiFacade` properties, inject the services into your subclass - they were removed from the parent constructors
- if your project extends `Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade` and uses the `$this->promoCodeProductRepository` property, use `Shopsys\FrameworkBundle\Model\Order\PromoCode\ProductPromoCodeFiller::getAllowedProductIds()` instead - the property was removed from the parent constructor
- the following results are now memoized for the rest of the request in `Shopsys\FrameworkBundle\Component\Cache\InMemoryCache`: product prices calculated by `Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser::calculatePricesForCustomerUserAndDomainId()`, relevant special prices found by `Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice\SpecialPriceFacade::findRelevantSpecialPrice()`, promo code flags and relations validated by `Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade`, and payments of the current domain returned by `Shopsys\FrameworkBundle\Model\Payment\PaymentFacade` - when your code changes the underlying data and reads the result again within the same request (typically in functional tests), call `Shopsys\FrameworkBundle\Component\Cache\InMemoryCache::reset()` in between (functional tests can use `resetRequestScopedCache()` from `Tests\App\Test\WebTestCase`)
- `Shopsys\FrontendApiBundle\Model\Cart\CartApiFacade::getCartCreateIfNotExists()` now memoizes the resolved cart per customer user and cart UUID in `Shopsys\FrameworkBundle\Component\Cache\InMemoryCache` for the rest of the request - a cart removed from the entity manager (for example by `Shopsys\FrameworkBundle\Model\Cart\CartFacade::deleteCart()`) is resolved again, but when your code replaces the cart of the same customer within one request in another way, call `Shopsys\FrameworkBundle\Component\Cache\InMemoryCache::reset()` afterwards
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/aea484563e6d8e0a862d1e54fd13336c45fed581) to update your project

#### SEO attributes are unified across all entities ([#4845](https://github.com/shopsys/shopsys/pull/4845))

- SEO attributes (title, meta description, H1, and the new meta robots and canonical URL) of all entities are now stored in the `Shopsys\FrameworkBundle\Model\Seo\SeoAttributes` embeddable and transferred in `Shopsys\FrameworkBundle\Model\Seo\SeoAttributesData`, see [SEO attributes](https://docs.shopsys.com/en/latest/model/seo-attributes/)
    - update the code that fills the data objects (data fixtures, imports, tests):
        ```diff
        - $productData->seoTitles[$domainId] = 'Title';
        - $productData->seoMetaDescriptions[$domainId] = 'Description';
        - $productData->seoH1s[$domainId] = 'Heading';
        + $productData->seo[$domainId]->title = 'Title';
        + $productData->seo[$domainId]->metaDescription = 'Description';
        + $productData->seo[$domainId]->h1 = 'Heading';
        ```
        - the same applies to `CategoryData`, `BrandData`, `BlogCategoryData`, `BlogArticleData`, and the new `FlagData::$seo`
        - single-domain entities have a single object: `ArticleData::$seoTitle` → `$articleData->seo->title`, `ReadyCategorySeoMixData::$title/$metaDescription/$h1` → `$readyCategorySeoMixData->seo->title/metaDescription/h1`, and the new `StoreData::$seo`
        - `SeoPageData::$seoTitlesIndexedByDomainId/$seoMetaDescriptionsIndexedByDomainId/$canonicalUrlsIndexedByDomainId` → `$seoPageData->seo[$domainId]->title/metaDescription/canonicalUrl`
    - if you have your own data factory for any of these entities, create the `SeoAttributesData` for every domain with `Shopsys\FrameworkBundle\Model\Seo\SeoAttributesDataFactory` (`create()` for a new entity, `createFromSeoAttributes()` when editing), otherwise the domain entities are not created
    - the SEO attributes are read only through `getSeoAttributes()` (`getSeoAttributes($domainId)->getTitle()` etc.) and edited through `getSeoAttributes()->edit($seoAttributesData)`: the getters and setters on the domain entities (`ProductDomain::getSeoTitle()`, `CategoryDomain::setSeoH1()`, ...) as well as the `getSeoTitle()`, `getSeoMetaDescription()`, and `getSeoH1()` methods of `Product`, `Category`, `Brand`, `BlogArticle`, `BlogCategory`, `Article`, and `SeoPage` (incl. `getCanonicalUrl()`), and `getTitle()` and `getMetaDescription()` of `ReadyCategorySeoMix` were removed
    - update your DQL queries, the Doctrine fields were renamed: `seoTitle`/`seoMetaDescription`/`seoH1` of the domain entities and `Article` → `seo.title`/`seo.metaDescription`/`seo.h1`, `SeoPageDomain.canonicalUrl` → `seo.canonicalUrl`, `ReadyCategorySeoMix.title/metaDescription/h1` → `seo.title/seo.metaDescription/seo.h1`
- `Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListData` now holds the URL addresses of a single domain (`$toDelete`, `$mainFriendlyUrl`, and `$newUrls` as a plain list of slugs), the `$mainFriendlyUrlsByDomainId` field and the `FIELD_DOMAIN` and `FIELD_SLUG` constants were removed
    - the `$urls` field of `ProductData`, `CategoryData`, `BrandData`, `FlagData`, `BlogArticleData`, and `BlogCategoryData` is an array of `UrlListData` indexed by domain ID like any other multidomain data (e.g. `$seo`), `ArticleData`, `StoreData`, and `ReadyCategorySeoMixData` keep a single instance; update your data fixtures, imports, and tests:
        ```diff
        - $categoryData->urls->newUrls[] = [UrlListData::FIELD_DOMAIN => $domainId, UrlListData::FIELD_SLUG => 'slug'];
        - $categoryData->urls->mainFriendlyUrlsByDomainId[$domainId] = $friendlyUrl;
        + $categoryData->urls[$domainId]->newUrls[] = 'slug';
        + $categoryData->urls[$domainId]->mainFriendlyUrl = $friendlyUrl;
        ```
    - `UrlListData` is created by the new `Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListDataFactory` (`create()` for a new entity, `createForAllDomainsIndexedByDomainId()` / `createForDomain()` when editing) and is no longer initialized in the constructor of `ArticleData` and `StoreData`; if you have your own data factory, create the `UrlListData` for every domain with the factory (see e.g. `BrandDataFactory`)
    - `Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade::saveUrlListFormData()` accepts an array of `UrlListData` indexed by domain ID instead of a single object, so the calls for multidomain entities stay the same; for a single-domain entity use the new `saveUrlListFormDataForDomain($routeName, $entityId, $urlListData, $domainId)`
    - `Shopsys\FrameworkBundle\Form\Constraints\UniqueSlugsOnDomains` and its validator were renamed to `UniqueSlugsOnDomain` and `UniqueSlugsOnDomainValidator`, the constraint requires the `domainId` argument and validates the new URLs of that domain
- `Shopsys\FrameworkBundle\Form\UrlListType` renders the URL addresses of a single domain, the `domain_id` option (`int`) is required and the `limit_domains_by_ids` option was removed (multidomain entities render one `UrlListType` per domain via `MultidomainType`), `Shopsys\FrameworkBundle\Form\FriendlyUrlType` no longer accepts `limit_domains_by_ids`
    - the `toDelete` and `mainFriendlyUrl` (formerly `mainFriendlyUrlsByDomainId`) children are choice fields of the domain added only when the entity already has URL addresses, the `newUrls` child is a collection of `FriendlyUrlType`, which is now a plain text field with the slug (`TextType` parent) instead of a compound type with the `domain` and `slug` children
    - the `@ShopsysAdministration/form/type/UrlListType.html.twig` template renders a single domain (the `absoluteUrlsByDomainIdAndSlug`, `mainUrlsSlugsOnDomains`, `routeName`, and `entityId` view variables were replaced by `absoluteUrlsBySlug`, `mainUrlSlug`, and `domainUrl`)
- the `seoTitles`, `seoMetaDescriptions`, `seoH1s`, and `urls` fields of the administration entity forms were replaced by the `seoGroup` field built by `Shopsys\FrameworkBundle\Form\Admin\Seo\SeoGroupType` (in `SeoPageFormType` the `attributes` group with the `*IndexedByDomainId` fields was replaced by `seoGroup` and `openGraphGroup`)
    - the group contains `domains` (`MultidomainType` with one `Shopsys\FrameworkBundle\Form\Admin\Seo\DomainSeoType` per domain, e.g. `seoGroup[domains][1][seo][title]` and `seoGroup[domains][1][urls][newUrls][0]`) or `domain` (a single `DomainSeoType`, e.g. `seoGroup[domain][seo][title]`) when the `domain_id` option is set for a single-domain entity; update your form type extensions and acceptance tests that address these fields
    - to change the SEO fields of all entities at once, write a form type extension for `Shopsys\FrameworkBundle\Form\Admin\Seo\SeoAttributesType`
- the main page title and meta description were removed from _Settings > SEO_, the values are migrated into the SEO page with the slug `/` (_Settings > SEO > SEO pages_)
    - `Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade::SEO_TITLE_MAIN_PAGE`, `SEO_META_DESCRIPTION_MAIN_PAGE`, `getTitleMainPage()`, `getDescriptionMainPage()`, `getDescriptionsMainPageIndexedByDomainIds()`, `setTitleMainPage()`, and `setDescriptionMainPage()` were removed, use `Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageFacade::findByDomainIdAndPageSlug($domainId, '/')` instead
    - the `getSeoMetaDescription()` Twig function was removed, remove it from your templates
- the SEO pages for all static storefront pages (cart, search, customer section, ...) are created by the `Shopsys\FrameworkBundle\Migrations\Version20260909110000` migration with the slugs of the default storefront routes for the `en`, `cs`, and `sk` locales
    - compare the migration with the `config/routes.ts` of your storefront before you run it; if your project uses different slugs or other locales, either skip the migration in your `migrations-lock.yml` (`skip: true`, see [Reordering and skipping migrations](https://docs.shopsys.com/en/latest/introduction/database-migrations/#reordering-and-skipping-migrations)) and write your own migration with your slugs, or run it and fix the slugs afterwards in _Settings > SEO > SEO pages_, otherwise the storefront does not find the SEO pages
    - when a slug is already used on the domain by another SEO page (e.g. one created in the administration), the migration leaves the slug to that page and sets only the meta robots of the SEO page on that domain (the SEO page keeps its original slug there, or is not created there at all when it is new); check your SEO pages for such duplicates after the migration and resolve them in _Settings > SEO > SEO pages_, e.g. delete the duplicate SEO page and fix the slug
- Frontend API: the `seoTitle`, `seoMetaDescription`, and `seoH1` fields of `RegularProduct`, `MainVariant`, `Variant`, `Category`, `Brand`, `BlogCategory`, `BlogArticle`, and `ArticleSite`, and the `title`, `metaDescription`, and `canonicalUrl` fields of `SeoPage` were replaced by the `seo: SeoAttributes!` field (`title`, `metaDescription`, `h1`, `metaRobots`, `canonicalUrl`) of the new `Seo` interface, `Flag` and `Store` got the field too; `SeoPage` has no heading, its `seo.h1` is always `null`
    - `SeoSetting.title` and `SeoSetting.metaDescription` were removed, query `seoPage(pageSlug: "/")` instead
    - `getSeoTitle()`, `getSeoMetaDescription()`, and `getSeoH1()` of `Shopsys\FrontendApiBundle\Model\Resolver\Products\DataMapper\ProductArrayFieldMapper` and `ProductEntityFieldMapper` were replaced by `getSeo()` returning `Shopsys\FrontendApiBundle\Model\Seo\SeoAttributesQueryDto` built by `SeoAttributesQueryDtoFactory` from the Elasticsearch data, resp. the `SeoAttributes` embeddable of the entity; the API serves the attributes exactly as set in the administration and leaves the fallbacks (name of the entity, plain text of the description) to the storefront, so return the embeddable in your own resolvers and let your own GraphQL types with SEO attributes implement the `Seo` interface
- the product, article, and blog article Elasticsearch indexes contain new SEO fields, recreate and export the indexes after the deployment:
    ```sh
    php phing elasticsearch-index-recreate elasticsearch-export
    ```
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/935c80a0efe0db9d2caf1a695bca24caca4eed58) to update your project

#### rewrite easy controllers to crud controller ([#4848](https://github.com/shopsys/shopsys/pull/4848))

- the legacy admin controllers were replaced by CRUD controllers and their route names changed, update your links and redirects (the URL leaf segments changed too, e.g. `/list/` → `/`, `/new/` → `/create`)
    - `admin_parametergroup_*` → `admin_crud_parameter_group_*` (`Shopsys\AdministrationBundle\Controller\ParameterGroupController`)
    - `admin_navigation_*` → `admin_crud_navigation_*` (`Shopsys\AdministrationBundle\Controller\NavigationController`)
    - `admin_notificationbar_*` → `admin_crud_notification_bar_*` (`Shopsys\AdministrationBundle\Controller\NotificationBarController`)
    - `admin_slider_*` → `admin_crud_slider_*` (`Shopsys\AdministrationBundle\Controller\SliderController`), the `/item/` URL segment was dropped
    - `admin_advert_*` → `admin_crud_advert_*` (`Shopsys\AdministrationBundle\Controller\AdvertController`)
    - `admin_salesrepresentative_*` → `admin_crud_sales_representative_*` (`Shopsys\AdministrationBundle\Controller\SalesRepresentativeController`), the `deleteconfirm` route was removed, the confirm message is rendered by the datagrid
    - `admin_closedday_*` → `admin_crud_closed_day_*` (`Shopsys\AdministrationBundle\Controller\ClosedDayController`), `admin_closedday_holidaysimport` → `admin_crud_closed_day_holidays_import`
    - `admin_store_*` → `admin_crud_store_*` (`Shopsys\AdministrationBundle\Controller\StoreController`), `admin_store_setdefault` → `admin_crud_store_set_default`, `admin_store_loadcoordinates` → `admin_crud_store_load_coordinates`
    - `admin_pricelist_*` → `admin_crud_price_list_*` (`Shopsys\AdministrationBundle\Controller\PriceListController`), `admin_pricelist_export|import|loadmetadata` → `admin_crud_price_list_export|import|load_metadata`
- the following classes were removed, if you extended them, move your changes to a `#[CrudControllerExtension]` class (see [Extending existing CRUD Controller](https://docs.shopsys.com/en/latest/administration/crud-controller/getting-started/extending-existing-crud-controller/))
    - `Shopsys\FrameworkBundle\Controller\Admin\{ParameterGroup,Navigation,NotificationBar,Slider,Advert,SalesRepresentative,ClosedDay,Store,PriceList}Controller`
    - `Shopsys\FrameworkBundle\Model\SalesRepresentative\SalesRepresentativeGridFactory`
    - `Shopsys\FrameworkBundle\Model\Store\ClosedDay\Grid\ClosedDayGridFactory`
    - `Shopsys\FrameworkBundle\Model\PriceList\PriceListGridFactory`
- the grid query helpers of the converted sections were removed as they are replaced by the CRUD list query: `Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroupFacade::getOrderedParameterGroupsQueryBuilder()`, `Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository::getOrderedParameterGroupsQueryBuilder()`, `Shopsys\FrameworkBundle\Model\NotificationBar\NotificationBarFacade::getAllByDomainIdQueryBuilderForGrid()`, `Shopsys\FrameworkBundle\Model\NotificationBar\NotificationBarRepository::getAllByDomainIdQueryBuilderForGrid()`, `Shopsys\FrameworkBundle\Model\SalesRepresentative\SalesRepresentativeFacade::getAllQueryBuilder()`, `Shopsys\FrameworkBundle\Model\Store\StoreFacade::getStoresByDomainIdQueryBuilder()`, `Shopsys\FrameworkBundle\Model\PriceList\PriceListFacade::getPriceListGridQueryBuilder()`, `Shopsys\FrameworkBundle\Model\PriceList\PriceListRepository::getPriceListGridQueryBuilder()`
- `Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade::findEmailsOfCustomerUsersUsingSalesRepresentative()` and `Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRepository::findEmailsOfCustomerUsersUsingSalesRepresentative()` were replaced by `getCustomerUserEmailsSummaryIndexedBySalesRepresentativeId(array $salesRepresentativeIds, int $emailsLimit)`
- `Shopsys\FrameworkBundle\Model\Store\StoreFacade::delete()` now throws `Shopsys\FrameworkBundle\Model\Store\Exception\DefaultStoreCannotBeDeletedException` for the default store, handle it where your project calls the method directly
- the following templates were removed as the pages are rendered by the CRUD templates, move your overrides to a `#[CrudControllerExtension]` class or to an override of the `@ShopsysAdministration/crud/` templates
    - `@ShopsysAdministration/content/{parameterGroup,notificationBar,slider,advert,salesRepresentative,store,priceList}/{list,listGrid,new,edit,detail}.html.twig`
    - `@ShopsysAdministration/content/navigation/itemsList.html.twig` and `@ShopsysAdministration/content/navigation/item/{new,edit,detail}.html.twig`
    - `@ShopsysAdministration/content/closedDay/{listGrid,new,edit,detail}.html.twig`
- if you override `@ShopsysAdministration/content/closedDay/list.html.twig`, rebase your override on `@ShopsysAdministration/crud/list.html.twig` and its blocks, as the template now extends it
- the `Shopsys\FrameworkBundle\Model\AdminNavigation\SideMenuBuilder` constants of the converted sections were removed (`LIST_/NEW_/EDIT_PARAMETER_GROUP`, `LIST_/NEW_/EDIT_NAVIGATION`, `LIST_/NEW_/EDIT_NOTIFICATION_BAR`, `LIST_/NEW_/EDIT_BANNER`, `LIST_/NEW_/EDIT_ADVERT`, `LIST_/NEW_/EDIT_SALES_REPRESENTATIVE`, `LIST_/NEW_/EDIT_CLOSED_DAY`, `LIST_/NEW_/EDIT_STORE`, `LIST_/NEW_/EDIT_PRICE_LIST`), the menu items are now registered by the CRUD controllers under their list route name (e.g. `admin_crud_store_list`)
- `Shopsys\AdministrationBundle\Component\Datagrid\Datagrid::__construct()` has new required parameters `Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade $administratorGridFacade` and `Symfony\Bundle\SecurityBundle\Security $security`, update your code if you create the datagrid directly instead of through `Shopsys\AdministrationBundle\Component\Datagrid\DatagridFactory`
- `Shopsys\FrameworkBundle\Model\SalesRepresentative\SalesRepresentative::getPresentationalLabel()` was removed, use `toHumanReadable()` instead
- `Shopsys\FrameworkBundle\Model\Slider\SliderItem::setDatetimeVisibleFrom()` and `Shopsys\FrameworkBundle\Model\Slider\SliderItem::setDatetimeVisibleTo()` were removed, set the values through `Shopsys\FrameworkBundle\Model\Slider\SliderItem::edit()`
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/c533a1f36ca78b736ba340890447104183789853) to update your project

#### stop reporting 410 GoneHttpException and 403 AccessDeniedHttpException to Sentry ([#4858](https://github.com/shopsys/shopsys/pull/4858))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/ac0ae989d42624efa771f906a40ac719d97723b2) to update your project

#### Login rate limit is no longer exhausted by successful logins ([#4860](https://github.com/shopsys/shopsys/pull/4860))

- if you have extended `LoginMutation`, incorporate the changes to your extension:
    - `checkLoginRateLimitAndGetCurrentRequest()` has a new parameter `$username` and calls `peek()` instead of `consume()` on the rate limiter
    - the rate limiter is consumed only when the login fails, right before `InvalidCredentialsUserError` is thrown
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/4c445f2cdc3f001baf8a1da9d3bf751ac3689793) to update your project

#### Electronic gift vouchers have no delivery options on the product detail ([#4869](https://github.com/shopsys/shopsys/pull/4869))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/878d0b8b6cfd395ef9a5dfb92924059fe8b89755) to update your project

#### Add AI security audit of merge request diffs to GitLab CI ([#4872](https://github.com/shopsys/shopsys/pull/4872))

- if you want the new manual `ai:security-review` pipeline job, set the `CLAUDE_CODE_OAUTH_TOKEN` CI/CD variable with a Claude Code OAuth token (generate it with `claude setup-token`) — the job is silently skipped while it or `API_TOKEN` is missing
- mark `CLAUDE_CODE_OAUTH_TOKEN` and `API_TOKEN` as **masked** CI/CD variables — the job runs the merge request's own checked-out pipeline and wrapper script while holding them, so masking keeps a tampered script from printing them into the job log
- the job runs on the shared Claude account with Sonnet and a low turn budget on purpose, so it is started by hand from the merge request pipeline rather than on every push
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/ca15a869c0d29c0f2e64d357a22bd4ba0b9b2f70) to update your project

#### Disable Sentry cron monitoring in review environments ([#4875](https://github.com/shopsys/shopsys/pull/4875))

- cron check-ins can now be switched off with `SENTRY_CRON_MONITORING_ENABLED=0` while error reporting stays on — set it in your review and CI environments so they stop flooding the shared Sentry monitors
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/a2af21bdd6d0dfabbc72ded3380a4c3319fcb61e) to update your project

#### Registration via social network no longer fails when the profile has no name ([#4877](https://github.com/shopsys/shopsys/pull/4877))

- `SocialNetworkFacade::validateDataFromSocialNetwork()` was moved to `RegistrationDataFactory::validateDataFromSocialNetwork()`

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/a8914239295141145e708abf358de18dc1396286) to update your project

#### Maintenance page is now enabled during deploys with database migrations ([#4880](https://github.com/shopsys/shopsys/pull/4880))

- if you have overridden the `db-migrations-count-with-maintenance` target in your `build.xml`, remove the override so that the fixed framework target is used, or apply the same change to it:
    ```diff
    -        <exec executable="${path.php.executable}" checkreturn="true" passthru="true" returnProperty="migrationCounts">
    +        <exec executable="${path.php.executable}" checkreturn="true" logoutput="true" outputProperty="migrationCounts">
    ...
    -            <equals arg1="${migrationCounts}" arg2="0"/>
    +            <equals arg1="${migrationCounts}" arg2="0" trim="true"/>
    ```

#### Fix logging of edits on entities with a plain Loggable attribute ([#4882](https://github.com/shopsys/shopsys/pull/4882))

- `#[Loggable]` and `#[LoggableChild]` without arguments (or with a named `strategy:` argument) previously logged no property changes; they now use the default `Loggable::STRATEGY_INCLUDE_ALL` strategy, so review such entities in your project and mark sensitive properties with `#[ExcludeLog]`
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/0fec02bbf2b507d0c3ad909ada051e2d9385b295) to update your project

#### Whitelist PageSpeed.ONE IP for HTTP auth on deployed environments ([#4884](https://github.com/shopsys/shopsys/pull/4884))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/e44b56091a5cfb503f51a05d35dd0e77822e5063) to update your project

#### demo data: variants of Television Philips now have clearly distinguishable names ([#4886](https://github.com/shopsys/shopsys/pull/4886))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/abefb18fe88d5618dfef880d7cb13c403ad1b5ea) to update your project

#### Improve storefront loading and shared component dependencies ([#4888](https://github.com/shopsys/shopsys/pull/4888))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/ccf2201cbae65cd875f1e031349370123147804a) to update your project

#### Administration JS translations are available before Stimulus controllers connect ([#4889](https://github.com/shopsys/shopsys/pull/4889))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/4b17f512ea17d524ad0d03b6d96b8b28f16d9814) to update your project

#### Price change messages now also cover changes of the price without VAT ([#4892](https://github.com/shopsys/shopsys/pull/4892))

- the watched prices of cart items, transport and payment are now `Shopsys\FrameworkBundle\Model\Pricing\PriceInterface` instead of `Shopsys\FrameworkBundle\Component\Money\Money` with VAT, update your code accordingly:
    - `Shopsys\FrameworkBundle\Model\Cart\Item\CartItem` constructor and `Shopsys\FrameworkBundle\Model\Cart\Item\CartItemFactory::create()` now expect `?PriceInterface $watchedPrice` instead of `?Money $watchedPrice`
    - `Shopsys\FrameworkBundle\Model\Cart\Item\CartItem::getWatchedPrice()` now returns `?Shopsys\FrameworkBundle\Model\Pricing\Price` and `setWatchedPrice()` accepts `?PriceInterface`, use `isWatchedPriceChanged()` to compare it with the current price
    - `Shopsys\FrameworkBundle\Model\Cart\Cart::getTransportWatchedPrice()` and `getPaymentWatchedPrice()` now return `?Shopsys\FrameworkBundle\Model\Pricing\Price` and `setTransportWatchedPrice()` and `setPaymentWatchedPrice()` accept `?PriceInterface`, use `isTransportWatchedPriceChanged()` / `isPaymentWatchedPriceChanged()` to compare them with the current price and `hasTransportWatchedPrice()` / `hasPaymentWatchedPrice()` instead of comparing them with `null`
    - `Shopsys\FrameworkBundle\Model\Cart\Payment\CartPaymentData::$watchedPrice` and `Shopsys\FrameworkBundle\Model\Cart\Transport\CartTransportData::$watchedPrice` are now `PriceInterface`
    - `Shopsys\FrameworkBundle\Model\Cart\Payment\CartPaymentFacade::setPaymentWatchedPrice()` and `Shopsys\FrameworkBundle\Model\Cart\Transport\CartTransportFacade::setTransportWatchedPrice()` now accept `PriceInterface`
    - `Shopsys\FrameworkBundle\Model\Cart\Payment\CartPaymentDataFactory::getPaymentWatchedPriceWithVat()` was renamed to `getPaymentWatchedPrice()` and `Shopsys\FrameworkBundle\Model\Cart\Transport\CartTransportDataFactory::getTransportWatchedPriceWithVat()` was renamed to `getTransportWatchedPrice()`, both now return `PriceInterface`
    - if you create gift cart items yourself, pass `Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlanSettingFacade::calculateBaseGiftPrice()` as the watched price instead of `getInputGiftPrice()`
- protected properties `Shopsys\FrameworkBundle\Model\Cart\Item\CartItem::$watchedPrice`, `Shopsys\FrameworkBundle\Model\Cart\Cart::$transportWatchedPrice` and `Shopsys\FrameworkBundle\Model\Cart\Cart::$paymentWatchedPrice` were renamed to `$watchedPriceWithVat`, `$transportWatchedPriceWithVat` and `$paymentWatchedPriceWithVat`, update your extended entities and DQL queries
    - the database columns `cart_items.watched_price`, `carts.transport_watched_price` and `carts.payment_watched_price` were renamed to `*_with_vat`, update your custom SQL queries
- `Shopsys\FrontendApiBundle\Model\Payment\PaymentValidationFacade::checkPaymentPrice()` and `Shopsys\FrontendApiBundle\Model\Transport\TransportValidationFacade::checkTransportPriceAndWeightLimit()` now return the calculated `PriceInterface` instead of `void`, update your overridden methods
- a missing watched price is no longer a price change, if you customized the price checks in `Shopsys\FrameworkBundle\Model\Cart\Watcher\CartWatcher` or `Shopsys\FrontendApiBundle\Model\Cart\TransportAndPaymentWatcherFacade`, use the `is*WatchedPriceChanged()` methods and store the current price without reporting it when no watched price is stored
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/be6bee0291b936701aab577bf26ca7655f252955) to update your project

#### CRUD handler and hook interfaces receive the entity as `Presentable` ([#4893](https://github.com/shopsys/shopsys/pull/4893))

- `createDataFromEntity()` and `edit()` of `EditHandlerInterface`, the entity parameter of `beforeEdit()`, `afterEdit()`, `onEditError()`, `afterCreate()`, `beforeDelete()`, `afterDelete()` and `onDeleteError()` hook methods and the entity parameter of `configureForm()` of `AbstractCrudController` and `AbstractCrudControllerExtension` are typed as `Shopsys\FrameworkBundle\Component\Utils\Presentable` instead of `object`, consistently with `getById()`, `create()` and `delete()`
    - your handlers, controllers and extensions declaring `object $entity` keep working, but change the type to `Presentable` to match (data objects stay `object`)
    - if you call these methods yourself, pass an entity implementing `Presentable`
- `getById()` and `create()` of `TransportGroupCrudHandler`, `BlogArticleAuthorCrudHandler` and `AdditionalServiceCrudHandler` return the concrete entity instead of `Presentable`
    - if you extend one of these handlers and override the method, change its return type to the entity as well
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/76fc31dbc35a99e6fc0ef443054c885888027fd0) to update your project

#### Docker build checks CI of the split repositories and project-base runs Cypress tests in parallel groups ([#4896](https://github.com/shopsys/shopsys/pull/4896))

- the `Docker build` GitHub Actions workflow of your project now runs Cypress tests in parallel groups (the same groups as in `storefront/cypress/cypress.config.ts`) instead of one sequential run, which cuts the build time roughly to a third
- the `storefront-cypress` service in `docker/conf/docker-compose.yml.dist` (and the macOS variant) got a longer healthcheck `start_period`, so that `docker compose up --wait storefront-cypress` no longer gives up while the production build of the storefront is still running
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/6f4c386ce63cd17a9cc2bf72bd94863357aa7a26) to update your project

#### AddOrderItemsToCart and payment price in order context now require proof of order ownership ([#4874](https://github.com/shopsys/shopsys/pull/4874))

- `orderUuid` is not a secret and no longer works as the sole authorization for `AddOrderItemsToCart` and for the `price` field of payments resolved in an order context (e.g. `orderPayments { availablePayments { price } }`)
- pass the order's `urlHash` in the new `orderUrlHash` field of `AddOrderItemsToCartInput`, or call the mutation as the logged-in owner of the order, otherwise it now fails with `order-not-found` (or with a request error when no proof is provided at all)
- if you use the `price` field of payments in an order context, make sure the `orderUrlHash` variable is passed alongside `orderUuid` in the same request, or that the request is made by the logged-in owner of the order
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/16c23dd955afc286702ad49ff2c6c79edbbeb9f4) to update your project

#### Clarify warehouses and stores in demo data and administration ([#4908](https://github.com/shopsys/shopsys/pull/4908))

- template `@ShopsysAdministration/form/content/ProductStockDataCollection.html.twig` with the block `_product_form_stocksGroup_productStockData_row` was removed
    - if you have overridden the block, move your changes to the blocks `product_stocks_widget` and `product_stock_form_row` in `@ShopsysAdministration/form/content/ProductStocksType.html.twig`
- a newly created stock is now placed at the end of the stock list (`Shopsys\FrameworkBundle\Model\Stock\Stock::GEDMO_SORTABLE_LAST_POSITION` is now `-1`), existing positions are not changed
    - if your integration or import expects a new stock to appear at the top of the list, adjust it
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/853085d1979cbb38894eed5068cb79dbafe69661) to update your project

#### Datagrid fields can combine properties, be restricted by role and set a column class ([#4909](https://github.com/shopsys/shopsys/pull/4909))

- `FieldDescriptor::getSelectProperty()` was replaced by `getSelectProperties()` returning an array, as the `property` option now accepts an array of properties
    - if you implement your own `AdapterInterface`, select all the returned properties
- values computed by the adapter (fields with `transform` or with multiple properties) are stored under the field name and the grid reads them from there
    - fields combining `property` with `transform` now display the transformed value instead of the raw property value
- `DatagridDataSource::addQueryOrder()` accepts a comma-separated list of properties and orders by all of them
- `Datagrid` constructor requires `AccessCheckerInterface` as the third argument, create datagrids via `DatagridFactory` as before
- `Column::setOrderSourceColumnName()` was added to allow ordering a grid column by a different data source column than the displayed one
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/bbe4504c83312c07fa263b6102d8a835e34e8bce) to update your project

#### imageResizer: image metadata stripping is now explicit and configurable ([#4916](https://github.com/shopsys/shopsys/pull/4916))

- if your project needs to keep image metadata (EXIF, GPS, …), set `$stripImageMetadata = false` in `app/web/imageResizer.php`
- VSH CDN image URLs now use the `v2` endpoint, so all existing CDN image URLs change and every image is processed again on first request after deploy
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/b7be70519394f8eb13471a5f66b672663f841aba) to update your project

#### Display product name prefix and suffix in admin variants and product picker ([#4870](https://github.com/shopsys/shopsys/pull/4870))

- the administration now displays the full product name (prefix, name and suffix) everywhere, so if you display a product name anywhere in your administration, use the full name too:
    - for an entity, use `Shopsys\FrameworkBundle\Model\Product\Product::getFullName()` or the `productDisplayName` Twig filter
    - in DQL queries (for example datagrids), select the full name with `Shopsys\FrameworkBundle\Model\Product\ProductFullNameDqlHelper::getDqlExpression()`

#### product parameter values are filtered by locale directly in the query ([#4729](https://github.com/shopsys/shopsys/pull/4729))

- `Shopsys\FrameworkBundle\Twig\ProductExtension::getProductParameterValues()` and the `getProductParameterValues()` Twig function were removed
    - if you use the function in your templates, call `Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade::getProductParameterValues()` from your own Twig extension instead
- `Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade::getProductParameterValues()` no longer filters out values of other locales and parameters without a name, this is now done in `Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository::getProductParameterValuesByProductSortedByOrderingPriorityAndNameQueryBuilder()`
    - if you override the query builder method, add the same conditions to keep the behavior
    - if you override the facade method, add `$locale` to the in-memory cache key, otherwise values for different locales of the same product collide
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/43d0de1a84cba7717f8f2ec94db07538346bf7d4) to update your project

#### The installation script detects the operating system ([#4918](https://github.com/shopsys/shopsys/pull/4918))

- `scripts/configure.sh` detects the operating system (Linux, macOS, or Windows with WSL) instead of asking for it, and only asks for a confirmation of the detected value
- the new `--os=linux`, `--os=wsl`, and `--os=mac` options select the operating system without any question — use them where there is no interactive terminal, the `echo 1 | ./scripts/configure.sh` workaround is no longer needed
    - the script also uses the detected value automatically when it runs without an interactive terminal, so existing CI pipelines keep working
- unknown options passed to `scripts/configure.sh` are reported as an error instead of being silently ignored
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/d65bb5ad3a4752c582c5572c36e97eb646132d3c) to update your project

#### Prevent deleting required advert images without replacement ([#4921](https://github.com/shopsys/shopsys/pull/4921))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/441dc75b9d64233b865d3513e976291cd48e327a) to update your project

#### Fix block insertion at the end of email templates ([#4919](https://github.com/shopsys/shopsys/pull/4919))

- the migration `Shopsys\FrameworkBundle\Migrations\Version20261005120000` replaces the bodies of the `watchdog_mail` and `gift_voucher` mail templates on all domains with the new default content (a single GrapesJS text block), overwriting any customizations — if you have customized these templates, skip the migration and re-save the templates in the administration instead

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/516e594cf96b2cbded3c9fbdf5abbce4b4c52f76) to update your project

#### Open Graph data now fall back to organization details and use a dedicated image preset ([#4927](https://github.com/shopsys/shopsys/pull/4927))

- the storefront now requests `og:image` from the image resizer with the `og` preset (`?preset=og`), so apply the changes of `app/web/imageResizer.php` together with the storefront changes, otherwise the sharing image is not shrunk into 1200×630 px and may be served as WebP
    - if your project sets `CDN_RESIZE_DISABLE` to `true`, the original image is served instead, so make sure the images used for sharing (SEO page Open Graph images, product, category, article, brand and store images, organization logo) are JPG or PNG of a reasonable size
- `og:site_name` is now taken from the organization name, fill in the Company name in the Organization section of the administration SEO settings (Settings > SEO > SEO and Open Graph) for every domain, otherwise the tag is not rendered
- `Shopsys\Cli\Config\Section\MetatagSiteNameSection` and `Shopsys\Cli\Worker\Storefront\MetatagSiteNameWorker` were removed - remove the `metatag_site_name` key from your CLI project config files
    - `Shopsys\Cli\Config\CoreProjectConfig::getConfigSection()` was removed as well, as there is no project config section left to read
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/a8adaf50be74e997e63778d40d63fa1db5cddf9a) to update your project

#### admin product list price follows the default administration currency ([#4738](https://github.com/shopsys/shopsys/pull/4738))

- the admin product list lists the price of the default pricing group of the first domain whose default currency is the default administration currency (see `Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade::findDefaultPricingGroupByCurrency()`), when no domain uses that currency, no price is listed
- `Shopsys\FrameworkBundle\Model\Product\Listing\ProductListAdminRepository::getProductListQueryBuilder()` now accepts `null` as `$pricingGroupId`, update the signature if you override the method
    ```diff
    - public function getProductListQueryBuilder(int $pricingGroupId): QueryBuilder
    + public function getProductListQueryBuilder(?int $pricingGroupId): QueryBuilder
    ```
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/6479449d66d281592193a3f38d9e2454bab514f1) to update your project

#### FE API: Category and BlogCategory breadcrumbs are batch loaded ([#4934](https://github.com/shopsys/shopsys/pull/4934))

- `Shopsys\FrontendApiBundle\Model\Resolver\Breadcrumb\BreadcrumbQuery::categoryBreadcrumbQuery()` was removed, if your GraphQL types still use `resolve: '@=query("categoryBreadcrumbQuery", value)'` on `Category.breadcrumb`, remove the `resolve` expression
- if your GraphQL types still use `resolve: '@=query("breadcrumbQuery", value.getId(), "front_blogcategory_detail")'` on `BlogCategory.breadcrumb`, remove the `resolve` expression
- `Category.breadcrumb` and `BlogCategory.breadcrumb` are no longer resolved through `Shopsys\FrameworkBundle\Model\Category\CategoryBreadcrumbGenerator` and `Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryBreadcrumbGenerator`, if your project customizes these generators for the Frontend API, move the customization to `Shopsys\FrontendApiBundle\Model\Category\CategoryBreadcrumbLinksFactory` or the breadcrumb batch loaders (`Shopsys\FrontendApiBundle\Model\Category\CategoryBreadcrumbBatchLoader`, `Shopsys\FrontendApiBundle\Model\Blog\Category\BlogCategoryBreadcrumbBatchLoader`)
- `Shopsys\FrameworkBundle\Model\Product\ProductListFriendlyUrlDataProvider::ROUTE_NAME` and `Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryDetailFriendlyUrlDataProvider::ROUTE_NAME` are `public const string` now, if you redeclare these constants in your project, change them to `public const string` as well
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/62a6f67430196a0fd8cd18bc989764b706e8f0ee) to update your project

#### Cart stores only the date of its last activity ([#4932](https://github.com/shopsys/shopsys/pull/4932))

- `Shopsys\FrameworkBundle\Model\Cart\Cart::$modifiedAt` was renamed to `$lastActivityAt` and its type was changed from `datetime_immutable` to `date_immutable` (database column `carts.modified_at` → `carts.last_activity_at`), so the cart entity is written at most once a day instead of on every request
    - `Cart::setModifiedNow()` was replaced by `Cart::updateLastActivityDate(): bool`, which moves the date to today only when the day changed and returns whether it did
    - `Cart::setModifiedAt()` was removed and a new `Cart::getLastActivityAt()` getter was added; to test time-dependent cart behavior, move the clock with `Symfony\Component\Clock\Test\ClockSensitiveTrait::mockTime()` instead
    - if your project relies on the time of the last cart modification (e.g. for abandoned cart reminders), store it in your own column
- every read of the cart (`CartFacade::findCartByCustomerUserIdentifier()`, `CartFacade::findCartByCartIdentifier()`) now counts as activity, so carts the customer keeps opening are not deleted by `CartFacade::deleteOldCarts()`
- `CartRepository::deleteOldCartsForUnregisteredCustomerUsers()` and `CartRepository::deleteOldCartsForRegisteredCustomerUsers()` compare the `last_activity_at` date instead of the `modified_at` timestamp
- the migration `Version20261008120000` renames the column and converts the existing timestamps to dates
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/bb10e1d0a230bd992b00c23ddf6453ba799ce5bd) to update your project

#### Delete watchdogs of the removed company user instead of the owner's ([#4930](https://github.com/shopsys/shopsys/pull/4930))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/63b6e7dc9a4eed70be73634ed42fb7380420d4e2) to update your project

#### Delete watchdogs only on the domain of the deleted customer user ([#4931](https://github.com/shopsys/shopsys/pull/4931))

- `WatchdogFacade::deleteByEmail()` was replaced by `WatchdogFacade::deleteByEmailAndDomainId()`
- `WatchdogRepository::deleteByEmail()` was replaced by `WatchdogRepository::deleteByEmailAndDomainId()`
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/3b1bef8d2ee9c6cbdd0e58b4486a2a89f5969325) to update your project

#### Take product data in Frontend API always from Elasticsearch ([#4933](https://github.com/shopsys/shopsys/pull/4933))

- [features moved](#movement-of-features-from-project-base-to-packages) from project-base to the frontend-api package:
    - `App\FrontendApi\Resolver\Products\ProductResolverMap` → `Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductResolverMap`
    - `App\FrontendApi\Resolver\Products\DataMapper\ProductArrayFieldMapper` (`getCatalogNumber()`, `getPartNumber()`, `getCategoriesPromise()`, `getFlagsPromise()`, `getBrandPromise()`) → `Shopsys\FrontendApiBundle\Model\Resolver\Products\DataMapper\ProductArrayFieldMapper`
    - `App\FrontendApi\Model\Flag\FlagsBatchLoader` → `Shopsys\FrontendApiBundle\Model\Flag\FlagsBatchLoader`
    - `catalogNumber` and `partNumber` fields of the `Product` type → `ProductDecorator.types.yaml`
- [features moved](#movement-of-features-from-project-base-to-packages) from project-base to the framework package:
    - `App\FrontendApi\Model\Flag\FlagFacade::getFlagsByIds()` and `App\FrontendApi\Model\Flag\FlagRepository::getFlagsByIds()` → `Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade::getVisibleFlagsByIds()` and `Shopsys\FrameworkBundle\Model\Product\Flag\FlagRepository::getVisibleFlagsByIds()`
- `Shopsys\FrontendApiBundle\Model\Resolver\Products\DataMapper\ProductEntityFieldMapper` was removed and product types (`Product`, `RegularProduct`, `Variant`, `MainVariant`) are always resolved from Elasticsearch data by `Shopsys\FrontendApiBundle\Model\Resolver\Products\DataMapper\ProductArrayFieldMapper`
    - move your own methods of `App\FrontendApi\Resolver\Products\DataMapper\ProductEntityFieldMapper` to your extension of `ProductArrayFieldMapper` and read the values from the Elasticsearch array (extend the product export when the data are not there)
    - resolvers of your own fields on product types always receive the Elasticsearch array, never the product entity
    - your own fields of a product type that return product entities (e.g. a field resolved as `value.getProduct()`) have to be resolved by the new `Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductEntityQuery` (`productByEntityQuery`, `productsByEntitiesQuery`)
- a field of a product type without its own method in `ProductArrayFieldMapper` is resolved from the Elasticsearch key with the name of the field or its snake_case variant, so these methods of `Shopsys\FrontendApiBundle\Model\Resolver\Products\DataMapper\ProductArrayFieldMapper` were removed: `getShortDescription()`, `getStockQuantity()`, `isAllowedNegativeStock()`, `getDescription()`, `getOrderingPriority()`, `getProductType()`, `getNamePrefix()`, `getNameSuffix()`, `getAvailableStoresCount()`, `getProductVideos()`, `getVatPercent()`
    - if you override or call them, read the value from the Elasticsearch array directly
- `Shopsys\FrontendApiBundle\Model\Resolver\Products\DataMapper\ProductArrayFieldMapper::getCategories()`, `getFlags()` and `getBrand()` were removed, as categories, flags and brand of a product are loaded by `getCategoriesPromise()`, `getFlagsPromise()` and `getBrandPromise()` now
- `Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade::getByIds()` and `Shopsys\FrameworkBundle\Model\Product\Flag\FlagRepository::getByIds()` were removed, use `getVisibleFlagsByIds()`, which returns only visible flags translated to the given locale
- the first parameter of these methods accepts only the Elasticsearch array of a product data instead of `Product|array`:
    - `Shopsys\FrontendApiBundle\Model\Resolver\Price\ProductPriceQuery::priceByProductQuery()`, `giftPriceByProductQuery()` and `isProductUponInquiry()`
    - `Shopsys\FrontendApiBundle\Model\Resolver\Image\ProductImagesQuery::imagesByProductPromiseQuery()` and `mainImageByProductPromiseQuery()`
    - `Shopsys\FrontendApiBundle\Model\Resolver\Image\ProductImagesCountQuery::imagesCountByProductPromiseQuery()`
    - `Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductFilesQuery::filesByProductPromiseQuery()`
    - `Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductGiftsQuery::giftsByProductPromiseQuery()`
    - `Shopsys\FrontendApiBundle\Model\Resolver\ProductReview\ProductReviewsQuery::productReviewsByProductQuery()` and `getProductIdCarryingReviews()`
- these methods were removed without a replacement as they were used only for resolving products from entities:
    - `Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductFilesQuery::resolveByEntityIdPromise()`
    - `Shopsys\FrontendApiBundle\Model\FriendlyUrl\FriendlyUrlSlugBatchLoader::loadProductSlugs()`
    - `Shopsys\FrontendApiBundle\Model\AdditionalService\AdditionalServicesBatchLoader::loadByProductIds()` and `getProductsWithAdditionalServicesIndexedById()`
    - `Shopsys\FrontendApiBundle\Model\ProductReview\ProductReviewApiFacade::getReviewSummaryForProduct()`
    - `Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade::getProductBasicPrice()`
    - `Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryFacade::getOfferedAccessories()`, `Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryRepository::getOfferedAccessories()` and `getAllOfferedAccessoriesByProductQueryBuilder()`
    - `Shopsys\ProductFeed\ZboziBundle\FrontendApi\Resolver\ZboziCategoryQuery::zboziCategoryByProductQuery()`
- class `Shopsys\FrontendApiBundle\Model\Resolver\Price\Exception\ProductPriceMissingUserError` (error code `product-price-missing`) was removed
- products not visible for the current customer are no longer available in the API: they are resolved as `null` (e.g. `OrderItem.product`, `ComplaintItem.product`), omitted from lists, and instead of being reported in `noLongerListableCartItems` they set `someProductWasRemovedFromEshop`
    - if your own API clients rely on hidden products in these fields, handle `null` and the `someProductWasRemovedFromEshop` flag instead
- the `notAddedProducts` field of `CartMultipleAddedProductModificationsResult` was replaced by `notAddedProductNames` with the names of all order items that `AddOrderItemsToCart` could not add to the cart, including the items whose product is no longer available
    - `Shopsys\FrontendApiBundle\Model\Cart\CartWithModificationsResult::addProductsNotAddedByMultipleAddition()` was replaced by `addProductNamesNotAddedByMultipleAddition()`
    - if your own API clients read `notAddedProducts`, read `notAddedProductNames` instead
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/401a317ced73d2eaab3be44961cdbb9f9a5b4d4a) to update your project
- see also project-base diff of [#4992](https://github.com/shopsys/shopsys/pull/4992) with additional cleanup

#### FE API: product filter options are batch loaded ([#4938](https://github.com/shopsys/shopsys/pull/4938))

- if your project relies on the database based flag choices, brand choices, or price range of the filter config of the brand, search, or all-products listing, adjust it to the Elasticsearch based implementation
- `Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfigFactory`: `createForAll()`, `createForSearch()`, `createForBrand()`, `createForCategory()`, and `createForFlag()` were removed, move your changes of the all-products, search, and flag config to `createWithFlagAndBrandChoices()`, of the brand config to `createWithFlagChoices()`, and of the category config to `createWithParameterFlagAndBrandChoices()`
- `Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterElasticFacade`: `getProductFilterDataForAll()`, `getProductFilterDataForSearch()`, `getProductFilterDataInBrand()`, `getProductFilterDataInCategory()`, and `getProductFilterDataInFlag()` were removed, use the corresponding `getAggregationQueryForAll()`, `getAggregationQueryForSearch()`, `getAggregationQueryInBrand()`, `getAggregationQueryInCategory()`, or `getAggregationQueryInFlag()` with `getProductFilterConfigIdsData()`
- `Shopsys\FrameworkBundle\Model\Product\Filter\FlagFilterChoiceRepository`, `Shopsys\FrameworkBundle\Model\Product\Filter\BrandFilterChoiceRepository`, and `Shopsys\FrameworkBundle\Model\Product\Filter\PriceRangeRepository` were removed
- `Shopsys\FrameworkBundle\Model\Product\ProductRepository`: `getListableInCategoryQueryBuilder()`, `getListableForBrandQueryBuilder()`, `getSellableInCategoryQueryBuilder()`, `getListableBySearchTextQueryBuilder()`, and `filterByBrand()` were removed
- `Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchRepository`: `filterBySearchText()`, `getFoundProductIds()`, `getProductIdsBySearchText()`, and the protected `createQuery()` and `extractIds()` were removed
- `Shopsys\FrameworkBundle\Model\Product\Search\ProductFilterCountDataElasticsearchRepository`: `getProductFilterCountDataInSearch()` and `getProductFilterCountDataInCategory()` were removed, the protected `calculateFlagsPlusNumbers()`, `calculateBrandsPlusNumbers()`, `replaceParametersPlusNumbers()`, and `calculateParameterPlusNumbers()` were replaced by the public `createSearchQueriesIndexedByQueryName()` and `createCountData()`, move your changes of the count queries there
- `Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainElasticFacade` was removed, use `Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptionsFacade` instead of its `getProductFilterCountDataInCategory()`, `getProductFilterCountDataForBrand()`, `getProductFilterCountDataForSearch()`, `getProductFilterCountDataForAll()`, and `getProductFilterCountDataForFlag()`, and `Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchRepository::getAggregationQueryForCategoryIds()` with `extractCategoryIdsAggregation()` instead of its `getCategoryIdsForFilterData()`
- `Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptionsFactory`: `createProductFilterOptionsForAll()`, `createProductFilterOptionsForCategory()`, `createProductFilterOptionsForBrand()`, and `createProductFilterOptionsForFlag()` accept the loaded `ProductFilterCountData` instead of loading it, no longer accept the search text, brand, or flag, and no longer check the product filter counts module, the module check and the loading moved to `Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptionsFacade`
- `Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterFacade`: `getProductFilterConfigForSearch()`, `getProductFilterConfigForBrand()`, and `getProductFilterConfigForCategory()` were removed, use `getProductFilterConfigPromiseForSearch()`, `getProductFilterConfigPromiseForBrand()`, or `getProductFilterConfigPromiseForCategory()`, `getValidatedProductFilterDataForBrand()` and `getValidatedProductFilterDataForCategory()` were removed, use `getValidatedProductFilterDataPromiseForBrand()` or `getValidatedProductFilterDataPromiseForCategory()`
- `Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnection::getProductFilterOptions()` returns `ProductFilterOptions|Promise`, product filter options closures of `Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnectionFactory` return a `Promise` resolving to `ProductFilterOptions` obtained from `Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptionsFacade`
- `Shopsys\FrontendApiBundle\Component\Elasticsearch\MultipleSearchQuery` and `Shopsys\FrontendApiBundle\Component\Elasticsearch\MultipleSearchQueryFactory` were removed, use `Shopsys\FrameworkBundle\Component\Elasticsearch\MultipleSearchRepository`
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/8b0250e5298a21da2b210e6577d08a1a56a0b31b) to update your project

#### Log changes of transports, payments, customers and administrators ([#4939](https://github.com/shopsys/shopsys/pull/4939))

- `Shopsys\FrameworkBundle\Component\EntityLog\Attribute\LoggableEntityConfig` now holds the name of the parent property instead of the name of the parent getter (the parent is read via Doctrine metadata, so a `LoggableChild` no longer needs a getter for its parent)
    - constructor parameter `$parentEntityFunctionName` was renamed to `$parentPropertyName`
    - method `getParentEntityFunctionName()` was renamed to `getParentPropertyName()`
    - method `setParentEntityName()` now requires a second `string $parentPropertyName` parameter
- `Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\Formatter\ScalarDataTypeFormatter`, `MoneyDataTypeFormatter`, `DateTimeDataTypeFormatter` and `BooleanDataTypeFormatter` no longer extend `AbstractChangeSetFormatter` but implement `Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\Formatter\DataTypeFormatterInterface`
    - their `formatChanges()` method was removed, use `formatValue()` instead
    - if you format values of your own data type in the entity log, implement `DataTypeFormatterInterface` (the service is autoconfigured and selected by `Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\Formatter\DataTypeFormatterRegistry`)
- method `Shopsys\FrameworkBundle\Model\Administrator\Administrator::addRoles()` was removed, use `setRoles()` instead (it replaces the whole collection of roles)
    - protected method `Shopsys\FrameworkBundle\Model\Administrator\Role\AdministratorRoleFacade::removeAllByAdministrator()` was removed, roles are now updated in place by `refreshAdministratorRoles()`
- methods `Shopsys\FrameworkBundle\Model\Transport\TransportFacade::deleteAllPricesByTransport()` and `Shopsys\FrameworkBundle\Model\Transport\TransportRepository::deleteAllPricesByTransport()` were removed
    - `Shopsys\FrameworkBundle\Model\Transport\Transport::setPrices()` now updates the collection in place and the prices left out are deleted as orphans, so pass the existing `TransportPrice` entities you want to keep instead of creating new ones
- the entity log now respects `#[Log]` and `#[ExcludeLog]` also on collections, so on your entities with `#[Loggable(Loggable::STRATEGY_EXCLUDE_ALL)]` mark the collections you want to keep logging with `#[Log]`
- the `#[Loggable]` and `#[LoggableChild]` attributes are not inherited, so if you extended any of the following entities in your project (beyond the ones in project-base), add the attribute to your extended class as well
    - `#[Loggable]` on `Customer` and `Complaint`
    - `#[LoggableChild]` on `PaymentDomain`, `PaymentPrice`, `PaymentTranslation`, `TransportDomain`, `TransportPrice`, `ComplaintItem`, `WithdrawalRequest` and `Image`
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/a83682e68b24430441abc7e7e37981468dfe39a1) to update your project

#### nginx: robots.txt is now available through the CDN ([#4940](https://github.com/shopsys/shopsys/pull/4940))

- `/robots.txt` requested through the CDN now allows everything, the CDN serves only static files, check your custom deployment overrides
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/4010b55e6ebbb7b8333d50df901052b61c97da64) to update your project

#### Improve checkout selections, product flags and skeleton transitions ([#4891](https://github.com/shopsys/shopsys/pull/4891))

- product flags in administration, product filters, and the Frontend API now follow the global order configured in administration instead of their previous name or ID order; update customized flag queries and sorting to use `Shopsys\FrameworkBundle\Model\Product\Flag\Flag::$position`
- after upgrading, review and save the desired order in **Settings → Lists and nomenclatures → Flags** using drag and drop; migration `Shopsys\FrameworkBundle\Migrations\Version20261002120000` initializes positions from flag IDs, so previously alphabetical lists may change order
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/91c6c706251ff3be1d84209415b34f6757aab500) to update your project

#### Batch load all BlogCategory fields in the Frontend API ([#4950](https://github.com/shopsys/shopsys/pull/4950))

- `Shopsys\FrontendApiBundle\Model\Blog\Article\BlogArticlesQuery::blogArticleByCategoryQuery()` now returns `Promise` instead of `Connection|Promise` and paginates in `Paginator::MODE_PROMISE` through the `blog_articles_by_blog_category_batch_loader` DataLoader - update your override of this method accordingly
- Frontend API field `BlogCategory.blogArticles` now returns `totalCount` respecting the `onlyHomepageArticles` argument (previously all published articles of the category were counted) - adjust your storefront if it relied on the total of all articles
- `Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryFacade::getAllVisibleChildrenByBlogCategoryAndDomainId()` and `Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryRepository::getAllVisibleChildrenByBlogCategoryAndDomainId()` were removed, use `getAllVisibleChildrenByBlogCategoriesAndDomainIdIndexedByParentId()` instead
- `Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\BlogArticleElasticsearchFacade::getByBlogCategory()` and `Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\BlogArticleElasticsearchRepository::getByBlogCategory()` were removed, use `Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\FilterQueryFactory::createPublishedByBlogCategory()` with `BlogArticleElasticsearchDataFetcher::getAllResults()` or the `blog_articles_by_blog_category_batch_loader` DataLoader instead
- `Shopsys\FrameworkBundle\Model\Seo\HreflangLinksFacade::getForBlogCategory()` and the constant `HreflangLinksFacade::ROUTE_BLOG_CATEGORY_DETAIL` were removed, use the `blog_category_hreflang_links_batch_loader` DataLoader instead
- `Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryFacade::getVisibleByIds()` and `Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryRepository::getVisibleByIds()` were replaced by `getVisibleByIdsIndexedById()` which joins the translation of the domain locale and returns the blog categories indexed by their ID
- `Shopsys\FrameworkBundle\Model\Seo\HreflangLinksFacade::getRelevantDomainIds()` is public now, if you override it, change its visibility to public
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/66f9af23a16353cf7b843de6f6eb3f5f7731bc0e) to update your project

#### Admin edit pages now offer a button to save and stay on the page ([#4941](https://github.com/shopsys/shopsys/pull/4941))

- if you override `editAction()` or `newAction()` of a framework admin controller, or render a framework admin form type in your own controller, replace the redirect after a successful save with `Shopsys\FrameworkBundle\Controller\Admin\AdminBaseController::redirectAfterSave()`, or set `'apply' => false` in the action bar options of the form type
- if you want the button on your own admin page:
    - add `'apply' => true` to the options of `Shopsys\FormTypesBundle\ActionBarType` in your form type
    - replace the redirect after a successful save in your controller with `$this->redirectAfterSave($form, '<list route>', '<edit route>', ['id' => $entity->getId()])`

#### Fix long cron names overflowing admin toast messages ([#4945](https://github.com/shopsys/shopsys/pull/4945))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/71000e500f9931c2f38484c7945bfcfe6f3c26fb) to update your project

#### Flag categories in Frontend API are no longer limited to 10 ([#4946](https://github.com/shopsys/shopsys/pull/4946))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/3a039a872cc1df9e21c753342fc7d5d8e1d8724b) to update your project

#### Order items in Frontend API no longer include items of deleted orders ([#4947](https://github.com/shopsys/shopsys/pull/4947))

- if you override `Shopsys\FrontendApiBundle\Model\Order\OrderItemApiFacade::createCustomerUserOrderItemsLimitedListQueryBuilder()` or `Shopsys\FrontendApiBundle\Model\Order\OrderItemApiFacade::createCustomerOrderItemsLimitedListQueryBuilder()`, build the query on the new `Shopsys\FrontendApiBundle\Model\Order\OrderItemApiFacade::createOrderItemExcludingDeletedOrdersAndOrdersWithWithdrawalQueryBuilder()` so items of deleted orders are not returned
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/1c6dad8c57db28b29b324687ab2deb19285e2e44) to update your project

#### Complaints can no longer be created for deleted orders ([#4948](https://github.com/shopsys/shopsys/pull/4948))

- `Shopsys\FrameworkBundle\Model\Order\OrderRepository::getByUuid()` (and `OrderFacade::getByUuid()`) now throws `OrderNotFoundException` for a deleted order, the same as for a non-existing one, so `CreateComplaint` mutation can no longer be used for orders deleted by the shop and payment status notifications for deleted orders are ignored
    - if you call the method in your project and rely on getting deleted orders too, load the order on your own (e.g. by `findOneBy(['uuid' => $uuid])` on the Doctrine repository)
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/8bdb6e649ddf5aebdf6b5cf97ea93d8b06b3e447) to update your project

#### Match wishlist and comparison counts to visible products ([#4957](https://github.com/shopsys/shopsys/pull/4957))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/c05442ed468cc3e1aa314710f5b3f886edb4a426) to update your project

#### Show gallery titles and item counters instead of image ALT captions ([#4922](https://github.com/shopsys/shopsys/pull/4922))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/4c394fdfdfaaac58204c8d96534ae93b12065d07) to update your project

#### Clear promoted products cache when promoted products change ([#4960](https://github.com/shopsys/shopsys/pull/4960))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/3e66957c1a59fe34df4fc46af0546560b1eec414) to update your project

#### Add flash message history to administration ([#4961](https://github.com/shopsys/shopsys/pull/4961))

- if you have overridden `@ShopsysAdministration/layout/layout.html.twig`, include `@ShopsysAdministration/partial/flash_message/history.html.twig` in the top bar to display the message history
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/fd7e252e323bb02a7cf3c7607ea29f1662fa983a) to update your project

#### Complaint cannot be created for an electronic gift voucher via API ([#4949](https://github.com/shopsys/shopsys/pull/4949))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/9608d85a3a31c7fb4e54281d54cccffae4447db0) to update your project

#### Escape dynamic values in administration raw outputs ([#4965](https://github.com/shopsys/shopsys/pull/4965))

- `Shopsys\FrameworkBundle\Component\ConfirmDelete\ConfirmDeleteResponseFactory::createDeleteResponse()` and `createSetNewAndDeleteResponse()` now escape a message passed as a string
    - if your confirmation message contains HTML (e.g. `<br>`), pass it as `Twig\Markup` and pass the dynamic values (entity names, labels, e-mails) in the new `$messageParameters` argument, where they are escaped, instead of `t()` parameters
    ```diff
    - $message = t('Do you really want to remove rate "%name%"?<br>It is not used anywhere.', ['%name%' => $vat->getName()]);
    - return $this->confirmDeleteResponseFactory->createDeleteResponse($message, 'admin_vat_delete', $id);
    + $message = new Markup(t('Do you really want to remove rate "%name%"?<br>It is not used anywhere.'), 'UTF-8');
    + return $this->confirmDeleteResponseFactory->createDeleteResponse($message, 'admin_vat_delete', $id, ['%name%' => $vat->getName()]);
    ```
- `Shopsys\AdministrationBundle\Component\Action\RowAction::setConfirmMessage()` now escapes a message passed as a string
    - if your confirmation message contains HTML, pass it as `Twig\Markup`, and pass the dynamic values in the new `$parameters` argument instead of escaping them yourself
    ```diff
    - $action->setConfirmMessage(t('Remove "%name%"?<br>It is not used anywhere.', ['%name%' => htmlspecialchars($row['name'])]));
    + $action->setConfirmMessage(new Markup(t('Remove "%name%"?<br>It is not used anywhere.'), 'UTF-8'), ['%name%' => $row['name']]);
    ```
    - the confirm attributes are no longer part of the `attributes` template parameter, so if you override `@ShopsysAdministration/datagrid/row_action.html.twig`, render them yourself
    ```diff
         {{ attributes|raw }}
    +    {% if confirmMessage is not null %} data-confirm-window data-confirm-message="{{ action_confirm_message(confirmMessage, confirmMessageParameters) }}"{% endif %}
    ```
- `Shopsys\FrameworkBundle\Component\FlashMessage\FlashMessageService::addSuccessFlash()`, `addErrorFlash()`, `addInfoFlash()`, and `addWarningFlash()` (and the same methods of `Shopsys\FrameworkBundle\Component\FlashMessage\FlashMessageTrait`) now escape the message
    - replace the calls whose message contains HTML with `addSuccessFlashTwig()`, `addErrorFlashTwig()`, `addInfoFlashTwig()`, or the new `addWarningFlashTwig()` and pass the dynamic values as template parameters; never put values into the template string, they would be compiled as Twig code
    ```diff
    - $this->addSuccessFlashTwig(t('Holiday / internal day <strong>{{ name }}</strong> deleted', ['{{ name }}' => $closedDayName]));
    + $this->addSuccessFlashTwig(t('Holiday / internal day <strong>{{ name }}</strong> deleted'), ['name' => $closedDayName]);
    ```
    - the `*FlashTwig()` methods no longer call the `add*Flash()` methods, so if you override `add*Flash()` to customize all flash messages, override `addFlashMessage()` instead
- if you override methods of `Shopsys\FrameworkBundle\Twig\RequiredSettingExtension` that add warnings with warehouse, parameter, or domain names, add them with the new `addRequiredSettingsMessage()` method and pass the names as its parameters, where they are escaped
- if you override `@ShopsysAdministration/content/notificationBar/grid/text.html.twig`, replace `{{ value|raw }}` with `{{ value|sanitize_html('shopsys.ckeditor_preview') }}`
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/efffcdbde90758b39df6418821f689cf51061155) to update your project

#### Disable session upload progress in PHP image ([#4962](https://github.com/shopsys/shopsys/pull/4962))

- `session.upload_progress.enabled` is now turned off in the `shopsys/php-image` Docker image via `conf.d/shopsys.ini`, which takes precedence over `php.ini`; if your project relies on session upload progress, enable it again in your own `Dockerfile` with a `conf.d` file loaded after `shopsys.ini` (e.g. `RUN printf "session.upload_progress.enabled = On\n" > $PHP_INI_DIR/conf.d/zz-upload-progress.ini`), setting it in `php-ini-overrides.ini` has no effect

#### Prevent customer managers from granting stronger role groups ([#4975](https://github.com/shopsys/shopsys/pull/4975))

- review the customer user role groups of your project in the administration, a customer user can now assign a role group (`customerUserRoleGroups` query, `roleGroupUuid` of the `AddNewCustomerUser` and `EditCustomerUserPersonalData` mutations) only when the roles of the role group, expanded through the role hierarchy, are a subset of their own roles, and change the role group of another customer user or remove them only when they can assign the role group the customer user currently has (see `Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleResolver::canCustomerUserAssignRoleGroup()` and `canCustomerUserChangeRoleGroupOfCustomerUser()`)
    - add the missing roles to the role groups of customer users who should be able to assign the other role groups
    - add `ROLE_API_ALL` to the role groups of customer users who should be able to assign a role group with `ROLE_API_ALL`, having all the individual roles is not enough
- `ROLE_API_MANAGE_CUSTOMERS` now includes `ROLE_API_CUSTOMER_SELF_MANAGE` in the role hierarchy, if customer users with `ROLE_API_MANAGE_CUSTOMERS` must not manage their own data in your project, override `Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleHierarchyProvider::buildCustomerManagementRoleHierarchy()`
- if you translate `Manage all customers under the user's company` in your project, rename the msgid to `Manage all customers under the user's company (includes managing own data)`
- if you redefine the validation of the `roleGroupUuid` field of `AddNewCustomerUserDataInput` in your project, add the `Shopsys\FrontendApiBundle\Component\Constraints\CustomerUserRoleGroupAssignable` constraint to it
- if you redefine the class validation of `EditCustomerUserPersonalDataInput` in your project, add the `Shopsys\FrontendApiBundle\Component\Constraints\CustomerUserRoleGroupChangeAllowed` constraint to it
- if you override `Shopsys\FrontendApiBundle\Model\Mutation\Customer\User\CustomerUserMutation::checkCustomerUserCanBeDeleted()` without calling the parent, throw `Shopsys\FrontendApiBundle\Model\Resolver\Customer\Error\CustomerUserAccessDeniedUserError` when `Shopsys\FrontendApiBundle\Model\Mutation\Customer\User\CustomerUserMutation::canCustomerUserRemoveCustomerUser()` returns `false`
- if you override `Shopsys\FrontendApiBundle\Model\Resolver\Customer\CustomerUserRoleGroup\CustomerUserRoleGroupQuery::customerUserRoleGroupsQuery()`, use `Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupFacade::getAllAssignableByCustomerUser()` instead of `getAll()`
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/c4409f72b4b8615cdae89fea1552842f43db3327) to update your project

#### restrict the file manager (elFinder) to the WYSIWYG directory ([#4953](https://github.com/shopsys/shopsys/pull/4953))

- the file manager is now available only to administrators who can create or edit in at least one section, grant such a permission to administrators who need the file manager and have only view or delete permissions
- the root of the new `wysiwyg_filesystem` service is the `shopsys.filemanager_upload_dir` directory, if your file manager roots in `app/config/packages/fm_elfinder.yaml` point to another directory, set it to this parameter, root paths (`path`, `tmb_path`, attribute patterns) are now relative to it
- `Shopsys\FrameworkBundle\Component\Filesystem\FilesystemFactoryInterface::create()` now has a new `string $rootDirectory = ''` parameter, add it to your implementations and decorators of the interface, use it as the root (or the key prefix) of the created filesystem and pass it to the decorated factory
- the `main_filesystem` service is no longer public, if you get it from the container (`$container->get('main_filesystem')`), inject `League\Flysystem\FilesystemOperator` instead
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/6d8a4dc769241d88b48882c54a46d6c227923c37) to update your project

#### Added new admin accounts to allow pair testing ([#4976](https://github.com/shopsys/shopsys/pull/4976))

- `Shopsys\FrameworkBundle\Model\Administrator\AdministratorRepository::getCountExcludingSuperadmin()` was renamed to `getCount()` and now counts all administrators including superadmins
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/e939139cca35c89181dfd3dcfe91e2fb7e25dfa8) to update your project

#### Unified admin collections ([#4979](https://github.com/shopsys/shopsys/pull/4979))

- administration collections with add / remove buttons are now rendered by the new `Shopsys\FrameworkBundle\Form\InteractiveCollectionType` and the `interactive-collection` Stimulus controller, see [Collections with Add and Remove Buttons](https://docs.shopsys.com/en/latest/administration/collections-with-add-and-remove-buttons/)
    - if you have your own collection with custom JavaScript for adding and removing items, consider replacing it with `InteractiveCollectionType`
- `Shopsys\FrameworkBundle\Form\Admin\Mail\MailWhitelistCollectionType` was removed, use `Shopsys\FrameworkBundle\Form\InteractiveCollectionType` instead
- `Shopsys\FrameworkBundle\Form\Admin\PromoCode\PromoCodeFlagCollectionType`, `Shopsys\FrameworkBundle\Form\Admin\PromoCode\PromoCodeLimitCollectionType` and `Shopsys\FrameworkBundle\Form\ProductParameterValueType` now extend `Shopsys\FrameworkBundle\Form\InteractiveCollectionType` instead of `Symfony\Component\Form\Extension\Core\Type\CollectionType`
- admin JavaScript components `HreflangSetting.js`, `MailWhitelist.js`, `OpeningHoursCollection.js`, `Parameters.js`, `PromoCodeFlags.js`, `PromoCodeLimits.js`, `TransportPriceWithWeightLimitCollection.js` and the videos initialization in `Product.js` were removed together with the `js-*` classes and `data-*` attributes they used; if you import or extend any of them, move your logic to a Stimulus controller listening to the `interactive-collection:added` / `interactive-collection:removed` events
- form theme blocks `hreflang_setting_collection_widget`, `mail_whitelist_collection_widget` and `_product_form_videos_widget` were removed, the `hreflang_setting_collection` block prefix of `Shopsys\FrameworkBundle\Form\Admin\Seo\HreflangSettingFormType` was removed as well; if you override any of these blocks, override the `interactive_collection_widget` block or embed `@ShopsysAdministration/partial/_interactive_collection.html.twig` instead
- if you override the `promo_code_flag_collection_widget`, `promo_code_limit_collection_widget`, `transport_prices_with_limits_collection_widget`, `product_parameter_value_row` or `_store_form_userInformation_openingHours_widget` blocks, rewrite them to embed `@ShopsysAdministration/partial/_interactive_collection.html.twig` and override its `collection_header` / `collection_entry` blocks

#### `MustUploadFile` constraint now accepts already stored files ([#4981](https://github.com/shopsys/shopsys/pull/4981))

- `Shopsys\FrameworkBundle\Form\Constraints\MustUploadFile` is now satisfied also by an already stored image or file that is not being deleted
    - check that the new behavior matches the requirements of your forms that use this constraint
- `Shopsys\FrameworkBundle\Form\Admin\Advert\AdvertFormType` options `web_image_exists` and `mobile_image_exists` were removed, stop passing them
- `Shopsys\FrameworkBundle\Twig\ImageExtension::imageExists()` was removed as it is no longer used, use `Shopsys\FrameworkBundle\Component\Image\ImageFacade::getImageByObject()` and `Shopsys\FrameworkBundle\Component\Image\ImageLocator::imageExists()` instead
- [features moved](#movement-of-features-from-project-base-to-packages) from project-base to the framework package:
    - `App\Model\Slider\SliderItem::$uuid` and `getUuid()` → `Shopsys\FrameworkBundle\Model\Slider\SliderItem`
    - `App\Model\Slider\SliderItemData::$uuid` and `$mobileImage` → `Shopsys\FrameworkBundle\Model\Slider\SliderItemData`
    - web and mobile images of a slider item in `App\Model\Slider\SliderItemDataFactory` and `App\Model\Slider\SliderItemFacade` → `Shopsys\FrameworkBundle\Model\Slider\SliderItemDataFactory` and `Shopsys\FrameworkBundle\Model\Slider\SliderItemFacade`
    - `App\Model\Slider\SliderItemFacade::IMAGE_TYPE_WEB` and `IMAGE_TYPE_MOBILE` → `Shopsys\FrameworkBundle\Model\Slider\SliderItemFacade`
    - `image` and `mobileImage` fields of `App\Form\Admin\SliderItemFormTypeExtension` → `Shopsys\FrameworkBundle\Form\Admin\Slider\SliderItemFormType`
    - `uuid` field of the `SliderItem` type → `SliderItemDecorator.types.yaml`
    - migration `App\Migrations\Version20210727191108` → `Shopsys\FrameworkBundle\Migrations\Version20261029120000`
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/725d1f2340dafc3c9684ab03c57fe583b22766b0) to update your project

#### Upgrade Tabler in administration to 1.7.4 ([#4983](https://github.com/shopsys/shopsys/pull/4983))

- administration styles now import precompiled `@tabler/core/dist/css/tabler.css` and `tabler-vendors.css` instead of compiling Tabler Sass sources; if your admin SCSS uses Tabler Sass mixins or variables (e.g. `media-breakpoint-up()`, `$navbar-height`), add `@use '@tabler/core/scss/config' as *;` at the top of the file
- `@extend` of Tabler classes (e.g. `.icon`, `.sticky-top`) no longer works in admin SCSS, copy the declarations you need instead
- Tabler Sass variables backed by CSS custom properties now compile without the `--tblr-` prefix (e.g. `$border-color` → `var(--gray-200)`), use `var(--tblr-*)` custom properties in your admin SCSS instead
- `bootstrap`, `autosize` and `countup.js` were removed from `@shopsys/administration` dependencies and the `window.autosize` and `window.countUp` globals are no longer set; use `Autosize` and `CountUp` exported from `@tabler/core` instead
- dropdown menus in the top admin navbar are now positioned by Popper (with `position: fixed`) like all other admin dropdowns, instead of by CSS; if you override `partial/select_admin_domains.html.twig` or `partial/flash_message/history.html.twig`, or have your own navbar dropdowns, don't use a percentage `min-width` (it is computed from the viewport width), `.position-static` on the dropdown item or margin utilities (e.g. `.me-2`) on the menu, since they break its placement
- if you use Tabler classes in your own admin templates, check the [Tabler upgrade guides 1.5–1.7](https://docs.tabler.io/ui/getting-started/upgrade) (e.g. `.badges-list` → `.badge-list`)

#### MCP: SQL queries run against database views with exposed columns only ([#4984](https://github.com/shopsys/shopsys/pull/4984))

- prepare your environments for the MCP database views in the `mcp` schema, which `shopsys:migrations:migrate` recreates on every run:
    - before deploying, make sure the MCP database role (`MCP_DATABASE_USER`) exists in every environment where MCP is enabled and has `USAGE` on schema `public` (the roles are usually managed by your hosting or devops), otherwise the migrations fail with `Shopsys\McpBundle\Component\Database\View\Exception\McpDatabaseRoleNotFoundException` or `Shopsys\McpBundle\Component\Database\View\Exception\McpDatabaseRoleWithoutPublicSchemaUsageException`
    - the application database user must be allowed to create schemas in the database (the database owner is)
    - the `mcp` schema is owned by the application and dropped with everything in it on every migration run (even when MCP is disabled), do not create any other objects in it
        - if your database already contains a schema named `mcp`, set `shopsys_mcp.database.views_schema_name` to another name before deploying, otherwise the migrations drop the schema, and use that name in the `search_path` of the MCP role below
    - right before deploying, reduce the privileges of a dedicated MCP role so that the database itself keeps MCP queries away from the hidden columns, and limit its memory; MCP queries fail until the deploy finishes, as the previous version still reads the tables in `public`; run it as a database superuser, which is usually done by your hosting or devops:
        ```sql
        -- when the role is a member of a group with access to the tables, e.g. the built-in read-only role or a group of your hosting
        REVOKE pg_read_all_data FROM "<mcp-user>";
        REVOKE "<read-only-group>" FROM "<mcp-user>";
        REVOKE ALL PRIVILEGES ON ALL TABLES IN SCHEMA public FROM "<mcp-user>";
        ALTER DEFAULT PRIVILEGES FOR ROLE "<application-user>" IN SCHEMA public REVOKE ALL ON TABLES FROM "<mcp-user>";
        GRANT CONNECT ON DATABASE "<database>" TO "<mcp-user>";
        GRANT USAGE ON SCHEMA public TO "<mcp-user>";
        -- the MCP connection sets these per session as well, the role defaults survive a session reset by a connection pooler
        ALTER ROLE "<mcp-user>" SET search_path = mcp;
        ALTER ROLE "<mcp-user>" SET default_transaction_read_only = on;
        -- the statement timeout does not limit memory, 4MB is the default work_mem of PostgreSQL
        ALTER ROLE "<mcp-user>" SET work_mem = '4MB';
        ALTER ROLE "<mcp-user>" SET temp_file_limit = '256MB';
        ```
    - if the privileges are not reduced, the views exist but the role still reads the tables in `public`, so only the SQL validator keeps the hidden columns away; the post-deploy task `check_mcp_database_role_privileges`, which runs on every deploy, therefore fails the non-blocking deploy phase with the `REVOKE` statements to run while a dedicated MCP role can read a table in `public` or has default privileges on the tables created there
    - when `MCP_DATABASE_USER` is the application database user, skip the privileges and the post-deploy task is skipped as well, but such an environment has no database barrier, only the search path and the SQL validator, so use a dedicated role wherever the database holds production data, including development servers with a copy of it
    - see the recommended role setup in [MCP server documentation](https://docs.shopsys.com/en/latest/ai/mcp/mcp-server/)
    - for local development and CI, the role is prepared by the application, rebuild your databases (e.g. `php phing db-demo test-db-demo`) so the local MCP role loses access to the tables in `public` and gets the role settings
    - if you query `doctrine.dbal.mcp_connection` in your own code, query the views by unqualified table names, the tables in `public` are not accessible through it
    - if you change the database schema outside of `shopsys:migrations:migrate` (e.g. with `Doctrine\ORM\Tools\SchemaTool` in tests), drop the views first by `Shopsys\McpBundle\Component\Database\View\McpDatabaseViewsFacade::dropViews()` and recreate them afterwards by `recreateViews()` or by `php bin/console shopsys:mcp:database-views:recreate`
- `Shopsys\McpBundle\Component\Database\Query\SqlQueryValidator` was rewritten, it no longer resolves columns (the views contain only exposed columns) and accepts only allowed parse tree nodes (`ALLOWED_NODE_TAGS`), if you extended it, review your changes
    - constants `ERROR_WILDCARD_SELECT_NOT_SUPPORTED`, `ERROR_COLUMN_NOT_EXPOSED`, `ERROR_AMBIGUOUS_UNQUALIFIED_COLUMN`, `ERROR_UNKNOWN_RELATION_ALIAS` and `UNSUPPORTED_NODE_TAGS` were removed
    - constant `ERROR_DISALLOWED_CAST_TARGET` was renamed to `ERROR_TYPE_NOT_ALLOWED` and `ALLOWED_TYPE_CAST_NAMES` to `ALLOWED_TYPE_NAMES`
    - the column resolving methods (e.g. `validateSelectStatement()`, `validateColumnReference()`, `collectRelationsFromNode()`) were removed, `isAllowedTypeCast()` was renamed to `isAllowedTypeName()`
- `Shopsys\McpBundle\Component\Database\User\McpReadOnlyUserManager` was renamed to `Shopsys\McpBundle\Component\Database\User\McpDatabaseUserFacade` and its method `ensureReadOnlyUser()` to `ensureMcpUser()`
- `Shopsys\McpBundle\EventSubscriber\DatabaseSchemaPreparedSubscriber::ensureMcpReadOnlyUser()` was renamed to `ensureMcpDatabaseUser()`
- `Shopsys\McpBundle\Component\Database\Schema\ExposedSchemaProvider::getAllowedColumnsSetIndexedByTableNames()` was removed, use the `columns` of the tables returned by `getExposedSchema()` instead
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/4043e80fbdc2ee3b8732e9a55b9837f7f4ad705c) to update your project
- see also project-base diff of [#5004](https://github.com/shopsys/shopsys/pull/5004) with additional fixes

#### Batch load Flag fields in the Frontend API ([#4994](https://github.com/shopsys/shopsys/pull/4994))

- `Shopsys\FrameworkBundle\Model\Seo\HreflangLinksFacade::getForFlag()` was removed, use `getForFlags()`, the protected `doGetHrefLinks()` no longer accepts `Shopsys\FrameworkBundle\Model\Product\Flag\Flag`
- `Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade::getVisibleFlagsByIds()` and `Shopsys\FrameworkBundle\Model\Product\Flag\FlagRepository::getVisibleFlagsByIds()` return the flags indexed by their ID
- `Shopsys\FrameworkBundle\Model\Category\CategoryFacade::getCategoriesOfProductByFilterData()` was removed, the flag categories are loaded by `Shopsys\FrontendApiBundle\Model\Resolver\Category\CategoryQuery::categoriesFilteredByProductFilterForFlagQuery()`, which returns a `Promise` and no longer returns categories hidden on the current domain
- `Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchRepository::getCategoryIdsForFilterData()` was removed, use `getAggregationQueryForCategoryIds()` to create the query and the now public `extractCategoryIdsAggregation()` to read the category IDs from the response
- `Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterFacade::getValidatedProductFilterDataForFlag()` and `getProductFilterConfigForFlag()` were removed, use `getValidatedProductFilterDataPromiseForFlag()` or `getProductFilterConfigPromiseForFlag()`
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/285026484a735f2c53f478c58c863185897fb77a) to update your project

#### Fix missing Bootstrap variables in production admin styles ([#5011](https://github.com/shopsys/shopsys/pull/5011))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/34fbcf418b4244a258f42b9137bf2cefaaa95686) to update your project

<!-- backendNotes -->

### Storefront

#### GraphQL performance improvements ([#4561](https://github.com/shopsys/shopsys/pull/4561))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/8ae58d0a3e436e641911d07f18bec09fbbb89fd5) to update your project

#### Add Zbozi.cz category to GTM page metadata ([#4600](https://github.com/shopsys/shopsys/pull/4600))

- Category detail data now includes `zboziCategory` from GraphQL and passes it to GTM page metadata.
- If you customize category detail GraphQL fragments, generated types, or GTM page info mapping, update them so category pages can emit `page.zboziCategory` when the backend category mapping exists.
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/0f4cf3717017268f682f3eb98235d79b3116f1ec) to update your project

#### Upload Storefront source maps to Sentry ([#4620](https://github.com/shopsys/shopsys/pull/4620))

- Storefront builds can now upload source maps to Sentry as artifact bundles when Sentry CI credentials are available
- Sentry release name is now shared between uploaded artifacts, runtime events, and release commit metadata using the current commit SHA
- Sentry CLI optional binaries are now installed so source map uploads work during production builds
- projects that should upload source maps from CI need to provide `SENTRY_AUTH_TOKEN`, `SENTRY_ORG`, and `SENTRY_PROJECT`; leave `SENTRY_AUTH_TOKEN` unset in environments where source map upload should stay disabled
- projects using the standard Shopsys self-hosted Sentry setup also need to provide `SENTRY_URL` pointing to their Sentry instance
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/9cd170081f9930f1710c61478bdb515a9019181e) to update your project

#### Link Sentry storefront traces with backend requests ([#4621](https://github.com/shopsys/shopsys/pull/4621))

- if your project overrides storefront Sentry initialization, update it to enable `Sentry.browserTracingIntegration()` and configure `tracePropagationTargets` for your GraphQL endpoints so frontend requests propagate `sentry-trace` and `baggage` headers to the backend
- keep the storefront and backend tracing sample rate aligned (project-base uses `0.1` on both sides) to make frontend and backend spans appear in the same Sentry trace consistently
- ensure `SENTRY_RELEASE` is available for the storefront runtime if you rely on release-based Sentry filtering or trace investigation
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/e2495cf08c59f940f426a8bf045292330a145baf) to update your project

#### Set Corepack cache path in storefront Docker images ([#4625](https://github.com/shopsys/shopsys/pull/4625))

- storefront Dockerfiles now set `COREPACK_HOME=/usr/local/share/corepack`
- if you override storefront Dockerfiles in your project, add the same `COREPACK_HOME` value before running `corepack enable && corepack prepare pnpm@10.30.3 --activate`
- this makes Corepack use the same cache location during image build and container runtime
- without an explicit cache path, pnpm prepared during Docker image build can be stored in the root user's Corepack cache, while the running storefront container executes as the `node` user and uses a different cache
- keeping the cache shared prevents unnecessary runtime pnpm downloads and makes the storefront Docker image startup more deterministic
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/cc1819c142d0e86689819fed47f120f465e8121f) to update your project

#### Keep Next cache writable in production storefront image ([#4629](https://github.com/shopsys/shopsys/pull/4629))

- production storefront image keeps application files owned by `root:root` and read-only, but Next.js needs writable `.next/cache` at runtime for image cache files
- if you override production storefront Docker image setup, create `$APP_DIR/.next/cache` after hardening application files and make it writable for the runtime user (`node` by default)
- keep the writable exception limited to `.next/cache`; do not make the whole `$APP_DIR` writable, so the immutable application directory hardening from #4481 stays preserved
- this follows from investigation of `EACCES: permission denied, mkdir '/home/node/app/.next/cache'` in production storefront runtime

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/74f349956a761f8e1fe35b296d57831208249512) to update your project

#### Fix storefront domain resolution behind proxies ([#4630](https://github.com/shopsys/shopsys/pull/4630))

- Storefront domain resolution is now safer behind reverse proxies. When Next.js receives an internal request host such as `webserver-php-fpm:8080`, but the public domain is available in `X-Forwarded-Host`, both SSR and middleware domain resolution prefer `X-Forwarded-Host` before the `Host` header. If your project customizes `getDomainConfig()`, `resolveDomainConfigByHost()`, or middleware domain parsing, port this behavior to avoid matching internal upstream hosts.
- Default ports are normalized during domain matching. HTTPS requests with `:443` and HTTP requests with `:80` still match domains configured without those ports, while non-default ports such as local `localhost:8000` remain significant and must stay in the `domains` configuration.
- Requests for an unsupported locale on an otherwise configured host now fall back to the configured domain for that host and continue through the standard storefront routing/error handling. If your project overrides domain or locale matching, preserve this fallback to avoid server-side `Domain ... is not configured` errors for scanner or manually typed URLs such as `/en/...`.
- When investigating similar production errors, inspect `Host`, `X-Forwarded-Host`, and `X-Forwarded-Proto` from your edge/proxy setup. The failing host can come from proxy headers or default ports on incoming requests even when the storefront itself does not generate links with those ports.
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/2984e7a571ee32d321302589f26fd2b3015e8768) to update your project

#### Allow quantity changes from product cards ([#4632](https://github.com/shopsys/shopsys/pull/4632))

- product cards and product detail now show quantity controls when the product is already in the cart
- product actions now show skeletons while the cart state is loading to avoid flickering between add-to-cart buttons and quantity controls
- quantity controls now debounce rapid plus/minus changes in the Spinbox itself and submit one final quantity change per debounce window
- the mini-cart now uses quantity controls for regular products and keeps gift rows read-only with the existing gift badge
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/a49ba37d8c14ff72a6bbe83abb2176cecdaf1ff8) to update your project

#### Redesign minimal storefront error page ([#4487](https://github.com/shopsys/shopsys/pull/4487))

- minimal storefront error page now uses brand styling, corrected background token, fixed button hover styling, and production-only debug panel behavior
- if your project customizes `MinimalErrorContent`, port the updated fallback error page markup and styling
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/a8955cd2dc56403e734491c1321ec3713c11834c) to update your project

#### Improve storefront accessibility for screen readers ([#4635](https://github.com/shopsys/shopsys/pull/4635))

- storefront accessibility was improved across skip links, product sliders, product cards, filters, forms, cart items, product detail controls, and popup dialogs
- accessible names for text buttons were unified according to WCAG accessibility guidance: clear visible labels are used directly, while generic labels are extended with additional context that starts with the visible action text
- popup dialogs now announce important status updates more clearly and restore focus to the element that opened them after closing
- storefront color tokens and dynamic flag text contrast were adjusted to better satisfy WCAG contrast requirements
- if your project overrides storefront components, translations, or theme tokens touched by this change, review the project-base diff and apply the relevant accessibility and contrast updates
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/03cd6b5ee28a604f0bef353d3ba15d700110226d) to update your project

#### Add heading anchor navigation to blog articles ([#4638](https://github.com/shopsys/shopsys/pull/4638))

- blog article detail pages now render article content navigation generated from `h2` headings in the article text
- generated heading anchors use the heading text and include an introduction link
- the navigation is rendered only in the blog article layout, regular article detail pages keep their previous centered layout
- if you override blog article detail components, blog layout, GrapesJS article parsing, or user text heading styles in your project, apply the relevant changes
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/36437ddcb64daffee54d9249c9c1433578ac9dc5) to update your project

#### Add customer account order and complaint filtering ([#4641](https://github.com/shopsys/shopsys/pull/4641))

- add `@radix-ui/react-popover` and `react-day-picker` to your storefront dependencies and update `pnpm-lock.yaml`
- if your project customizes customer account order or complaint pages, update them from [project-base diff](https://www.github.com/shopsys/project-base/commit/3b38d6417dfb59bf9d61828f792dc7dbc3077286) to include the new filters, status tabs, empty states, skeletons, date picker, and URL query handling
- if your custom customer account filters send date values to the Frontend API, update them to send `createdAfter` and `createdBefore` as UTC `DateTime` values
- if your custom storefront imports `project-base/storefront/components/Pages/Customer/Orders/OrderItemElements.tsx`, replace it with the shared customer record components from `project-base/storefront/components/Pages/Customer/CustomerRecordElements.tsx`
- if your custom storefront imports `project-base/storefront/components/Pages/Customer/Complaints/ComplaintItemColumnInfo.tsx`, replace it with the shared customer record components from `project-base/storefront/components/Pages/Customer/CustomerRecordElements.tsx`
- regenerate storefront GraphQL types after applying the backend schema changes
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/3b38d6417dfb59bf9d61828f792dc7dbc3077286) to update your project

#### Unify order confirmation and payment return flow ([#4642](https://github.com/shopsys/shopsys/pull/4642))

- replace usages of storefront route `/order-payment-confirmation` with `/order-confirmation`
- replace usages of `useOrderPaymentPageContentQuery()` and `useOrderSentPageContentQuery()` with order data from `useOrderDetailByHashQuery()` or `useOrderDetailQuery()` and field `order.confirmationPageContent`
- replace usages of `useOrderDetailByHashOrUuidQuery()` with `useOrderDetailByHashQuery()` or `useOrderDetailQuery()`
- remove usages of `useSetOrderPaymentStatusPageValidityHashMutation()`
- update usages of `useUpdatePaymentStatusMutation()` to stop passing `orderPaymentStatusPageValidityHash`
- update custom payment gateway return handling to use `returnHash` and resolve the order hash via `orderUrlHashByReturnHash` before rendering the order confirmation
- replace usages of `TypePaymentContentPageStatusEnum` with `TypeOrderConfirmationPageContentStatusEnum`
- replace custom GTM usages of `GtmEventType.payment_fail`, `GtmPaymentFailEventType`, `getGtmPaymentFailEvent()`, or `onGtmPaymentFailEventHandler()` with the unified payment GTM event
- replace usages of `gtmPaymentEventLocalStorage` with retry count derived from `Order#paymentTransactionsCount`; ensure custom order fragments used for payment GTM include this field
- update custom order fragments used for payment GTM to include `Order#paymentStatus`
- update custom payment GTM event payloads to include `paymentStatus`; use raw GoPay status from `Order#paymentStatus` for GoPay payments and `NOT_APPLICABLE` for payments without an external gateway
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/942b9c14520eb6917e906f13171f4535f561d4e2) to update your project

#### [SSP-4095] store filter improvement ([#4650](https://github.com/shopsys/shopsys/pull/4650))

- update custom `GoogleMap` usages because `activeMarkerHandler` now receives `MapMarker | null` instead of marker identifier string; use `marker?.identifier` when you need the store UUID
- replace custom imports or usages of removed `StoreSelect` component with `StoresWrapper` or `StoreList`/`StoreListItem` with `onSelectStoreCallback`
- update custom `StoresWrapper` usages to pass `stores`, `searchTextValue`, and `onSearchTextCallback` instead of relying on internal `StoresQuery`
- update custom queries using `TransportStoresFragment` to define and pass `$searchText`, `$coordinates`, `$first`, and `$after`; use the updated `TransportStoresQuery` as a reference
- update manually created `TypeListedStoreConnectionFragment` mocks or fixtures to include `pageInfo.hasNextPage` and `pageInfo.endCursor`
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/9fc69aa9df4582938bf20fea66f5c276fd18a8e2) to update your project

#### Redesign storefront navigation and mobile controls ([#4657](https://github.com/shopsys/shopsys/pull/4657))

- update custom storefront usages of Frontend API field `NavigationItem#link`, as it is now nullable for category dropdown navigation items
- include and use Frontend API field `NavigationItem#type` in custom navigation GraphQL fragments and components to distinguish direct links from category dropdowns
- align custom mobile slide-in panels with the shared `Drawer` and `Portal` implementation
    - render mobile drawers through `OVERLAY_PORTAL_ROOT_ID`
    - use the shared `Drawer` for overlay handling, body scroll locking, Escape closing, right-side placement, and z-index stacking
    - use the shared `Drawer` for mobile menu, mobile filter panel, cart panel, and account panel
- port the bottom navigation menu and simplified mobile header when maintaining custom mobile storefront navigation
- port the top search overlay with immediate popup opening, input focus, and close action for an empty input when maintaining custom mobile search
- port the floating filter and sorting controls, including active filter count and scrolling to selected filters after applying filters, when maintaining custom product listing controls on mobile
- port the mega menu layout, overlay behavior, and link/category item handling when maintaining custom desktop navigation dropdowns
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/cf1c718cb075407f01caea3a38a092d23a31f1b7) to update your project

#### Upgrade Storefront npm dependencies ([#4658](https://github.com/shopsys/shopsys/pull/4658))

- Storefront npm dependencies were upgraded so `pnpm audit` passes without security vulnerabilities
- Storefront pnpm overrides were reduced to the remaining audit and URQL compatibility entries
- GraphQL Code Generator configuration was adjusted for the upgraded `@graphql-codegen/*` packages to keep generated Storefront types compatible with the existing codebase
- custom GraphQL scalars are explicitly mapped to concrete TypeScript types (`DateTime` to `string`, `FileUpload` to `File`, and `Password` to `string`), because the new generator defaults unknown scalars to `unknown`
- `avoidOptionals` and `preResolveTypes` are configured explicitly to preserve the previous shape of generated operation variables and avoid TypeScript errors after running `make generate-schema`
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/1f9970213dd1ea4e780d7323ca37a8eae170f458) to update your project

#### added form for customer questions about products to product detail ([#4661](https://github.com/shopsys/shopsys/pull/4661))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/35fb6fbdde6338f83d3707b618ef81b596670c9d) to update your project

#### Add grouped transport selection to checkout ([#4662](https://github.com/shopsys/shopsys/pull/4662))

- update custom transport GraphQL fragments used in checkout to include `Transport#group` with `id`, `name`, `position`, and `mainImage` if you want to use grouped transport selection
- update custom checkout transport/payment selection components to handle transports grouped by `Transport#group`; transports without a group should still be rendered as standalone transport items
- if you override `Radiobutton`, update custom `onClick` forwarding to pass both the selected value and the keyboard/mouse event
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/5e5c338fcf579a841bf77c145eea18a3f740c746) to update your project

#### Count nested fields in GraphQL pagination complexity ([#4665](https://github.com/shopsys/shopsys/pull/4665))

- update custom storefront GraphQL queries using paginated fields to request only displayed nested data
    - if they newly exceed `query_max_complexity`, reduce nested selections
    - or intentionally raise the configured complexity limit
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/83c4e81f6c14245be844f1f22311e21ac72626a5) to update your project

#### Improve storefront initial runtime performance ([#4675](https://github.com/shopsys/shopsys/pull/4675))

- update your storefront according to [project-base diff](https://www.github.com/shopsys/project-base/commit/72e76fe248c32033173aba89ccf41ec4d70fbfe8), especially if you customized product detail gallery or availability, store detail map or gallery, promo code, cart step navigation, product comparison, or last visited products on listing and content pages
- replace imports of `getTransportAndPaymentValidationMessages` from `components/Pages/Order/TransportAndPayment/transportAndPaymentUtils` with `utils/cart/getTransportAndPaymentValidationMessages`

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/72e76fe248c32033173aba89ccf41ec4d70fbfe8) to update your project

#### Blog article authors ([#4682](https://github.com/shopsys/shopsys/pull/4682))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/b6edd322f2394819c8bff2a39222527d77235642) to update your project

#### Sticky header ([#4676](https://github.com/shopsys/shopsys/pull/4676))

- if you override storefront layout, header, navigation, autocomplete search, cart, logo, user menu, overlay, notification bar, popup, or product detail section navigation components in your project, apply the relevant desktop fixed header changes
- update custom desktop header implementations to render a compact fixed header after the original header leaves the viewport
- pass already loaded navigation data to the fixed header to avoid loading the same navigation again
- update custom navigation implementations to move overflowing desktop navigation items into the `More` menu instead of relying on horizontal scrolling
- update custom autocomplete search, cart, and user menu implementations to use unique input IDs and consistent overlay behavior when rendered in both the regular header and the fixed header
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/f9a5029a1b22599bab6ab362fe4377eda5096d5e) to update your project

#### Upgrade Storefront test runtime dependencies ([#4684](https://github.com/shopsys/shopsys/pull/4684))

- Upgrade Storefront `vitest` to 4.x and `jsdom` to 29.x; see [project-base diff](https://www.github.com/shopsys/project-base/commit/c2330b42c3f047ab8231fa823cec07d1180500a2) for the exact `package.json` and `pnpm-lock.yaml` changes.
- If your project has custom Vitest tests with `mockImplementation()` overrides, reset those mock implementations in `beforeEach()`. Vitest 4 exposed tests that relied on leaked mock state from previous scenarios, so affected Storefront tests now reset router, session store, and SEO-sensitive filter mocks explicitly.
- If your project keeps custom Storefront `pnpm.overrides`, make sure `undici` resolves to at least `7.28.0` so `pnpm audit` does not report the vulnerable transitive version from `i18next-parser`.

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/c2330b42c3f047ab8231fa823cec07d1180500a2) to update your project

#### allow restricting products to personal pickup only ([#4671](https://github.com/shopsys/shopsys/pull/4671))

- transports excluded for products in the cart are no longer hidden in the checkout; they are rendered disabled with the blocking products listed by the new `TransportUnavailabilityInfo` component — if you override `TransportAndPaymentSelect`, `TransportSelectListItem`, or the transport fragments, apply the changes from the project-base diff
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/ba3a234ac6794b862b7aad321e8101069dc28b52) to update your project

#### storefront passes order urlHash as ownership proof to payment operations ([#4693](https://github.com/shopsys/shopsys/pull/4693))

- pass `$orderUrlHash` to the `UpdatePaymentStatusMutation`, `PayOrderMutation`, and `OrderAvailablePaymentsQuery` documents, and add `orderUrlHash` to `ChangePaymentInOrderInput`
- update `UpdatePaymentStatusFragment` to the new `UpdatePaymentStatusResult` type, drop `urlHash`, `items`, and `number` and select `orderNumber` and `paymentName` instead
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/41ab5ecd5160f4efaf891a21f8aa5d6cf5b0f879) to update your project

#### Gift vouchers – purchase, generation, redemption ([#4695](https://github.com/shopsys/shopsys/pull/4695))

- if you customized `components/Blocks/PromoCode`, `components/Pages/Cart/CartPreview.tsx`, `components/Pages/Order/TransportAndPayment`, `components/Pages/Order/ContactInformation`, `components/Pages/OrderConfirmation`, or the GTM mappers, merge the new gift voucher handling (multi-code input, remaining to pay rows, email transport auto-selection, the `giftVouchersExceedPayableAmount` warning that disables order submission, `productType` and voucher fields in `ec.create_order`)
- the `discountAmount` field in the `ec.create_order` GTM event now contains the discount excluding VAT (previously including VAT); the including-VAT value moved to the new `discountAmountWithTax` field – update your analytics/GTM containers accordingly
- the `ec.create_order` GTM event renamed its order value fields: `valueWithoutVat` → `value`, `valueWithVat` → `valueWithTax`, `vatAmount` → `valueTax`, and `currencyCode` → `currency` – update your analytics/GTM containers accordingly
- when gift vouchers cover the whole order, the transport-and-payment step auto-selects the gift-voucher-type payment and replaces the payment chooser with an informational box, and the gift-voucher payment is filtered out of the payment list otherwise – merge this into customizations of the `TransportAndPayment` components
- hooks `useRemovePromoCodeFromCart` and `useRemoveGiftVoucherFromCart` were replaced by the unified `useRemoveCodeFromCart`, and `useApplyCodeToCart` now calls the single `ApplyCodeToCart` mutation – merge this into customizations of the cart code input and the cart preview rows
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/00964a1de0b224277ccf4bcbd3766aa91c7ebd6b) to update your project

#### Highlight a single recommended product in articles ([#4696](https://github.com/shopsys/shopsys/pull/4696))

- add the `ArticleProductHero` component from `project-base/storefront/components/Blocks/Product/ArticleProductHero.tsx` together with the `grapesjs_product_hero` TID
- update `GrapesJsProducts` in `project-base/storefront/components/Basic/UserText/GrapesJsProducts.tsx` to render `ArticleProductHero` when exactly one fetched visible product remains and to keep using `ProductsSlider` for multiple visible products
- update the `highlight` variant in `project-base/storefront/components/Basic/Flag/Flag.tsx` to use `bg-orange-500 text-text-default`
- add the `Recommended product` translation key to every locale supported by your storefront
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/f2bc4cd05debc5d6ff10c5ed462dfef699340e3d) to update your project

#### Allow B2B user managers to access customer management ([#4697](https://github.com/shopsys/shopsys/pull/4697))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/4a5711f662280c525575d4b617d718e2a26e5c87) to update your project

#### Add grid and list views to storefront product listings ([#4704](https://github.com/shopsys/shopsys/pull/4704))

- if your storefront customizes `utils/cookies/cookiesStore.ts`, add `productListViewMode` with the `ProductListViewModeType` type to `CookiesStoreState` and initialize it to `grid` in `getDefaultCookiesStoreState`
- if your storefront customizes the product-list rendering:
    - render `ProductListViewModeToggle` in `SortingBar`
    - read `productListViewMode` from `useCookiesStore`, use `productListTwClass` for grid mode or `productListViewModeListTwClass` for list mode, and pass the mode through `ProductsListContent`, `ProductListItem`, `ProductListItemPlaceholder`, and `SkeletonModuleProductListItem`
    - use the cookie-backed mode during server and client rendering so product listings and category placeholders produce matching markup during hydration
- replace `variant="list"` with `variant="grid"` in customized `ProductFlags` usages that should keep flags positioned over the grid-card image; `variant="list"` now renders flags inline for the compact list layout
- add `Go to product page`, `Grid view`, and `List view` to the `common` namespace and `Product list view mode`, `Show products in grid view`, and `Show products in list view` to the `accessibility` namespace for every supported custom locale
- update customized Cypress tests to initialize the domain-specific `cookiesStore` cookie when testing the persisted view mode, and add the `blocks_product_list_view_grid` and `blocks_product_list_view_list` TIDs where the toggle is tested
- after adding `GridIcon`, `ListIcon`, and the new Tailwind layout classes, regenerate derived assets with `make generate-icons-for-styleguide` and `make generate-tailwind-for-admin`
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/7fafdb9a9e2f5d808653acaee1b4540c63ff5427) to update your project

#### Protect storefront refresh tokens with HttpOnly cookies ([#4705](https://github.com/shopsys/shopsys/pull/4705))

- copy the new Next.js API routes `pages/api/auth/token.ts` and `pages/api/auth/clear.ts` together with `utils/auth/server/authApi.ts`, `utils/auth/authMutationFetcher.ts`, `utils/auth/authConstants.ts`, and `utils/serverSide/getIpAddressFromRequest.ts` into your storefront
    - ensure the backend `TRUSTED_PROXIES` configuration includes the storefront server or its internal proxy so Symfony accepts the forwarded client IP for login rate limiting
- update all custom browser-side authentication flows using `LoginMutation`, `LoginViaExchangeTokenMutation`, `LogoutMutation`, `RecoverPasswordMutation`, `RefreshTokens`, `RegistrationMutation`, or `RegistrationByOrderMutation` to pass `{ fetch: getAuthMutationFetcher(domainConfig) }` to the URQL mutation
    - do not store, read, or send the refresh token from client-side JavaScript
- replace usages of `getTokensFromCookies()` from `utils/auth/getTokensFromCookies.ts`
    - use `getAccessTokenFromCookies()` to read the access token
    - use `hasRefreshTokenInCookies()` in browser code to detect an available refresh session
    - use `getRefreshTokenFromCookies()` only in server-side code with a request/response context
- replace imports of the removed `OptionalTokenType` from `urql/types.ts` with the return types of the individual cookie helpers
- update calls to `setTokensToCookies()` and `removeTokensFromCookies()` to pass a server-side request/response context
    - use `clearAuthCookies(domainConfig)` instead of `removeTokensFromCookies(domainConfig)` in browser code
- update custom password recovery code to call the handler returned by `useLoginAfterPasswordRecovery()` with only `showCartMergeInfo`
    - execute `RecoverPasswordMutation` through `getAuthMutationFetcher(domainConfig)` so the server stores the returned tokens
- replace usages of the removed `getProtocol()` from `utils/requestProtocol.ts` with the protocol from `getDomainConfig(context).url` on the server or `window.location.protocol` in the browser
- replace imports of the removed `getIpAddress()` from `utils/serverSide/buildServerSideProps.ts` with `getIpAddressFromRequest()` from `utils/serverSide/getIpAddressFromRequest.ts` and pass `context.req` directly
- update custom Cypress authentication commands to create `refreshToken-{domainId}` as an `HttpOnly`, `Secure`, `SameSite=Lax` cookie and also create the non-sensitive `refreshTokenPresent-{domainId}` cookie
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/5b7c7b55b2fbd5f662050c3e7bdbc4f970c50c41) to update your project

#### Upgrade storefront form dependencies ([#4706](https://github.com/shopsys/shopsys/pull/4706))

- if you have custom storefront forms using Yup validation, replace imports of `yupResolver` from `@hookform/resolvers/yup` with `utils/forms/yupResolver` and pass the form values type as its generic argument, for example `yupResolver<LoginFormType>(schema)`
- if you have custom controlled form components, type their `name` property as `FieldPath<TFieldValues>` and their `control` property as `Control<TFieldValues, any, TTransformedValues>` to keep them compatible with the updated `react-hook-form`
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/c11fd2614e97a8ca94b88bf2ff7790ad932308e7) to update your project

#### Fix cart analytics for immediate quantity reversal ([#4708](https://github.com/shopsys/shopsys/pull/4708))

- if your project customizes cart quantity handling, update `CartListItem` to serialize absolute quantity mutations, retain the latest requested quantity while a mutation is in progress, and after every successful mutation store the returned `addProductResult.cartItem.quantity` as the last confirmed and submitted quantity
    - submit the queued quantity after the current mutation finishes, or synchronize the spinbox with the confirmed backend quantity when the queue is empty
    - when a mutation fails, restore the latest quantity confirmed by the backend
- update customizations of `useAddToCart` to keep the last confirmed quantity for each cart product and refresh it from every successful mutation response before calculating the next GTM quantity difference, so immediate quantity reversals emit matching `ec.add_to_cart` and `ec.remove_from_cart` events
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/537475cfeb669faa49fd3db167a638d6efecfe33) to update your project

#### Fix blog article navigation overlapping fixed header ([#4709](https://github.com/shopsys/shopsys/pull/4709))

- update the sticky sidebar in `BlogLayout` to use `--sticky-navigation-offset` for both its top position and available maximum height, so it stays below the fixed header
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/008bfd4ea6770c1d563c6e7218dfacc347653dd9) to update your project

#### Enable native media swiping in product galleries ([#4710](https://github.com/shopsys/shopsys/pull/4710))

- port the shared native carousel implementation from `storefront/components/Basic/MediaCarousel/`
    - add `MediaCarouselTrack.tsx`, `MediaCarouselNavigationButton.tsx`, and `MediaCarouselPagination.tsx`
    - update `ArrowIcon.tsx` and `PlayIcon.tsx` to the carousel-compatible 24 × 24 SVGs
- update the modal gallery from `storefront/components/Basic/ModalGallery/`
    - port the changes to `ModalGallery.tsx` and `ModalGalleryCarousel.tsx`
    - add `ModalGalleryTrack.tsx` and `ModalGalleryControls.tsx`
    - remove the obsolete `itemsRefs` prop from custom `ModalGalleryCarousel` usages
- add `1920` to `images.deviceSizes` in `storefront/next.config.js` and to `$allowedImageSizes` in `app/web/imageResizer.php`
    - keep both lists synchronized so fullscreen galleries can request a 1920 px image instead of being limited to 1440 px by the backend resizer
- port the product-detail gallery changes
    - update `ProductDetailGallery.tsx` and add the components from `storefront/components/Pages/ProductDetail/ProductDetailGallery/`
    - update `ProductDetailContent.tsx`, `ProductDetailMainVariantContent.tsx`, `ProductDetailElements.tsx`, `ProductDetailInfo.tsx`, and `ProductFlags.tsx`
    - if customized, stop passing `name`, `namePrefix`, and `nameSuffix` to `ProductDetailInfo` and render them through `ProductDetailTitle`
- port the product-list gallery changes
    - add `ProductListItemGallery.tsx` and `ProductListItemGalleryControls.tsx`
    - update `ProductListItem.tsx`, `ProductListItemImage.tsx`, `ProductsList.tsx`, and `ProductsListContent.tsx`
    - enable `isWithImageGallery` in category and search product listings
- update `CategoryProductsQuery.graphql`, `SearchProductsQuery.graphql`, and `SearchQuery.graphql` to request `imagesCount` in addition to the existing listed-product data
    - add `ProductListItemImagesQuery.graphql` for lazy loading of the complete `images` collection
    - keep this query paused initially and fetch it with the `cache-first` policy only after gallery interaction
    - do not add the complete image collection to product-list queries
- add the new gallery accessibility translations to every supported locale
- port the carousel focus-ring rule from `storefront/styles/base.css`
- update `SkeletonPageProductDetail.tsx` and `SkeletonPageProductDetailMainVariant.tsx`
- run `make generate-schema` to regenerate GraphQL documents and synchronized schema artifacts
- run `make generate-tailwind-for-admin` to regenerate `storefront/public/tailwind-for-admin/style.css`
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/2941727b470dcb7db93e585b1cc384c529bc15f9) to update your project

#### Storefront navigation now loads interaction features on demand ([#4714](https://github.com/shopsys/shopsys/pull/4714))

- update `DeferredNavigation` and its usage in `CommonLayout` to load the full desktop navigation only on desktop after the deferred render wave, keeping its JavaScript out of the mobile render path
- dynamically load the mobile menu, cart, and account implementations only after their first user interaction while keeping previously opened drawers mounted for closing animations
- keep the mobile search overlay in the synchronous render path so its input is focused directly from the originating user gesture, preserving the iOS virtual keyboard behavior
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/08dcd3459fa08b1486111201b25efa6ed8b40841) to update your project

#### Additional services offered with products ([#4716](https://github.com/shopsys/shopsys/pull/4716))

- **`GtmProductInterface` now has a required `productType: 'product'` field** — update your custom GTM mappers and test fixtures that construct `GtmProductInterface`, `GtmListedProductType`, or `GtmCartItemType` objects
- **`products` arrays in GTM ecommerce events changed type** from `GtmCartItemType[]` to `GtmCartProductOrServiceType[]` (union of `GtmCartItemType` and the new `GtmServiceCartItemType`), including `GtmCartInfoType.products` and the `getGtmCartViewEvent()` parameter — narrow items by `productType` before accessing product-only fields
- cart and order GTM events now also contain the chosen additional services as items with `productType: 'service'` (identified by uuid in `id`, with `sku`, `sourceProductIds`, and aggregated `quantity`) — update your GTM container/analytics processing to handle them
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/83ad8b643a8f02878e2348e92817c29c6e152dc3) to update your project

#### product expected restocking date ([#4715](https://github.com/shopsys/shopsys/pull/4715))

- **GTM data layer product `availability` attribute changed** - it now contains a language-independent slug (`in_stock`, `out_of_stock`, or `expected_restock`) instead of the localized availability name, and products awaiting restock also send `availability_date` (`YYYY-MM-DD`) - update your GTM container and analytics reports relying on the old values
- `getDeliveryMessage()` from `components/Pages/Order/TransportAndPayment/transportAndPaymentUtils.tsx` was removed - use the `useExpectedDeliveryDateMessage()` hook or the `ExpectedDeliveryDateInfo` component instead
- `isPacketeryTransport()` was moved from `utils/packetery` to `utils/transport` and now accepts `TypeTransportTypeEnum | undefined` instead of `string | undefined`
- `Transport.daysUntilDelivery` field is no longer available in the API - use `Transport.expectedDeliveryDate` (rendered by the `useExpectedDeliveryDateMessage()` hook or the `ExpectedDeliveryDateInfo` component) instead
- `Transport.isPersonalPickup` field is no longer available in the API - use `isPersonalPickupTransport(transport.transportTypeCode)` or `isPickupPlaceTransport(transport.transportTypeCode)` from `utils/transport` instead
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/6837d52933d8ee978a0bd05460fe99077d0c2bba) to update your project

#### links with target="\_blank" get rel="noopener" automatically ([#4732](https://github.com/shopsys/shopsys/pull/4732))

- `ExtendedNextLink` and `Link` add `rel="noopener"` to every link rendered with `target="_blank"`, merging it into an existing `rel` value
    - if your project renders links outside these two components, use `addRelNoopenerWhenTargetIsBlank()` from `utils/links` there as well
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/45a36b3a25273c27b47912169e8dc356424ab3d0) to update your project

#### Modernize and expand the demo storefront ([#4745](https://github.com/shopsys/shopsys/pull/4745))

- update custom button usages based on the new variants in `components/Forms/Button/Button`
    - replace `variant="transparent"` with `variant="inverted"` for outlined actions on dark backgrounds
    - replace previous light-background `variant="inverted"` usages with `variant="secondary"`
    - use `variant="tertiary"` for low-emphasis actions and `variant="danger"` for destructive actions
    - review custom `variant="secondary"` usages because the secondary variant is now outlined instead of filled
- update custom `components/Forms/Button/IconButton` usages
    - replace `buttonRef={buttonRef}` with `ref={buttonRef}`
    - use `iconClassName` for styles that should be applied to the nested SVG
- add `@radix-ui/react-tooltip` to custom storefront dependencies and wrap custom application trees in `TooltipProvider` from `components/Basic/Tooltip/Tooltip`, following `pages/_app.tsx`
- replace renamed USP components and icons:

| Previous import                         | Replacement                             |
| --------------------------------------- | --------------------------------------- |
| `components/Blocks/UpsList/UpsList`     | `components/Blocks/UspList/UspList`     |
| `components/Blocks/UpsList/UpsListItem` | `components/Blocks/UspList/UspListItem` |
| `components/Basic/Icon/UpsFirstIcon`    | `components/Basic/Icon/UspFirstIcon`    |
| `components/Basic/Icon/UpsSecondIcon`   | `components/Basic/Icon/UspSecondIcon`   |
| `components/Basic/Icon/UpsThirdIcon`    | `components/Basic/Icon/UspThirdIcon`    |
| `components/Basic/Icon/UpsFourthIcon`   | `components/Basic/Icon/UspFourthIcon`   |
| `components/Basic/Icon/UpsFifthIcon`    | `components/Basic/Icon/UspFifthIcon`    |

- replace removed icon imports:

| Removed import                                  | Replacement                                                                                                 |
| ----------------------------------------------- | ----------------------------------------------------------------------------------------------------------- |
| `components/Basic/Icon/BoxPackageHandIcon`      | `components/Basic/Icon/DeliveryAddressIcon`                                                                 |
| `components/Basic/Icon/CheckboxCheckedIcon`     | `components/Basic/Icon/CheckmarkDoneIcon`                                                                   |
| `components/Basic/Icon/CrossIcon`               | `components/Basic/Icon/WarningIcon` for error states or `components/Basic/Icon/CloseIcon` for close actions |
| `components/Basic/Icon/DocumentDeleteIcon`      | `components/Basic/Icon/WithdrawalIcon`                                                                      |
| `components/Basic/Icon/EditIcon`                | `components/Basic/Icon/UserEditIcon`                                                                        |
| `components/Basic/Icon/InfoInTriangleIcon`      | `components/Basic/Icon/WarningIcon`                                                                         |
| `components/Basic/Icon/MailSecondaryIcon`       | `components/Basic/Icon/MailIcon`                                                                            |
| `components/Basic/Icon/MarkerIcon`              | `components/Basic/Icon/StoreIcon`                                                                           |
| `components/Basic/Icon/PhoneSecondaryIcon`      | `components/Basic/Icon/PhoneIcon`                                                                           |
| `components/Basic/Icon/RemoveBoldIcon`          | `components/Basic/Icon/CloseIcon`                                                                           |
| `components/Basic/Icon/RemoveIcon`              | `components/Basic/Icon/CloseIcon`                                                                           |
| `components/Basic/Icon/SearchListIcon`          | `components/Basic/Icon/OrderIcon`                                                                           |
| `components/Basic/Icon/ThumbUp`                 | `components/Basic/Icon/ThumbUpIcon`                                                                         |
| `components/Basic/Icon/UserProfileCardsIcon`    | `components/Basic/Icon/ContactInformationsIcon`                                                             |
| `components/Basic/Icon/WarehouseBoxPackageIcon` | `components/Basic/Icon/BillingAddressIcon`                                                                  |

- remove custom imports of `components/Basic/Icon/EmptyCartIcon` and `components/Basic/Icon/TriangleIcon`, or replace them with an icon matching the custom semantic context
- stop rendering `components/Blocks/BlogSignpost/Children` directly; render category navigation through `components/Blocks/BlogSignpost/BlogSignpost` and adapt custom panel behavior to `BlogSignpostPanel` and `BlogSignpostCategoryLink`
- remove custom `components/Blocks/Popup/ProductComparePopup` integrations; use `utils/productLists/comparison/useComparison`, which now reports a successful addition with a toast
- update customized component calls:
    - pass `settings.mainBlogCategoryData.mainBlogCategoryName` as `blogName` to `components/Blocks/BlogPreview/BlogPreview`
    - replace `buttonVariant="inverted"` with `buttonVariant="secondary"` in `components/Blocks/Product/AddToCart` and `components/Blocks/Product/ProductAction`
    - replace the `title` prop with `description` in `components/Blocks/Popup/RemoveAllProductsPopup`
    - remove the `itemClassName` prop from `components/Blocks/SimpleNavigation/SimpleNavigation`
    - replace `isSimpleCard` with `size` and `visibleItemsConfig` in `components/Blocks/Skeleton/SkeletonModuleProductListItem`
    - replace `isWithSimpleCards` with `productItemProps` in `components/Blocks/Skeleton/SkeletonModuleProductSlider`
    - remove the `productName` prop from `components/Pages/ProductDetail/ProductDetailGallery/ProductDetailGalleryThumbnails`
    - replace `isArrowLeftShowed` and `isArrowRightShowed` with `shouldShowArrows` when consuming `utils/productLists/comparison/useComparisonTable`
- remove `privacyPolicy` from custom `NewsletterFormType` defaults, validation schema, and metadata
- set `width="xl"` explicitly on customized `components/Layout/Webline/Webline` usages that must retain the previous maximum content width
- replace custom `components/Blocks/Skeleton/SkeletonModuleHeadline` usages with `components/Basic/Skeleton/Skeleton` using dimensions appropriate for the surrounding page
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/1857fbbe0ec2d67015a03393a75297619ed42b13) to update your project

#### withdrawal from contract on guest orders requires email confirmation ([#4782](https://github.com/shopsys/shopsys/pull/4782))

- for guest orders the `OrderWithdrawalRequestMutation` no longer creates a confirmed withdrawal request — show an email-sent state on the withdrawal form page instead of redirecting to the success page, and handle the `order-withdrawal-already-requested` error returned while a confirmation email is already pending
- add the new `confirmed` field to `OrderWithdrawalRequestFragment` and show an awaiting-confirmation note on the order detail for unconfirmed requests
- add the confirmation page `pages/order-withdrawal-confirmation/[hash].tsx` that calls the new `ConfirmOrderWithdrawalRequestMutation` and redirects to the success page using the returned order `urlHash`, and register the `/order-withdrawal-confirmation/:hash` route in `config/routes.ts` for all locales
- map the new `order-withdrawal-confirmation-invalid` error code in `utils/errors/applicationErrors.ts`
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/0b16f1dc9c32d97ab3093592d1b7de5dcb19b01a) to update your project

#### Add product reviews ([#4747](https://github.com/shopsys/shopsys/pull/4747))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/469eee79ce0046e5cb757048a780462c1828a902) to update your project

#### Add product ratings to product lists, structured data and GTM ([#4775](https://github.com/shopsys/shopsys/pull/4775))

- if you use a custom `visibleItemsConfig` in product list components, add `reviews: true` to show the new review stars in your product tiles (only the predefined `largeItem` and `mediumItem` configs enable them by default)
- if you track products in GTM, product objects in the data layer now contain optional `reviewCount` and `reviewValue` fields — update your GTM container if you want to use them
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/d55e8bf6ef6d4a4f5fe52aaa31fd63871fee6d4d) to update your project

#### Fix navigation for articles of type link ([#4776](https://github.com/shopsys/shopsys/pull/4776))

- footer menu items for articles of type link (`ArticleLink`) are no longer client-side routed as article detail pages — `type="article"` is passed to `ExtendedNextLink` only for `ArticleSite` items, so the target URL goes through friendly URL resolution and resolves to the correct page type (product, category, ...)
- if your project customizes `FooterMenuItem` or renders `ArticleLink` items elsewhere with `type="article"`, apply the same change to avoid 500/404 errors when the link points to a non-article URL
- storefront middleware `handleSlugTypeQueryParam()` now receives the normalized `pathname` and skips the `slugType` rewrite for the error page route (`/404`), so a leftover `slugType` query parameter can no longer rewrite the error page back to a dynamic page (which previously caused a nonsensical `ArticleDetailQuery` with `urlSlug: "404"`); if you override the storefront middleware, port this guard
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/d5141a1380418b09982324f793ab4cd1558a58b6) to update your project

#### Fix duplicate watchdog button on unavailable product variants ([#4779](https://github.com/shopsys/shopsys/pull/4779))

- `ProductAction` from `components/Blocks/Product/ProductAction` renders `WatchDogButton` itself now - remove `WatchDogButton` from every place where you render it as a sibling of `ProductAction`, otherwise the button is rendered twice
- pass the new `isWatchdogButtonShownWithPurchaseAction` prop wherever the watchdog button has to stay next to the purchase action, the way `ProductDetailVariantsTable` does - without it `ProductAction` offers the watchdog button only when there is nothing to purchase, which is how product lists, the product comparison and the article product hero have always behaved
- `ProductAction` no longer accepts `buttonVariant` - the inverted look of the add to cart button follows from the watchdog button being rendered next to it
- `ProductAction` renders two sibling elements where the watchdog button is shown next to the purchase action - keep the layout classes on the parent element and make it a `flex flex-col gap-2` container
- `buttonSize` of `ProductAction` sizes the watchdog button as well and defaults to `medium`, so the button in the variants table is `large` instead of `xlarge` now - the watchdog button can no longer be sized differently than the purchase action next to it
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/f85f88bc522c4aba2ec5175d607b475ddee68351) to update your project

#### product detail: show delivery options ([#4780](https://github.com/shopsys/shopsys/pull/4780))

- replace usages of the removed components `ProductDetailAvailabilityPopup`, `ProductDetailAvailabilityList`, and `ProductVariantsAvailabilityPopup` with the new delivery options popup (`components/Blocks/Popup/DeliveryOptionsPopup/`, opened via `useOpenDeliveryOptionsPopup()`)
- component `TransportAndPaymentSelectItemLabel` was renamed to `TransportAndPaymentItemLabel` (`components/Pages/Order/TransportAndPayment/TransportAndPaymentSelect/TransportAndPaymentItemLabel.tsx`) — update your imports
- the `Product.storeAvailabilities` API field was removed — remove it from your GraphQL requests (the `graphql/requests/storeAvailabilities/` directory with `SimpleStoreAvailabilityFragment` was deleted), in `urql/cache/cacheExchange.ts` remove the `StoreAvailability` key and add `ProductDeliveryOption` and `ProductDeliveryStore` as unkeyed (`keyNull`) types, and regenerate the GraphQL schema and types
- register the new `transport-price-missing` user error code in `utils/errors/applicationErrors.ts` (returned by `Transport.price` for a cart exceeding the transport weight limit)
- a product limited to personal pickup now shows "Ready for pickup" instead of "Ready to ship" in its availability, and no readiness text at all when it is stocked only outside the stores — `getInStockAvailabilityDetails()` (`utils/product/getInStockAvailabilityDetails.ts`) got a new `isPersonalPickupOnly` parameter and the `ProductAvailability` component a matching optional prop; `isPersonalPickupOnly` was added to `ListedProductFragment`, `ProductDetailFragment`, and `CartItemFragment`, so regenerate the GraphQL types and pass the flag wherever you render `ProductAvailability` with a store count; add the new "Ready for pickup" translation to your locale files
- the visitor coordinates for sorting the stores by distance are now acquired by the new `useUserCoordinates()` hook (`utils/useUserCoordinates.ts`) inside `usePaginatedStoreConnection`, so the delivery options popup sorts the stores by distance too — `StoresWrapper` no longer asks the browser for the geolocation itself, only the map centering and the marker click override stayed there
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/e04b6644dccf5d3ee1c41179429e489fbc952c0d) to update your project

#### Add product review photos ([#4781](https://github.com/shopsys/shopsys/pull/4781))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/03404e4c190e650a99d4e5127e0bf51d2fd44767) to update your project

#### Validate the checkout email only when the customer leaves the field ([#4783](https://github.com/shopsys/shopsys/pull/4783))

- `useErrorPopup()` from `utils/forms/useErrorPopup` no longer accepts the `overrideVisibility` argument - remove it from your calls, so `useErrorPopup(methods, fields, undefined, gtmMessageOrigin)` becomes `useErrorPopup(methods, fields, gtmMessageOrigin)`
- an order contact e-mail restored from the local storage is no longer validated on page load, so an invalid one is reported only after the customer leaves the field
    - if your project asserts the former behaviour in acceptance tests, update them - no error is rendered right after the page loads anymore
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/6c7e524073c09e0139b32911f174aabc0dc39bb5) to update your project

#### Set auth cookies secure flag based on the domain protocol ([#4754](https://github.com/shopsys/shopsys/pull/4754))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/71d324777564797250da44970dd3df205bf54edb) to update your project

#### Keep authentication feedback across synchronization reloads ([#4786](https://github.com/shopsys/shopsys/pull/4786))

- replace custom usages of `createAuthLoadingSlice`, `defaultAuthLoadingState`, `AuthLoadingSlice`, `AuthLoadingStatus`, and `updateAuthLoadingState()` from `store/slices/createAuthLoadingSlice`
    - use `AuthNotification` from `types/auth` and `storeAuthNotification(domainId, notification)` from `utils/auth/authNotificationStorage`
    - replace `login-loading` with `login`
    - replace `login-loading-with-cart-modifications` with `login-with-cart-modifications`
    - replace `logout-loading` with `logout`
    - replace `registration-loading` with `registration`
    - replace `registration-loading-with-cart-modifications` with `registration-with-cart-modifications`
    - replace `{ authLoadingStatus: 'social-login-fail', socialNetworkType }` with `{ type: 'social-login-fail', socialNetworkType }`
- remove `authLoading` from custom `PersistStore` integrations and stop pausing cart and product list queries during authentication navigation
- replace `useAuthLoader()` in custom deferred `Loaders` with `useAuthStateSynchronization()` from `utils/auth/useAuthStateSynchronization`
- if you customize authentication navigation
    - use `performAuthHardNavigation()` from `utils/auth/performAuthHardNavigation` for login and logout instead of combining `router.replace()` with `router.reload()`
    - after registration, use `router.replace('/')` and call `performAuthHardNavigation('/')` only if the client-side navigation returns `false`
- if you customize authentication mutation cache updates, invalidate `cart`, `currentCustomerUser`, and `ProductPrice` after login, logout, registration, and registration by order
- if you customize `_app.tsx`, `AppPageContent`, or `DeferredToastContainer`, move `DeferredToastContainer` from `AppPageContent` into `PersistStoreProvider` in `_app.tsx` before `CurrentCustomerUserProvider`, and render `ToastContainerWithAuthNotifications` from `DeferredToastContainer`
- if you customize social login, validate the URL network value with `getAllowedSocialNetworkType()` before storing the notification
- if custom messages passed to `showMessage()`, `showErrorMessage()`, `showInfoMessage()`, or `showSuccessMessage()` contain HTML markup, replace it with plain text
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/a548b2f289c1daf2e657dda81853c32b7784c380) to update your project

#### Improved customer return from the payment gateway ([#4789](https://github.com/shopsys/shopsys/pull/4789))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/1351308c9faa8da0faf5cb3ad4c7e8e3aaa2e1c0) to update your project

#### Fix storefront animation lifecycle and accessibility ([#4791](https://github.com/shopsys/shopsys/pull/4791))

- replace custom imports and usages of `AnimateAppearSlideY` from `components/Basic/Animations/AnimateAppearSlideY`; the component was removed without a direct replacement, so preserve a project-local implementation if the same vertical enter and exit animation is still needed
- update custom usages of `useFilterShowLess()` from `components/Blocks/Product/Filter/utils/useFilterShowLess`: replace the removed `defaultOptions` return value by deriving displayed options from the input options and `isWithAllItemsShown`
- remove the `keyName` prop from custom usages of `FilterGroupContentItem` from `components/Blocks/Product/Filter/FilterElements`; to animate hidden filter items as one group, wrap them in `FilterGroupHiddenItems` and pass `keyName` to that wrapper
- update custom calls of `useSearchProductsData()` from `components/Pages/Search/searchUtils`: replace `useSearchProductsData(totalProductCount)` with `useSearchProductsData({ totalProductCount })`; the new options object also accepts `searchProductsDataFromMainQuery`
- rename the `isSearchFetching` return field to `isSearchPageFetching` in custom usages of `useSearchQuery()` from `components/Pages/Search/searchUtils`
- rename the `isSearchFetching` prop to `isSearchPageFetching` in custom usages of `SearchPageContent` from `components/Pages/Search/SearchPageContent`
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/075416114d727989d0872a34121b9972ab626da5) to update your project

#### Unify count badge appearance across the storefront ([#4792](https://github.com/shopsys/shopsys/pull/4792))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/e68fb6ba088aeef2446b96da7db17cb716e4864f) to update your project

#### Rate limit password recovery requests by IP and e-mail ([#4785](https://github.com/shopsys/shopsys/pull/4785))

- the `RequestPasswordRecovery` and `RecoverPassword` mutations can now fail with the `too-many-password-recovery-attempts` user error code - register it as a `flash-message` error in `utils/errors/applicationErrors.ts` and map it to the `Too many password recovery attempts. Try again later.` message in `utils/errors/errorMessageMapper.ts`
- add the `Too many password recovery attempts. Try again later.` translation to every locale supported by your storefront (`public/locales/*/common.json`)
- call `useErrorHandler()` with the mutation error in your own password recovery components (`components/Pages/Customer/ChangePassword/ResetPassword.tsx`) - without it the new error is swallowed and the customer sees nothing
- stop passing `customMessage` to `useErrorHandler` in your password recovery form (`components/Pages/ResetPassword/ResetPasswordContent.tsx`) - it replaces the message of every application error, so the rate limit message would never reach the user
    - `usePasswordResetFormMeta()` in `components/Pages/ResetPassword/passwordResetFormMeta.ts` no longer returns `messages.error`, so remove it from your overrides or drop the `Could not reset password` translation key if you do not use it elsewhere
- stop passing `customMessage` to `useErrorHandler` in your new password form (`components/Pages/NewPassword/NewPasswordContent.tsx`) for the same reason - `RecoverPassword` is rate limited as well, so the customer can hit the limit when setting the new password
    - `useRecoveryPasswordFormMeta()` in `components/Pages/NewPassword/recoveryPasswordFormMeta.ts` no longer returns `messages.error`, so remove it from your overrides - keep the `An error occurred while changing your password` translation key, `components/Pages/Customer/ChangePassword/changePasswordFormMeta.ts` still uses it
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/2eb18435effa7e069cc29d60f275639f36e23d03) to update your project

#### Forms can send a honey pot field and report the exceeded submission rate limit ([#4800](https://github.com/shopsys/shopsys/pull/4800))

- move the wording of the application error codes you care about into `utils/errors/errorMessageMapper.ts`, `useErrorHandler` applies its `customMessage` option only to errors of the `default` type now and a registered code always shows its own message
- add the `too-many-form-submissions` code to your own copies of `utils/errors/applicationErrors.ts` and `utils/errors/errorMessageMapper.ts`, otherwise it falls through to the generic `Unknown error.` toast
- check the value returned by the `ContactForm` mutation in your own copy of `ContactContent`, a `false` result has to show the error message of the form instead of the success page
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/0c969d526bbaad3abc01ea336f186bab727d4363) to update your project

#### Errors from third-party browser scripts are no longer reported as storefront errors ([#4798](https://github.com/shopsys/shopsys/pull/4798))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/eaa81ff1b67a7073fbd6aa25e5ea08f140b9ad92) to update your project

#### Preserve login email while browsing the same page ([#4801](https://github.com/shopsys/shopsys/pull/4801))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/d3cf6e7621fdc72b290d15fc4752d67250210916) to update your project

#### Cancelled order no longer offers to repeat the online payment ([#4820](https://github.com/shopsys/shopsys/pull/4820))

- `Order` and `UpdatePaymentStatusResult` GraphQL types now provide the `isAwaitingPayment` field, use it in your custom components instead of computing `hasExternalPayment && !isPaid && !hasPaymentInProcess`, otherwise cancelled orders keep offering the payment
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/ce198266b2005a10212cd30b60fde7bc1d394ce7) to update your project

#### Product listings now paginate by 30 products to fill the 5-column grid ([#4814](https://github.com/shopsys/shopsys/pull/4814))

- if you adopt the wider storefront with five products per row, review `DEFAULT_PAGE_SIZE` in `config/constants.ts` and use a value divisible by five, such as the new default of `30`
    - verify pagination and "Load more" at wide and narrower breakpoints, especially four-column grids where `30` does not fill complete rows
- set the default viewport in `cypress/cypress.config.ts` to `1600 × 720` to cover the five-column product grid at the `1560px` `xxl` breakpoint
    - when adopting this viewport change, regenerate and review the affected Cypress snapshots
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/b5462fc42a50bed309582eab555acdac2a3f5e0a) to update your project

#### Fix hydration mismatches in storefront links ([#4824](https://github.com/shopsys/shopsys/pull/4824))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/7f84dad179c9c1ffece30c4c4ba25d1d71c228bf) to update your project

#### Unify image ALT texts and fallback behavior ([#4825](https://github.com/shopsys/shopsys/pull/4825))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/7757c1f6818be51b2562d7b204877dd1b7d12d18) to update your project

#### Complete structured data for organizations, products and articles ([#4826](https://github.com/shopsys/shopsys/pull/4826))

- move `LogoMetadata` rendering from `Logo` to `pages/_app.tsx` inside `UrqlWrapper` and `DomainConfigProvider`, and move `BreadcrumbsMetadata` rendering from `Breadcrumbs` to `CommonLayout`; apply the same changes to custom layouts
- pass `isNoIndex` to `CommonLayout` on custom pages that render `MetaRobots` with `noindex`, and omit article structured data on those pages
- upload an organization logo in the SEO settings for each domain or preserve your custom logo fallback; `LogoMetadata` no longer uses `/images/logo.svg` automatically
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/406bdd60e04a9cc8c64204f905de117b3b3e3a07) to update your project

#### Redesign product comparison and preserve product order ([#4828](https://github.com/shopsys/shopsys/pull/4828))

- if you have customized consumers of `useComparisonTable` from `utils/productLists/comparison/useComparisonTable.tsx`, attach its returned `scrollRef` to the horizontally scrollable comparison viewport and call `calcMaxMarginLeft` from its `onScroll` handler
    - remove the negative `tableMarginLeft` margin from the main comparison table; the hook now uses native horizontal scrolling
- update customized `ProductComparisonHeadSticky` call sites to pass `productColumnWidth`, `viewportLeft`, and `viewportWidth` from `useComparisonTable` so the sticky header stays aligned with the comparison table
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/98c0b0273acf3bde3e06507a8b42bf64895e65f8) to update your project

#### Fix add-to-cart popup prices overflowing on small screens ([#4834](https://github.com/shopsys/shopsys/pull/4834))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/67cefde55bee823b3155068f2ae4793e0ba38a81) to update your project

#### Disable Luigi's Box for local Cypress storefront ([#4836](https://github.com/shopsys/shopsys/pull/4836))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/3ade370cbbbc8a59f2acfccf559dfb0426637f5a) to update your project

#### Prevent category submenu flicker on quick click ([#4837](https://github.com/shopsys/shopsys/pull/4837))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/1ddde2855c42462b048a3de3f5403a9bf60b42f6) to update your project

#### Keep mobile search visible above the virtual keyboard ([#4838](https://github.com/shopsys/shopsys/pull/4838))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/d35561d6d0bce11a172dbddf7fdd56552ecabeac) to update your project

#### Add display controls to wishlist ([#4843](https://github.com/shopsys/shopsys/pull/4843))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/7feb83681a9f8795f500cf14f10759c788b01931) to update your project

#### Improve storefront accessibility for navigation and product content ([#4844](https://github.com/shopsys/shopsys/pull/4844))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/693ee19b158475bfa43f679758b468f2b4143baa) to update your project

#### SEO attributes are unified across all entities ([#4845](https://github.com/shopsys/shopsys/pull/4845))

- the `seoTitle`, `seoMetaDescription`, and `seoH1` GraphQL fields of products, categories, brands, blog categories, blog articles, and articles, and the `title`, `metaDescription`, and `canonicalUrl` fields of the SEO page were replaced by the `seo` field, use the new `SeoAttributesFragment` in your fragments and read `entity.seo.title`, `entity.seo.metaDescription`, and `entity.seo.h1`
- the API returns the SEO attributes exactly as set in the administration, the fallbacks of the `<head>` tags are resolved by `useSeo()`: pass `seo={entity.seo}` together with `defaultTitle={entity.name}` and `defaultDescription={entity.description}` (HTML is turned into a plain text truncated to 160 characters) to `CommonLayout`; the H1 fallback `entity.seo.h1 || entity.name` stays in the page content
- `settings.seo.title` and `settings.seo.metaDescription` are no longer available, the homepage title and description come from the SEO page with the slug `/` like on any other static page
- the `title` and `description` props of `CommonLayout` were renamed to `defaultTitle` and `defaultDescription`, the `MetaRobots` component was removed, pass the `defaultMetaRobots` prop to `CommonLayout` instead (`CustomerLayout` and `OrderLayout` set `noindex` for all their pages)
- `useSeoTitleWithPagination()` was renamed to `useHeadingWithPagination(heading, totalCount, pageSize?)` and no longer takes the entity name as a fallback, pass the resolved H1 (`entity.seo.h1 || entity.name`); the title of a paginated listing gets its page suffix from the new `paginationTotalCount` and `paginationPageSize` props of `CommonLayout`
- the `title` prop of `CollapsibleDescriptionWithImage` and `BlogCategoryHeader` was renamed to `heading`, `ProductDetailTitle` was replaced by `ProductDetailHeading` with the `heading` prop
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/935c80a0efe0db9d2caf1a695bca24caca4eed58) to update your project
- see also project-base diff of [#4924](https://github.com/shopsys/shopsys/pull/4924) with additional fixes

#### Highlight last used login and refine storefront interactions ([#4847](https://github.com/shopsys/shopsys/pull/4847))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/8fb9578efc5ced07d33b258576d6d9df2536d5ba) to update your project

#### Electronic gift vouchers have no delivery options on the product detail ([#4869](https://github.com/shopsys/shopsys/pull/4869))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/878d0b8b6cfd395ef9a5dfb92924059fe8b89755) to update your project

#### storefront passes order urlHash as ownership proof when repeating an order ([#4874](https://github.com/shopsys/shopsys/pull/4874))

- pass `orderUrlHash` to `AddOrderItemsToCartMutation` input when repeating an order from the order detail (`useAddOrderItemsToCart`, `MergeCartsPopup`, `OrderDetailBasicInfo`), so that anonymous customers can still repeat an order from the public order detail page
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/16c23dd955afc286702ad49ff2c6c79edbbeb9f4) to update your project

#### demo data: variants of Television Philips now have clearly distinguishable names ([#4886](https://github.com/shopsys/shopsys/pull/4886))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/abefb18fe88d5618dfef880d7cb13c403ad1b5ea) to update your project

#### Improve storefront loading and shared component dependencies ([#4888](https://github.com/shopsys/shopsys/pull/4888))

- If your project should keep an image next to the category or brand description, preserve that rendering in your project when applying the changes. The shared header no longer displays these images: `CollapsibleDescriptionWithImage` is replaced by `CollapsibleDescription`, which does not accept image props.
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/ccf2201cbae65cd875f1e031349370123147804a) to update your project

#### Improve checkout selections, product flags and skeleton transitions ([#4891](https://github.com/shopsys/shopsys/pull/4891))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/91c6c706251ff3be1d84209415b34f6757aab500) to update your project

#### Show transports directly when only one group is available ([#4897](https://github.com/shopsys/shopsys/pull/4897))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/e282a4952456d9a2b7b78763060b1579707d46cd) to update your project

#### Fix storefront product media and order controls ([#4898](https://github.com/shopsys/shopsys/pull/4898))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/276c137b7901a7045915453cbd910b2dda7b0ce0) to update your project

#### Send gift data to GTM ([#4900](https://github.com/shopsys/shopsys/pull/4900))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/d80da9ff47eb14814df14dd2ca078ea2086ae378) to update your project

#### Add promotion tracking events ([#4901](https://github.com/shopsys/shopsys/pull/4901))

- if your GTM configuration or analytics reports filter homepage recommended products by `listName`, update the expected value from `homepage promo products` to `Homepage - recommended products`
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/4b6c5aee8f052d2917414135ce7be72ada88afae) to update your project

#### Fix GTM cart data after item removal ([#4902](https://github.com/shopsys/shopsys/pull/4902))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/0ebbcadfb08b5bbad682af7c3431f918839acc40) to update your project

#### Handle null document access ([#4904](https://github.com/shopsys/shopsys/pull/4904))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/76ec03f0db0b03cd685c032fc476e0e71f1b0e0d) to update your project

#### Validate total attachment size ([#4905](https://github.com/shopsys/shopsys/pull/4905))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/07097c9a08220c375d27a44fcffa47491a924e01) to update your project

#### Prevent adverts flashing during article navigation ([#4906](https://github.com/shopsys/shopsys/pull/4906))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/bc133d108136d97c9be7fd84a864b12e480716a5) to update your project

#### Show bank transfer instructions after changing GoPay payment ([#4907](https://github.com/shopsys/shopsys/pull/4907))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/6f4fb01bcb9325b5a3f2cc21d6d8fd37c2568f6d) to update your project

#### Update storefront dependencies ([#4929](https://github.com/shopsys/shopsys/pull/4929))

- if you use Sentry, review `sentryDataCollection` against your project's data collection requirements before deployment; explicitly enable only necessary additional categories instead of relying on the Sentry 11 defaults
- before deploying, set `SENTRY_REPLAYS_ENABLE=0` in every environment, including CI/CD variable overrides; Sentry SDK 11.1.0 Replay recordings can still contain sensitive URLs despite masking, and the default `.env` value does not override deployment configuration
    - verify that Replay is disabled in the deployed storefront and no Replay recordings are sent
    - keep Replay disabled until an SDK update has been verified against actual outgoing payloads, including initial page URLs, navigation URLs, and links containing sensitive query parameters or capability hashes in their paths; upgrading the SDK alone is not sufficient to re-enable Replay
- if you use Sentry, after applying the project changes, verify a production build with your actual Sentry upload configuration and send a controlled test error from the deployed storefront
    - confirm that Sentry resolves the error to the original source and that no `*.js.map` files remain under `.next/static/` in the deployment artifact
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/66694f0150731ec8e67e74e055c041fee9f35fc0) to update your project

#### Simplify storefront GraphQL requests and reduce overfetching ([#4914](https://github.com/shopsys/shopsys/pull/4914))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/610206ce67e349e0fc5b658a1f79a5d9dc53937a) to update your project

#### Fix Redis connection leak when storefront SSR throws ([#4915](https://github.com/shopsys/shopsys/pull/4915))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/3352c3c26841a19c452e2ba4a2edeaf10335a74a) to update your project

#### Fix storefront build failing on extensionless Google Fonts URLs ([#4920](https://github.com/shopsys/shopsys/pull/4920))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/68fe9ceef90997c8cb786eaf501fa5b8473bbd86) to update your project
- the patch is bound to `next@15.5.19`; if your project uses a different `next` version, regenerate the patch for it with `pnpm patch next@<version>`, or drop it once [vercel/next.js#99114](https://github.com/vercel/next.js/issues/99114) is fixed in your version

#### Show gallery titles and item counters instead of image ALT captions ([#4922](https://github.com/shopsys/shopsys/pull/4922))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/4c394fdfdfaaac58204c8d96534ae93b12065d07) to update your project

#### Improve storefront test reliability and coverage ownership ([#4925](https://github.com/shopsys/shopsys/pull/4925))

- after applying the project changes, rebuild the Cypress image with `docker compose build cypress`; if your local Cypress container uses an existing `node_modules` volume, refresh it with `docker compose run --rm --no-deps --entrypoint npm cypress ci`
- review visual differences after switching to Chrome for Testing and regenerate only references whose new appearance is expected, using the same browser and environment for generation and comparison
- if your project needs functional-only acceptance runs during UI development, use `SKIP_SNAPSHOTS=1` with a regression Makefile command or set the GitLab pipeline variable to `"1"`; leave it at `0` for visual verification, and do not combine it with reference generation; follow the [Cypress guide](https://docs.shopsys.com/en/latest/storefront/cypress/#running-functional-checks-without-visual-snapshots) for commands and CI configuration
- keep the `apply_code_to_cart` rate-limiter override restricted to isolated Cypress CI backends on GitHub and GitLab; if you test promo-code request throttling, run those checks separately with the override disabled, and never mount it in review or live deployments
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/7a191c9f52e6ba88678292024861d6712d41c7bd) to update your project

#### Open Graph data now fall back to organization details and use a dedicated image preset ([#4927](https://github.com/shopsys/shopsys/pull/4927))

- the `ogImageUrlDefault` prop of `CommonLayout` and `SeoMeta` was replaced by `ogImage` of type `TypeImageFragment` - pass the image fragment (e.g. `product?.images[0]`) instead of its URL in your custom pages, the `og` preset of the image resizer and the image alternative text are applied by `useSeo`
- the `metatagSiteName` translation was removed, `og:site_name` is now taken from the organization name in the administration SEO settings - remove the key from `public/locales/*/common.json` of your custom locales
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/a8adaf50be74e997e63778d40d63fa1db5cddf9a) to update your project

#### Take product data in Frontend API always from Elasticsearch ([#4933](https://github.com/shopsys/shopsys/pull/4933))

- `OrderItem.product` and `ComplaintItem.product` are `null` also for products that are hidden for the current customer, not only for deleted ones, so render order and complaint items in your own components from the data of the item (`name`, `catnum`, `unitPrice`, `totalPrice`) and use the product only for its image and link (see `OrderItemProducts` and `OrderConfirmationProducts`)
- the `product-price-missing` error code is no longer returned by the API, remove it from your error handling
- the `notAddedProducts` field of `multipleAddedProductModifications` was replaced by `notAddedProductNames`, which lists also the products that are no longer available, so list the products not added when repeating an order from this field (see `CartModificationsFragment` and `useAddOrderItemsToCart`)
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/401a317ced73d2eaab3be44961cdbb9f9a5b4d4a) to update your project

#### Preserve original storefront exceptions in Sentry ([#4943](https://github.com/shopsys/shopsys/pull/4943))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/a8791b8ae6f0b9d2916a6690981692a78bf269c8) to update your project

#### Stop reporting missing blog categories to Sentry ([#4944](https://github.com/shopsys/shopsys/pull/4944))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/fb1aceb477614c46f06388557bfc44037cd2024c) to update your project

#### Prevent vertical scrolling in homepage benefits and blog sections ([#4951](https://github.com/shopsys/shopsys/pull/4951))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/b75d75f52aa7a7a26a2891d807432960e9cb6df2) to update your project

#### Prevent unverified JWT claims from selecting cache buckets ([#4952](https://github.com/shopsys/shopsys/pull/4952))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/95b1533ebf5a88db0fb95d541836d8b0020b57d6) to update your project

#### Hide watchdogs for products excluded from sale ([#4956](https://github.com/shopsys/shopsys/pull/4956))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/0ead9fe7c38c58ec0ffa151db8a3c4011d103b6b) to update your project

#### Add pagination to the wishlist ([#4967](https://github.com/shopsys/shopsys/pull/4967))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/f30b07651f879834b5cfc5f0855a090e8c386d0d) to update your project

#### Prevent customer managers from granting stronger role groups ([#4975](https://github.com/shopsys/shopsys/pull/4975))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/c4409f72b4b8615cdae89fea1552842f43db3327) to update your project

#### Preserve original storefront errors and request context in Sentry ([#4997](https://github.com/shopsys/shopsys/pull/4997))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/857731d771c6490b0fc42bf1685b6af15c2ae33f) to update your project

#### Show how many products a filter option adds to the result ([#4998](https://github.com/shopsys/shopsys/pull/4998))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/74ca6fe4760d04e7fa19603f04795ea347d0a4cb) to update your project

#### Do not report expected login failures to Sentry ([#4999](https://github.com/shopsys/shopsys/pull/4999))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/405db0b000f9ed3143323d4a476b93cbc65ae916) to update your project

#### Keep shared storefront Redis connections alive while idle ([#5005](https://github.com/shopsys/shopsys/pull/5005))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/769f037bbdaa087bc1ec0903d60f620f9b3dc0c2) to update your project

#### Fix uploaded photo preview alignment ([#5007](https://github.com/shopsys/shopsys/pull/5007))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/8509d77bdb5e3f523a21043375be0ef394eb63d4) to update your project

#### Upgrade storefront URQL and GraphQL tooling ([#5008](https://github.com/shopsys/shopsys/pull/5008))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/dcef00c2e964c64fdbc3fd4091a1e584e3a3a2f5) to update your project

#### Fix Sentry URL privacy test for single-domain projects ([#5014](https://github.com/shopsys/shopsys/pull/5014))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/296dafb6f6ea6c1cf0133df976903aa153777598) to update your project

<!-- storefrontNotes -->
