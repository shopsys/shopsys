<!-- Release notes generated using configuration in .github/release.yml at 18.0 -->

## What's Changed
### :sparkles: Enhancements and features
* fixed pagination scroll + added pagination to complaints by @chlebektomas in https://github.com/shopsys/shopsys/pull/4169
* added multiselect component by @chlebektomas in https://github.com/shopsys/shopsys/pull/4152
* updated form validation by @chlebektomas in https://github.com/shopsys/shopsys/pull/4181
* added better accessibility for banner slider1 by @chlebektomas in https://github.com/shopsys/shopsys/pull/4158
* Introduced factories for all implementations of DataSourceInterface + QueryBuilderDataSources now order textual columns with correct collation by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4135
* Add special article page (about) GTM type by @JanMolcik in https://github.com/shopsys/shopsys/pull/4184
* prevent exceeding available stock for products with negative stock disabled by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4173
* enable domain configuration with path fragment by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4113
* Autocomplete favorites and improved UX by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4215
* Product gift by @RostislavKreisinger in https://github.com/shopsys/shopsys/pull/4193
* Tc-ssp-3487-accessibility-alerts by @chlebektomas in https://github.com/shopsys/shopsys/pull/4162
* Promotion X + Y for free by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4194
* product selling from is prefilled in admin in new product page by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4234
* redesign contact informations by @chlebektomas in https://github.com/shopsys/shopsys/pull/4221
* tc-ssp-3627-reset-password-layout by @chlebektomas in https://github.com/shopsys/shopsys/pull/4254
* order withdrawal from contract  by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4246
* Translated Cypress test on single domain by @chlebektomas in https://github.com/shopsys/shopsys/pull/4272
* reduced data size transfer for product detail by @chlebektomas in https://github.com/shopsys/shopsys/pull/4324
### :bug: Bug Fixes
* updated newsletter gtm by @chlebektomas in https://github.com/shopsys/shopsys/pull/4171
* Fix loading overlay position in cart by @JanMolcik in https://github.com/shopsys/shopsys/pull/4155
* mirror to gitlab is now initiated by github action by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4192
* updated transport and payment select for single option by @chlebektomas in https://github.com/shopsys/shopsys/pull/4177
* updated scroll above heading in orders and complaints in customer layout by @chlebektomas in https://github.com/shopsys/shopsys/pull/4189
* Fix announcing page title for screen readers by @JanMolcik in https://github.com/shopsys/shopsys/pull/4166
* updated filled email in order after log by @chlebektomas in https://github.com/shopsys/shopsys/pull/4199
* fixed not working elfinder with new route access by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4200
* fixed missing access token by @chlebektomas in https://github.com/shopsys/shopsys/pull/4203
* fixed ssr recommended products identifier by @chlebektomas in https://github.com/shopsys/shopsys/pull/4210
* Contact info form error-visibility context by @JanMolcik in https://github.com/shopsys/shopsys/pull/4202
* admin: fix templates for single domain app by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4219
* disallow installing doctrine-bundle in ">=2.17.0" version by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4222
* fixed npm versions compatibility by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4223
* translations: do not extract empty strings anymore by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4229
* Fix payment transactions ordering in Order entity by @tommyguccis in https://github.com/shopsys/shopsys/pull/4205
* updated autocomplete popup accessibility and polished design by @chlebektomas in https://github.com/shopsys/shopsys/pull/4230
* admin forms: add some missing labels by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4228
* fixed gitlab builds by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4244
* product elastic index: fix default value for X+Y promotion data by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4245
* fixed tag input in grapesjs product block by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4237
* added broadcast channel after finishing order by @chlebektomas in https://github.com/shopsys/shopsys/pull/4211
* conflicted doctrine/orm 2.20.7 by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4252
* SF: getBasePathWithLocale now requires context instead of locale by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4249
* fixed styles by @chlebektomas in https://github.com/shopsys/shopsys/pull/4253
* ensure AccessChecker::hasPermission works properly with single roles by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4257
* fixed superadmin behavior by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4238
* updated access token refresh when expired by @chlebektomas in https://github.com/shopsys/shopsys/pull/4258
* customer now has properly resolved roles no matter current context by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4262
* fixed improper frontend api role section class placement by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4268
* moved frontend api roles to framework bundle by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4269
* unified opening image gallery through the portal to prevent CSS stacking context issues1 by @chlebektomas in https://github.com/shopsys/shopsys/pull/4273
* fixes of domain URL with path by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4265
* refactored auto-group orphaned fields to cards by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4287
* coupon usage is now decreased only once per order by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4290
* removed workaround for empty base_url by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4308
* complaint and payment form is not marked as modified after load by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4293
* calculated selling denied no longer uses product_visibility for its calculation and includes stocks by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4299
* updated robots.txt to support locale-only domain configs by @chlebektomas in https://github.com/shopsys/shopsys/pull/4302
* fixed grapesjs format helper by @chlebektomas in https://github.com/shopsys/shopsys/pull/4313
* added ability to report hidden products by @chlebektomas in https://github.com/shopsys/shopsys/pull/4304
* added onclick on tag for gtm events by @chlebektomas in https://github.com/shopsys/shopsys/pull/4322
* updated setting for delivery addresses in edit profile and contact informations by @chlebektomas in https://github.com/shopsys/shopsys/pull/4316
### :hammer: Developer experience and refactoring
* updated styleguide colors by @chlebektomas in https://github.com/shopsys/shopsys/pull/4154
* improve annotations in (Image/UploadedFile)Config + Loader classes to fix phpstan errors by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4187
* fixed warnings in storefront Dockerfile by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4188
* added check to ensure that upgrade notes include correct PR number and link by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4197
* Accessibility namespace split by @JanMolcik in https://github.com/shopsys/shopsys/pull/4153
* Improve cypress gql error handling by @JanMolcik in https://github.com/shopsys/shopsys/pull/4220
* updated add to cart accessibility for limited user by @chlebektomas in https://github.com/shopsys/shopsys/pull/4212
* nginx improvements by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4204
* DX tweaks in administration by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4243
* refactored middleware by @chlebektomas in https://github.com/shopsys/shopsys/pull/4242
* Replace `CsrfProtection` annotation with attribute by @henzigo in https://github.com/shopsys/shopsys/pull/4263
* order creation: email preparation is now asynchronous by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4266
* Name is no longer mandatory for Category, Blog Category and Blog Article by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4209
* only new manualInputPrice is now persisted by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4291
* Replaced direct usage of DateTime and DateTimeImmutable with Symfony/Clock by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4297
* removed fragment-matcher which is an Apollo-only concept by @chlebektomas in https://github.com/shopsys/shopsys/pull/4323
### :book: Documentation
* add generate_upgrade_notes Claude slash command by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4218
* added docs to correctly describe how to extend input validation in GraphQl types by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4292
* fix license in README.md by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4301
### :art: Design & appearance
* Administration overhaul by @grossmannmartin in https://github.com/shopsys/shopsys/pull/3813
* added product images to orders by @chlebektomas in https://github.com/shopsys/shopsys/pull/4213
* added wysiwyg ck editor text styles1 by @chlebektomas in https://github.com/shopsys/shopsys/pull/4208
* Statuses of orders and complaints are now color coded by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4235
* bestsellers now use same tree structure visual as category tree on categories page by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4231
* removed unnecessary borders in quick search in tabs by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4233
* minor fixes in elfinder in grapesjs by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4232
* fixed group admin form fields in cards by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4261
* update administartion styles by @chlebektomas in https://github.com/shopsys/shopsys/pull/4260
* update states after form submissionm by @chlebektomas in https://github.com/shopsys/shopsys/pull/4303
### :cloud: Infrastructure
* fixed build for branches with '/' in name by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4294
* updated config files used on review server by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4310
* switch shopsys phpunit-injector fork download from vsc to package by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4300
### :warning: Security
* graphql queries are sent exclusively by POST by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4236
### :placard: Other Changes
* upgrade rabbitMQ version by @henzigo in https://github.com/shopsys/shopsys/pull/4167
* added check for missing translations to gitlab build by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4172
* fixed safari grapesjs map by @chlebektomas in https://github.com/shopsys/shopsys/pull/4198
* improve LCP for Homepage by @henzigo in https://github.com/shopsys/shopsys/pull/3953
* [SSP-3564] qr payment by @RostislavKreisinger in https://github.com/shopsys/shopsys/pull/4195
* composer: require doctrine-bundle ^2.18 by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4226
* added upgrade notes for administration overhaul by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4227
* SF product filter choices: ensure parameter values are sorted alphabetically for the given locale by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4224
* [CRUD] Access control implementation by @henzigo in https://github.com/shopsys/shopsys/pull/4250
* Update BE Node version from 20 to 24 by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4281
* Fix locale parameter in BlogArticleDetailFriendlyUrlDataProvider by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4307

## New Contributors
* @tommyguccis made their first contribution in https://github.com/shopsys/shopsys/pull/4205

**Full Changelog**: https://github.com/shopsys/shopsys/compare/v17.0.1...v18.0.0