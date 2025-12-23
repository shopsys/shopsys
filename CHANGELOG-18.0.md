# Changelog for 18.0.x

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

<!-- Release notes generated using configuration in .github/release.yml at 18.0 -->

## What's Changed

### :sparkles: Enhancements and features

- Added multiselect component by @chlebektomas in https://github.com/shopsys/shopsys/pull/4152
- Updated form validation by @chlebektomas in https://github.com/shopsys/shopsys/pull/4181
- Added better accessibility for banner slider by @chlebektomas in https://github.com/shopsys/shopsys/pull/4158
- Introduced factories for all implementations of DataSourceInterface + QueryBuilderDataSources now order textual columns with correct collation by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4135
- Add special article page (about) GTM type by @JanMolcik in https://github.com/shopsys/shopsys/pull/4184
- Prevent exceeding available stock for products with negative stock disabled by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4173
- Enable domain configuration with path fragment by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4113
- Autocomplete favorites and improved UX by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4215
- Product gift by @RostislavKreisinger in https://github.com/shopsys/shopsys/pull/4193
- Accessibility alerts by @chlebektomas in https://github.com/shopsys/shopsys/pull/4162
- Promotion X + Y for free by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4194
- Product selling from is prefilled in admin in new product page by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4234
- Order withdrawal from contract by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4246
- Added uuid search to all quick searches by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4321
- Improved withdrawal deadline calculation by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4317
- Products temporarily out of stock now show proper info message by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4329
- Allow display discounts breakdown in cart by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4314
- QR payment by @RostislavKreisinger in https://github.com/shopsys/shopsys/pull/4195
- Banner skeleton by @chlebektomas in https://github.com/shopsys/shopsys/pull/4337

### :bug: Bug Fixes

- Fixed pagination scroll + added pagination to complaints by @chlebektomas in https://github.com/shopsys/shopsys/pull/4169
- Updated newsletter gtm by @chlebektomas in https://github.com/shopsys/shopsys/pull/4171
- Fix loading overlay position in cart by @JanMolcik in https://github.com/shopsys/shopsys/pull/4155
- Updated transport and payment select for single option by @chlebektomas in https://github.com/shopsys/shopsys/pull/4177
- Updated scroll above heading in orders and complaints in customer layout by @chlebektomas in https://github.com/shopsys/shopsys/pull/4189
- Fix announcing page title for screen readers by @JanMolcik in https://github.com/shopsys/shopsys/pull/4166
- Updated filled email in order after log by @chlebektomas in https://github.com/shopsys/shopsys/pull/4199
- Fixed not working elfinder with new route access by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4200
- Fixed missing access token by @chlebektomas in https://github.com/shopsys/shopsys/pull/4203
- Fixed ssr recommended products identifier by @chlebektomas in https://github.com/shopsys/shopsys/pull/4210
- Contact info form error-visibility context by @JanMolcik in https://github.com/shopsys/shopsys/pull/4202
- Admin: fix templates for single domain app by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4219
- Translations: do not extract empty strings anymore by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4229
- Fix payment transactions ordering in Order entity by @tommyguccis in https://github.com/shopsys/shopsys/pull/4205
- Admin forms: add some missing labels by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4228
- Product elastic index: fix default value for X+Y promotion data by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4245
- Fixed tag input in grapesjs product block by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4237
- Added broadcast channel after finishing order by @chlebektomas in https://github.com/shopsys/shopsys/pull/4211
- SF: getBasePathWithLocale now requires context instead of locale by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4249
- Fixed styles by @chlebektomas in https://github.com/shopsys/shopsys/pull/4253
- Ensure AccessChecker::hasPermission works properly with single roles by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4257
- Fixed superadmin behavior by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4238
- Updated access token refresh when expired by @chlebektomas in https://github.com/shopsys/shopsys/pull/4258
- Customer now has properly resolved roles no matter current context by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4262
- Fixed improper frontend api role section class placement by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4268
- Moved frontend api roles to framework bundle by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4269
- Unified opening image gallery through the portal to prevent CSS stacking context issues by @chlebektomas in https://github.com/shopsys/shopsys/pull/4273
- Fixes of domain URL with path by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4265
- Refactored auto-group orphaned fields to cards by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4287
- Coupon usage is now decreased only once per order by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4290
- Removed workaround for empty base_url by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4308
- Complaint and payment form is not marked as modified after load by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4293
- Calculated selling denied no longer uses product_visibility for its calculation and includes stocks by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4299
- Updated robots.txt to support locale-only domain configs by @chlebektomas in https://github.com/shopsys/shopsys/pull/4302
- Fixed grapesjs format helper by @chlebektomas in https://github.com/shopsys/shopsys/pull/4313
- Added ability to report hidden products by @chlebektomas in https://github.com/shopsys/shopsys/pull/4304
- Added onclick on tag for gtm events by @chlebektomas in https://github.com/shopsys/shopsys/pull/4322
- Updated setting for delivery addresses in edit profile and contact information by @chlebektomas in https://github.com/shopsys/shopsys/pull/4316
- Fix operationNameExchange to properly preserve existingFetchOptions by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4306
- Admin: promo code: added validation for remaining uses to be 0 or higher by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4315
- Fixed rendering DatePoint object in grid as date by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4330
- Fixed advanced search multiple ajax call by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4334
- Removed an unnecessary alert message by @chlebektomas in https://github.com/shopsys/shopsys/pull/4340
- Refactored `order-confirmation` page UI & UX after native browser back navigation from GoPay Gateway by @JanMolcik in https://github.com/shopsys/shopsys/pull/4214
- Fixed recalculation of deleted products by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4348
- Added 404 for not found category by @chlebektomas in https://github.com/shopsys/shopsys/pull/4344
- Fixed safari grapesjs map by @chlebektomas in https://github.com/shopsys/shopsys/pull/4198
- SF product filter choices: ensure parameter values are sorted alphabetically for the given locale by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4224
- Fix locale parameter in BlogArticleDetailFriendlyUrlDataProvider by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4307
- Blog friendly URL generation now skips translations with null names by @liborplucnarshopsys in https://github.com/shopsys/shopsys/pull/4309

### :hammer: Developer experience and refactoring

- Translated Cypress test on single domain by @chlebektomas in https://github.com/shopsys/shopsys/pull/4272
- Updated styleguide colors by @chlebektomas in https://github.com/shopsys/shopsys/pull/4154
- Improve annotations in (Image/UploadedFile)Config + Loader classes to fix phpstan errors by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4187
- Fixed warnings in storefront Dockerfile by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4188
- Added check to ensure that upgrade notes include correct PR number and link by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4197
- Accessibility namespace split by @JanMolcik in https://github.com/shopsys/shopsys/pull/4153
- Improve cypress gql error handling by @JanMolcik in https://github.com/shopsys/shopsys/pull/4220
- Updated add to cart accessibility for limited user by @chlebektomas in https://github.com/shopsys/shopsys/pull/4212
- Nginx improvements by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4204
- DX tweaks in administration by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4243
- Refactored middleware by @chlebektomas in https://github.com/shopsys/shopsys/pull/4242
- Replace `CsrfProtection` annotation with attribute by @henzigo in https://github.com/shopsys/shopsys/pull/4263
- Name is no longer mandatory for Category, Blog Category and Blog Article by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4209
- Only new manualInputPrice is now persisted by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4291
- Replaced direct usage of DateTime and DateTimeImmutable with Symfony/Clock by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4297
- Removed fragment-matcher which is an Apollo-only concept by @chlebektomas in https://github.com/shopsys/shopsys/pull/4323
- Added 10% discount promo code to demo data by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4318
- Replaced mutagen-compose with direct mutagen usage by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4331
- Improved install script to set correct version for mutagenio/sidecar image by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4336
- Remove dayjs dependency and replace with native Date methods by @JanMolcik in https://github.com/shopsys/shopsys/pull/4345
- [CRUD] Access control implementation by @henzigo in https://github.com/shopsys/shopsys/pull/4250
- Fix build with load_demo_data: false set for some domain by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4296

### :art: Design & appearance

- Administration overhaul by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3813
- Added product images to orders by @chlebektomas in https://github.com/shopsys/shopsys/pull/4213
- Added wysiwyg ck editor text styles by @chlebektomas in https://github.com/shopsys/shopsys/pull/4208
- Statuses of orders and complaints are now color coded by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4235
- Bestsellers now use same tree structure visual as category tree on categories page by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4231
- Removed unnecessary borders in quick search in tabs by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4233
- Minor fixes in elfinder in grapesjs by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4232
- Fixed group admin form fields in cards by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4261
- Update administration styles by @chlebektomas in https://github.com/shopsys/shopsys/pull/4260
- Update states after form submission by @chlebektomas in https://github.com/shopsys/shopsys/pull/4303
- Redesign contact information by @chlebektomas in https://github.com/shopsys/shopsys/pull/4221
- Reset password layout by @chlebektomas in https://github.com/shopsys/shopsys/pull/4254
- Comparison and wishlist tweaks by @chlebektomas in https://github.com/shopsys/shopsys/pull/4328
- Updated fixed bar on product detail by @chlebektomas in https://github.com/shopsys/shopsys/pull/4335
- Updated autocomplete popup accessibility and polished design by @chlebektomas in https://github.com/shopsys/shopsys/pull/4230
- Updated admin UI with responsive layout improvements by @chlebektomas in https://github.com/shopsys/shopsys/pull/4298

### :book: Documentation

- Add generate_upgrade_notes Claude slash command by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4218
- Added docs to correctly describe how to extend input validation in GraphQl types by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4292
- Fix license in README.md by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4301
- Add copy code functionality to code blocks in documentation by @JanMolcik in https://github.com/shopsys/shopsys/pull/4332

### :rocket: Performance Improvements

- Reduced data size transfer for product detail by @chlebektomas in https://github.com/shopsys/shopsys/pull/4324
- Optimized cart sync across tabs by @chlebektomas in https://github.com/shopsys/shopsys/pull/4327
- Order creation: email preparation is now asynchronous by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4266
- Improve LCP for Homepage by @henzigo in https://github.com/shopsys/shopsys/pull/3953

### :warning: Security

- Graphql queries are sent exclusively by POST by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4236
- Replaced jQuery UI with SortableJS by @chlebektomas in https://github.com/shopsys/shopsys/pull/4326

### :up: Dependencies

- Disallow installing doctrine-bundle in ">=2.17.0" version by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4222
- Composer: require doctrine-bundle ^2.18 by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4226
- Fixed npm versions compatibility by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4223
- Conflicted doctrine/orm 2.20.7 by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4252
- Upgrade rabbitMQ version by @henzigo in https://github.com/shopsys/shopsys/pull/4167
- Update BE Node version from 20 to 24 by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4281

### :cloud: Infrastructure

- Fixed build for branches with '/' in name by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4294
- Switch shopsys phpunit-injector fork download from vsc to package by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4300
- Fixed gitlab builds by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4244
- Added test_locale for gitlab cypress by @chlebektomas in https://github.com/shopsys/shopsys/pull/4333
- Added check for missing translations to gitlab build by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4172

## New Contributors

- @tommyguccis made their first contribution in https://github.com/shopsys/shopsys/pull/4205
- @liborplucnarshopsys made their first contribution in https://github.com/shopsys/shopsys/pull/4309

**Full Changelog**: https://github.com/shopsys/shopsys/compare/v17.0.1...v18.0.0
