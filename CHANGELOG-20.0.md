# Changelog for 20.0.x

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
- [shopsys/mcp](https://github.com/shopsys/mcp)
- [shopsys/mcp-attributes](https://github.com/shopsys/mcp-attributes)
- [shopsys/administration](https://github.com/shopsys/administration)
- [shopsys/maker](https://github.com/shopsys/maker)
- [shopsys/cli](https://github.com/shopsys/cli)

Packages are formatted by release version.
You can see all the changes done to the package that you carry about with this tree.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/) and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html) as explained in the [Backward Compatibility Promise](https://docs.shopsys.com/en/latest/contributing/backward-compatibility-promise/).

<!-- Add generated changelog below this line -->

## [v20.0.0](https://github.com/shopsys/shopsys/compare/v19.0.0...v20.0.0) (2026-11-10)

<!-- Release notes generated using configuration in .github/release.yml at 20.0 -->

## What's Changed

### :sparkles: Enhancements and features

- Add Zbozi.cz category to GTM page metadata by @chlebektomas in https://github.com/shopsys/shopsys/pull/4600
- Unify order confirmation and payment return flow by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4642
- Add heading anchor navigation to blog articles by @chlebektomas in https://github.com/shopsys/shopsys/pull/4638
- Cron monitoring by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4654
- Add indexed data diagnostics to admin edit pages by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4644
- Added form for customer questions about products to product detail by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4661
- Improve storefront accessibility for screen readers by @chlebektomas in https://github.com/shopsys/shopsys/pull/4635
- Allow quantity changes from product cards by @chlebektomas in https://github.com/shopsys/shopsys/pull/4632
- Allow restricting products to personal pickup only by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4671
- Add blog article authors and unify the admin single-value picker by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4682
- Highlight a single recommended product in articles by @chlebektomas in https://github.com/shopsys/shopsys/pull/4696
- Store filter improvement by @RostislavKreisinger in https://github.com/shopsys/shopsys/pull/4650
- Product expected restocking date and order delivery calculation by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4715
- Add product reviews by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4747
- Show delivery options on product detail pages by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4780
- Add product ratings to product lists, structured data and GTM by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4775
- Quick order status filters on the admin order list by @fujta in https://github.com/shopsys/shopsys/pull/4803
- Customers can attach photos to product reviews by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4781
- Additional services offered with products by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4716
- Add display controls to wishlist by @chlebektomas in https://github.com/shopsys/shopsys/pull/4843
- Improved customer return from the payment gateway by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4789
- Admin cron overview shows the error message of a failed cron run by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4867
- Make templates and parameters of CRUD actions configurable by @henzigo in https://github.com/shopsys/shopsys/pull/4795
- Guest order withdrawal now requires email confirmation by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4782
- Redesign product comparison and preserve product order by @chlebektomas in https://github.com/shopsys/shopsys/pull/4828
- Unify SEO attributes across all entities by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4845
- Complete structured data for organizations, products and articles by @chlebektomas in https://github.com/shopsys/shopsys/pull/4826
- Make image metadata stripping explicit and configurable by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4916
- Show info message instead of delivery options popup for Electronic gift vouchers on the product detail by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4869
- Add text search to transfer issues overview by @pk16011990 in https://github.com/shopsys/shopsys/pull/4731
- Price change messages now also cover changes of the price without VAT by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4892
- Complete OpenGraph data for social network sharing by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4927
- Add "save and stay" button to admin edit pages by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4941
- Log changes of transports, payments, customers and administrators by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4939
- Add flash message history to administration by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4961
- [CRUD] Create and edit actions by @henzigo in https://github.com/shopsys/shopsys/pull/4584
- Unify CRUD page titles and add datagrid drag-and-drop reordering by @henzigo in https://github.com/shopsys/shopsys/pull/4672
- Validate the checkout email only when the customer leaves the field by @fujta in https://github.com/shopsys/shopsys/pull/4783
- Gift vouchers – purchase, generation, redemption by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4695
- Add custom image support to Extended SEO categories by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4816
- Quick approval of pending product reviews in administration by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4830
- Open frontend product detail link from admin product list in a new tab by @pk16011990 in https://github.com/shopsys/shopsys/pull/4725
- Datagrid fields can combine properties, be restricted by role and set a column class by @henzigo in https://github.com/shopsys/shopsys/pull/4909
- Improve storefront accessibility for navigation and product content by @chlebektomas in https://github.com/shopsys/shopsys/pull/4844
- Add promotion tracking events by @chlebektomas in https://github.com/shopsys/shopsys/pull/4901
- Allow free transport promo code on printed gift vouchers by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4928
- Add customer account order and complaint filtering by @chlebektomas in https://github.com/shopsys/shopsys/pull/4641
- Add grouped transport selection to checkout by @chlebektomas in https://github.com/shopsys/shopsys/pull/4662
- Enable native media swiping in product galleries by @chlebektomas in https://github.com/shopsys/shopsys/pull/4710

### :bug: Bug Fixes

- Keep Next cache writable in production storefront image by @chlebektomas in https://github.com/shopsys/shopsys/pull/4629
- Allow disabling MCP in production by omitting database credentials by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4640
- Fix storefront domain resolution behind proxies by @chlebektomas in https://github.com/shopsys/shopsys/pull/4630
- Stop reporting 405 MethodNotAllowedHttpException to Sentry by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4646
- MCP: prevent reading hidden columns via subselects by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4653
- Domain URL replacement now skips mapped superclasses by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4655
- Clarify VAT labels in promo code limits by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4648
- Fix Biome checks for backend SCSS by @chlebektomas in https://github.com/shopsys/shopsys/pull/4670
- Make Zbozi tests independent of domain configuration by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4677
- Fix test-send of administrator password-reset email template by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4687
- Refresh project-base Symfony recipe lock entries by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4692
- Fix expanding lazily loaded branches in admin tree selection by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4701
- Fix cart analytics for immediate quantity reversal by @chlebektomas in https://github.com/shopsys/shopsys/pull/4708
- Fix blog article navigation overlapping fixed header by @chlebektomas in https://github.com/shopsys/shopsys/pull/4709
- Remove no longer necessary uploaded files structure migration by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4711
- Fix admin assets build failing with webpack 5.109.0 by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4720
- MCP OAuth client registrations now survive deploys and cache cleaning by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4719
- Improve order detail interactions in administration by @chlebektomas in https://github.com/shopsys/shopsys/pull/4713
- Remove unsupported customer-file visibility migration by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4718
- Allow B2B user managers to access customer management by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4697
- Upload validation errors now identify the invalid file by @pk16011990 in https://github.com/shopsys/shopsys/pull/4721
- Upgrade symfony/mcp-bundle to ^0.12 to fix MCP session save error by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4749
- Fix navigation for articles of type link by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4776
- Set auth cookies secure flag based on the domain protocol by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4754
- Fix standalone PHPStan builds of framework and project-base by @ShopsysBot in https://github.com/shopsys/shopsys/pull/4811
- Keep authentication feedback across synchronization reloads by @chlebektomas in https://github.com/shopsys/shopsys/pull/4786
- Preserve login email while browsing the same page by @chlebektomas in https://github.com/shopsys/shopsys/pull/4801
- Settings query no longer fails on domains without a product review policy article by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4822
- Link type articles no longer get a friendly URL by @fujta in https://github.com/shopsys/shopsys/pull/4805
- Mass generated promo code batch now downloads as a CSV file by @fujta in https://github.com/shopsys/shopsys/pull/4790
- Fix storefront animation lifecycle and accessibility by @chlebektomas in https://github.com/shopsys/shopsys/pull/4791
- Prevent category submenu flicker on quick click by @chlebektomas in https://github.com/shopsys/shopsys/pull/4837
- Fix add-to-cart popup prices overflowing on small screens by @chlebektomas in https://github.com/shopsys/shopsys/pull/4834
- Keep mobile search visible above the virtual keyboard by @chlebektomas in https://github.com/shopsys/shopsys/pull/4838
- Fix hydration mismatches in storefront links by @chlebektomas in https://github.com/shopsys/shopsys/pull/4824
- Gotenberg service is now part of project-base docker-compose files by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4852
- Keep the default admin locale set by shopsys/cli after yaml-standards fix by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4859
- Stop reporting 410 GoneHttpException and 403 AccessDeniedHttpException to Sentry by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4858
- Send delayed messages of successful handlers even when a sibling handler fails by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4823
- Fix combined article search failing on hits from a differently versioned index by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4861
- Fail clearly when a CRUD controller points to a missing menu section by @henzigo in https://github.com/shopsys/shopsys/pull/4851
- Saving an entity from a stale browser tab no longer breaks images and files by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4866
- Negated filters on nullable columns now keep rows with empty values by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4854
- Webalize uploaded file names to prevent issues with special characters by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4812
- VAT rate deletion now runs synchronously within the request by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4815
- Add missing storefront translations by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4881
- Fix local phpstan failing on the Postgres data directory by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4883
- Maintenance page is now enabled during deploys with database migrations by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4880
- Fix storefront product media and order controls by @chlebektomas in https://github.com/shopsys/shopsys/pull/4898
- Show transports directly when only one group is available by @chlebektomas in https://github.com/shopsys/shopsys/pull/4897
- Administration JS translations are loaded before Stimulus controllers connect by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4889
- Show bank transfer instructions after changing GoPay payment by @chlebektomas in https://github.com/shopsys/shopsys/pull/4907
- Default pricing group can no longer be deleted without a replacement by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4833
- Fix GTM cart data after item removal by @chlebektomas in https://github.com/shopsys/shopsys/pull/4902
- Unify image ALT texts and fallback behavior by @chlebektomas in https://github.com/shopsys/shopsys/pull/4825
- Fix advanced search losing a condition when adding one after a search by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4873
- Registration from the order thank-you page now sends the registration email by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4871
- Registration via social network no longer fails when the profile has no name by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4877
- Doctrine profiler collects query backtraces again by @henzigo in https://github.com/shopsys/shopsys/pull/4912
- Twig filter cropZeros accepts numeric and null values by @pk16011990 in https://github.com/shopsys/shopsys/pull/4730
- Handle null window in storefront client detection by @chlebektomas in https://github.com/shopsys/shopsys/pull/4904
- Fix storefront build failing on extensionless Google Fonts URLs by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4920
- Fix Redis connection leak when storefront SSR throws by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4915
- Prevent oversized attachment uploads by @chlebektomas in https://github.com/shopsys/shopsys/pull/4905
- Fix block insertion at the end of email templates by @chlebektomas in https://github.com/shopsys/shopsys/pull/4919
- Send gift data to GTM by @chlebektomas in https://github.com/shopsys/shopsys/pull/4900
- Prevent deleting required advert images without replacement by @chlebektomas in https://github.com/shopsys/shopsys/pull/4921
- Prevent vertical scrolling in homepage benefits and blog sections by @chlebektomas in https://github.com/shopsys/shopsys/pull/4951
- Stop reporting missing blog categories to Sentry by @chlebektomas in https://github.com/shopsys/shopsys/pull/4944
- Complaints can no longer be created for deleted orders by @basicvector in https://github.com/shopsys/shopsys/pull/4948
- Preserve original storefront exceptions in Sentry by @chlebektomas in https://github.com/shopsys/shopsys/pull/4943
- Admin product list price follows the default administration currency by @pk16011990 in https://github.com/shopsys/shopsys/pull/4738
- Luigi's Box articles now keep the order from Luigi's Box by @basicvector in https://github.com/shopsys/shopsys/pull/4903
- Delete watchdogs of the removed company user instead of the owner's by @basicvector in https://github.com/shopsys/shopsys/pull/4930
- Delete watchdogs only on the domain of the deleted customer user by @basicvector in https://github.com/shopsys/shopsys/pull/4931
- Flag categories in Frontend API are no longer limited to 10 by @basicvector in https://github.com/shopsys/shopsys/pull/4946
- Complaint cannot be created for an electronic gift voucher via API by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4949
- Hide watchdogs for products excluded from sale by @chlebektomas in https://github.com/shopsys/shopsys/pull/4956
- Prevent customer managers from granting stronger role groups by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4975
- Fix uploaded photo preview alignment by @chlebektomas in https://github.com/shopsys/shopsys/pull/5007
- Add pagination to the wishlist by @chlebektomas in https://github.com/shopsys/shopsys/pull/4967
- Require images in admin forms also on edit pages by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4981
- Fix missing Bootstrap variables in production admin styles by @grossmannmartin in https://github.com/shopsys/shopsys/pull/5011
- Preserve original storefront errors and request context in Sentry by @chlebektomas in https://github.com/shopsys/shopsys/pull/4997
- Fix Sentry URL privacy test for single-domain projects by @chlebektomas in https://github.com/shopsys/shopsys/pull/5014
- Show how many products a filter option adds to the result by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4998
- Do not report expected login failures to Sentry by @chlebektomas in https://github.com/shopsys/shopsys/pull/4999
- Keep shared storefront Redis connections alive while idle by @chlebektomas in https://github.com/shopsys/shopsys/pull/5005
- Fix Luigi's Box secondary search results by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4652
- Fix minor issues in administration by @pk16011990 in https://github.com/shopsys/shopsys/pull/4681
- Add missing translations of 2FA texts in administrator detail by @pk16011990 in https://github.com/shopsys/shopsys/pull/4723
- Set max upload size in Elfinder and disable chunked uploads by @pk16011990 in https://github.com/shopsys/shopsys/pull/4724
- Prevent installation of doctrine/orm 3.6.8 that breaks migrations by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4753
- Stop reporting third-party browser script errors as storefront errors by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4798
- Main friendly URL is no longer changed when entity is renamed by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4810
- Translate admin form labels that fell back to humanized field names by @pk16011990 in https://github.com/shopsys/shopsys/pull/4817
- Add missing recommender client identifiers for brand, article and blog category pages by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4819
- Fix duplicate watchdog button on unavailable product variants by @fujta in https://github.com/shopsys/shopsys/pull/4779
- Cancelled order no longer offers to repeat the online payment by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4820
- Login rate limit is no longer exhausted by successful logins by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4860
- Disabled ProductsType no longer renders editing controls by @fujta in https://github.com/shopsys/shopsys/pull/4804
- Prevent adverts flashing during article navigation by @chlebektomas in https://github.com/shopsys/shopsys/pull/4906
- Manually set URL of a programmatically created entity is now its main URL by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4855
- Fix SEO pages migration failing on slugs used by other SEO pages by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4924
- Customer role group form shows individual permissions as granted when the all scope is checked by @pk16011990 in https://github.com/shopsys/shopsys/pull/4739
- The robots.txt file is now available through the CDN by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4940
- Clear promoted products cache when promoted products change by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4960
- Order items in Frontend API no longer include items of deleted orders by @basicvector in https://github.com/shopsys/shopsys/pull/4947
- Deduplicate parallel refresh token requests by @chlebektomas in https://github.com/shopsys/shopsys/pull/4674
- Correct Slovak promo code validation messages by @chlebektomas in https://github.com/shopsys/shopsys/pull/4839
- Match wishlist and comparison counts to visible products by @chlebektomas in https://github.com/shopsys/shopsys/pull/4957

### :hammer: Developer experience and refactoring

- Improve GraphQL query performance with batch loading and Redis caching by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4561
- Frontend API now accepts slug queries with a leading slash by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4649
- Add make targets to switch the whole application between dev and production by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4667
- Stop monorepo npm from creating project-base vendor by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4691
- Add disabled state support to tree selection form fields by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4703
- Releaser: add major/minor branch checklist step by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4651
- Add missing factory for AdministratorRoleGroupData by @pk16011990 in https://github.com/shopsys/shopsys/pull/4722
- Remove unused Heureka widget setting and fix disabling the API key by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4752
- Automatic AI fixer of standards on CI by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4755
- Add domain controls to CRUD lists by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4741
- Deduplicate product recalculation messages in web tests by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4797
- Enable dead code detection and remove dead code by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4744
- Link order number to order detail in the payment failed text by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4807
- Remove dead lorem ipsum overwrite flow from the article demo fixture by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4787
- VAT selects in admin now show the rate percentage by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4813
- Improve custom actions and custom routes in CRUD controllers by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4794
- Dumped translation PO files now end with a single newline by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4835
- PHPStan now understands Webmozart Assert calls in CRUD handlers by @henzigo in https://github.com/shopsys/shopsys/pull/4842
- Sanitize, validate and default entity properties with property hooks by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4821
- Elasticsearch profiler now displays bulk (NDJSON) request bodies by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4788
- GoPay download exception is now captured to Sentry with more details by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4864
- Fix logging of edits on entities with a plain Loggable attribute by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4882
- Show change history of loggable entities on CRUD edit pages by @henzigo in https://github.com/shopsys/shopsys/pull/4850
- Stop logging client-safe Frontend API errors as critical by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4887
- CRUD handler and hook interfaces receive the entity as Presentable by @henzigo in https://github.com/shopsys/shopsys/pull/4893
- Clarify warehouses and stores in demo data and administration by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4908
- Make similar demo products clearly distinguishable by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4886
- Detect the operating system in the installation script by @pk16011990 in https://github.com/shopsys/shopsys/pull/4918
- IndexRepository find methods return null instead of throwing exception by @pk16011990 in https://github.com/shopsys/shopsys/pull/4726
- Replace Webpack Encore with Rsbuild and Symfony Reprise by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4818
- Load product parameter values only for the requested locale by @pk16011990 in https://github.com/shopsys/shopsys/pull/4729
- Simplify storefront GraphQL requests and reduce overfetching by @chlebektomas in https://github.com/shopsys/shopsys/pull/4914
- Improve storefront loading and shared component dependencies by @chlebektomas in https://github.com/shopsys/shopsys/pull/4888
- Take product data in Frontend API always from Elasticsearch by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4933
- Rewrite easy controllers to crud controller by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4848
- Added new admin accounts to allow pair testing by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4976
- Improve storefront test reliability and coverage ownership by @chlebektomas in https://github.com/shopsys/shopsys/pull/4925
- Unified admin collections by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4979
- Upgrade storefront URQL and GraphQL tooling by @chlebektomas in https://github.com/shopsys/shopsys/pull/5008
- Package manifests now resolve internal dev dependencies by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4679
- Fix coding standard violations by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4686
- Upgrade Storefront test runtime dependencies by @chlebektomas in https://github.com/shopsys/shopsys/pull/4684
- Add AI agent guidance into project-base and shorten monorepo AGENTS.md by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4700
- Add /adhoc-pr skill for publishing ad-hoc work end-to-end by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4702
- Force installing friendsofsymfony/ckeditor-bundle from source by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4707
- Upgrade storefront form dependencies by @chlebektomas in https://github.com/shopsys/shopsys/pull/4706
- No longer force installing friendsofsymfony/ckeditor-bundle from source by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4742
- Add pr-comments skill for processing PR review threads with an agent by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4751
- Add find-jira-task-by-pr agent skill by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4759
- Add on-demand PR rebase with autosquash via GitHub Actions by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4768
- Pin @tabler/core to version 1.4.0 by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4771
- Allow doctrine/orm 3.6.8 now that symfony/doctrine-bridge is fixed by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4772
- Add AI acceptance criteria check running Playwright against the review environment by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4769
- Blackout the embedded map in the article detail visual test by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4773
- Remove Selenium/Codeception acceptance tests and their infrastructure by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4784
- Prevent AI security review from exhausting its turn limit by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4796
- Skip AI review stages for Dependabot pull requests by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4793
- Clean up dead code leftovers and unsafe subresponse redirect cast by @pk16011990 in https://github.com/shopsys/shopsys/pull/4806
- Adapt to doctrine/orm 3.7 with SortDirection enum ordering by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4827
- Fix AI checks failing on bot-triggered pull request runs by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4829
- Datagrid default order now uses the native SortDirection enum by @henzigo in https://github.com/shopsys/shopsys/pull/4840
- Add agent skill for creating admin CRUD controllers by @henzigo in https://github.com/shopsys/shopsys/pull/4841
- AI standards autofix runs only on demand via /standards-autofix PR comment by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4846
- Add sentry-issue skill for investigating Sentry-born Jira issues by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4865
- Add AI security audit of merge request diffs to GitLab CI by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4872
- Fix coding standards by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4926
- Remove unused GTM settings from homepage banners by @pk16011990 in https://github.com/shopsys/shopsys/pull/4736
- Removed obsolete ProductSourceEqualityTest by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4992

### :book: Documentation

- Document how to add images to an entity by @pk16011990 in https://github.com/shopsys/shopsys/pull/4746
- Document how to create a project from a not-yet-released version by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4862
- Fix rendering of fenced code blocks in lists and admonitions and of blockquotes in docs by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4863
- Document CSRF protection for admin actions by @grossmannmartin with @Copilot in https://github.com/shopsys/shopsys/pull/4619
- Documented RabbitMQ as a required service by @pk16011990 in https://github.com/shopsys/shopsys/pull/4982

### :art: Design & appearance

- Improve administration order detail editing by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4637
- Add desktop sticky storefront navigation by @chlebektomas in https://github.com/shopsys/shopsys/pull/4676
- Add grid and list views to storefront product listings by @chlebektomas in https://github.com/shopsys/shopsys/pull/4704
- Modernize and expand the demo storefront by @chlebektomas in https://github.com/shopsys/shopsys/pull/4745
- Product listings now paginate by 30 products to fill the 5-column grid by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4814
- Unify count badge appearance across the storefront by @chlebektomas in https://github.com/shopsys/shopsys/pull/4792
- Additional service VAT selects in admin now use VatChoiceType by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4853
- Add QR placeholder hint and centered QR payment card for bank transfer by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4868
- Highlight last used login and refine storefront interactions by @chlebektomas in https://github.com/shopsys/shopsys/pull/4847
- Display product name prefix and suffix in admin variants and product picker by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4870
- Fix long cron names overflowing admin toast messages by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4945
- Improve checkout selections, product flags and skeleton transitions by @chlebektomas in https://github.com/shopsys/shopsys/pull/4891
- Show gallery titles and item counters instead of image ALT captions by @chlebektomas in https://github.com/shopsys/shopsys/pull/4922
- Show help of admin collections and localized fields under the label by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4986
- Upgrade Tabler in administration to 1.7.4 by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4983
- Storefront: redesign MinimalErrorContent to match brand theming by @JanMolcik in https://github.com/shopsys/shopsys/pull/4487
- Redesign storefront navigation and mobile controls by @chlebektomas in https://github.com/shopsys/shopsys/pull/4657

### :rocket: Performance

- Improve storefront initial runtime performance by @chlebektomas in https://github.com/shopsys/shopsys/pull/4675
- Storefront navigation now loads interaction features on demand by @chlebektomas in https://github.com/shopsys/shopsys/pull/4714
- Cart stores only the date of its last activity by @henzigo in https://github.com/shopsys/shopsys/pull/4932
- Remove N+1 database queries from the cart in Frontend API by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4937
- FE API: product filter options are batch loaded by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4938
- Batch load Category and BlogCategory breadcrumbs in the Frontend API by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4934
- Batch load BlogCategory fields in the Frontend API by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4950
- Batch load Flag fields in the Frontend API by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4994
- Cart and order processing performance optimizations by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4890

### :cloud: Infrastructure

- Upload Storefront source maps to Sentry by @chlebektomas in https://github.com/shopsys/shopsys/pull/4620
- GitHub Actions: use `xargs -r` to safely handle empty input in workflows by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4643
- GitHub Actions: set `persist-credentials` to false and remove redundant git config unsetting steps by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4645
- Link Sentry storefront traces with backend requests by @chlebektomas in https://github.com/shopsys/shopsys/pull/4621
- Prevent simplified review deployment volume race by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4680
- Added GitHub Actions security audit using Claude Code for each PR by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4683
- Fix GitLab storefront checks on current Node runtime by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4690
- Block merging pull requests with fixup commits by @pk16011990 in https://github.com/shopsys/shopsys/pull/4728
- Configure Dependabot for GitHub Actions by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4758
- Disable Luigi's Box for local Cypress storefront by @chlebektomas in https://github.com/shopsys/shopsys/pull/4836
- Disable Sentry cron monitoring in review environments by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4875
- Idle messenger worker no longer opens Redis connections just to close them by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4876
- Verify 20.0 and split repositories nightly with an automatic fix PR by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4757
- Bump the github-actions group across 1 directory with 5 updates by @dependabot[bot] in https://github.com/shopsys/shopsys/pull/4774
- Bump mikefarah/yq from 4.53.3 to 4.53.6 in the github-actions group across 1 directory by @dependabot[bot] in https://github.com/shopsys/shopsys/pull/4799
- Whitelist PageSpeed.ONE IP for HTTP auth on deployed environments by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4884
- Review environments are deployed only for non-draft pull requests and by several runners by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4895
- Review teardown no longer floods Sentry with AMQP connection errors by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4879
- Bump mikefarah/yq from 4.53.6 to 4.54.1 in the github-actions group across 1 directory by @dependabot[bot] in https://github.com/shopsys/shopsys/pull/4923
- Docker build checks CI of the split repositories and project-base runs Cypress tests in parallel groups by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4896
- Fix split repositories CI: dead code, mcp runner, single-domain test by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4936
- Run review crons only when scheduled from administration by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4942
- Install PHP image extensions via PIE instead of PECL by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4963
- Give each review environment its own Redis by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4954
- Fix pushing to split repositories by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4968
- Review URLs are added to the PR description via actions/github-script by @TomasLudvik in https://github.com/shopsys/shopsys/pull/5006

### :warning: Security

- Upgrade backend npm dependencies by @chlebektomas in https://github.com/shopsys/shopsys/pull/4614
- Limit GraphQL batch request operations by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4656
- Fix trusted proxies configuration by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4663
- Admin routes without access rules are denied by default by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4666
- Use constant-time comparison for password reset tokens by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4698
- Quote generated SQL identifiers and literals by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4699
- Payment GraphQL operations now require proof of order ownership by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4693
- Nginx: uploaded files now download as attachments by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4660
- Protect storefront refresh tokens with HttpOnly cookies by @chlebektomas in https://github.com/shopsys/shopsys/pull/4705
- Links with target attribute get rel="noopener" automatically by @pk16011990 in https://github.com/shopsys/shopsys/pull/4732
- Protect entity log change set against XSS via translation parameters by @fujta in https://github.com/shopsys/shopsys/pull/4778
- Protect CSV exports against formula injection by @fujta in https://github.com/shopsys/shopsys/pull/4777
- Rate limit password recovery requests by IP and e-mail by @fujta in https://github.com/shopsys/shopsys/pull/4785
- Protect public form mutations against spambots by @fujta in https://github.com/shopsys/shopsys/pull/4800
- Repeat order and payment price now require proof of order ownership by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4874
- Prevent unverified JWT claims from selecting cache buckets by @chlebektomas in https://github.com/shopsys/shopsys/pull/4952
- Disable session upload progress in PHP image by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4962
- Update storefront dependencies by @chlebektomas in https://github.com/shopsys/shopsys/pull/4929
- MCP: SQL queries run against database views with exposed columns only by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4984
- Restrict file manager to WYSIWYG directory and editing administrators by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4953
- Escape stored values rendered raw in administration by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4965
- MCP: database role check and schema generation fixed after the database views by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/5004
- Storefront audit now passes with fewer overrides by @chlebektomas in https://github.com/shopsys/shopsys/pull/4658
- Admin templates now use protectedUrl() for CSRF-protected links by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4856
- Upgrade npm and Node.js in PHP image to fix reported vulnerabilities by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4964
- Limit GraphQL query depth by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4664
- Count nested fields in GraphQL pagination complexity by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4665

**Full Changelog**: https://github.com/shopsys/shopsys/compare/v19.0.0...v20.0.0
