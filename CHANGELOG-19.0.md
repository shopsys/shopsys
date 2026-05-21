# Changelog for 19.0.x

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

## v19.0.0 (2026-05-21)

<!-- Release notes generated using configuration in .github/release.yml at 19.0 -->

## What's Changed

### :sparkles: Enhancements and features

- enable GoPay notifications on http auth secured sites by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4372
- Parameter of type colour can have Image instead of RGB hex by @JanMolcik in https://github.com/shopsys/shopsys/pull/4325
- SEO audit by @chlebektomas in https://github.com/shopsys/shopsys/pull/4379
- demo data: all mail templates are sent for order status change by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4397
- Remove product detail tabs by @chlebektomas in https://github.com/shopsys/shopsys/pull/4384
- SF product detail - add parameter value color preview by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4382
- files picker tweaks by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4373
- Shopsys CLI: A new project bootstrapper by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4377
- Packeta widget now filters pickup points based on the configured country per domain by @chlebektomas in https://github.com/shopsys/shopsys/pull/4476
- [SSP-1684] Simplify category SEO creation by @machacjan in https://github.com/shopsys/shopsys/pull/4419
- [CRUD] Delete action by @henzigo in https://github.com/shopsys/shopsys/pull/4017
- [CRUD] Menu icon can be set for CrudController by @henzigo in https://github.com/shopsys/shopsys/pull/4529
- added new rounding to 0.05 for order total price by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4522
- Improve work with phone numbers by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4514
- Catalog page by @chlebektomas in https://github.com/shopsys/shopsys/pull/4378
- added GTM noscript iframe fallback by @chlebektomas in https://github.com/shopsys/shopsys/pull/4586
- storefront GTM page events now use updated metadata by @chlebektomas in https://github.com/shopsys/shopsys/pull/4591
- Add product ecommerce data to watchdog GTM event by @chlebektomas in https://github.com/shopsys/shopsys/pull/4598
- Autocomplete GTM reports found result counts by @chlebektomas in https://github.com/shopsys/shopsys/pull/4593
- Add wishlist GTM events by @chlebektomas in https://github.com/shopsys/shopsys/pull/4594
- create order GTM now reports submitted order data by @chlebektomas in https://github.com/shopsys/shopsys/pull/4602
- Add variant parameters to GTM cart products by @chlebektomas in https://github.com/shopsys/shopsys/pull/4597
- Add order withdrawal GTM event by @chlebektomas in https://github.com/shopsys/shopsys/pull/4603
- Add company identifiers to user entry GTM events by @chlebektomas in https://github.com/shopsys/shopsys/pull/4595
- Complete GTM user data from prioritized sources by @chlebektomas in https://github.com/shopsys/shopsys/pull/4599
- Add MCP server for AI-based read-only database exploration by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4564
- it is now possible to make a link to specific domain tab by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4525
- Heureka categories for SK domains by @sspooky13 in https://github.com/shopsys/shopsys/pull/4216
- [SSP-3443] Localize category SEO slugs by @machacjan in https://github.com/shopsys/shopsys/pull/4471
- [SSP-3229] Add SEO attributes as search keywords by @machacjan in https://github.com/shopsys/shopsys/pull/4413
- Uploaded files can be open in a browser by @malyMiso in https://github.com/shopsys/shopsys/pull/3589
- [SSP-3083] Add blog article status by @machacjan in https://github.com/shopsys/shopsys/pull/4490

### :bug: Bug Fixes

- ensure friendly url slug is always url-encoded by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4445
- loading button and polishing by @chlebektomas in https://github.com/shopsys/shopsys/pull/4352
- complaint cannot be created for orders created as "non-logged" customer by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4369
- fixed print domain after install on mac by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4383
- added possibility to resize image in email template by @chlebektomas in https://github.com/shopsys/shopsys/pull/4353
- fixed image height and Tailwind !important modifiers to suffix syntax by @chlebektomas in https://github.com/shopsys/shopsys/pull/4380
- fixed GoPay gateway visibility broken by incorrect router event signatures by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4387
- move a DB migration from project-base to the framework package by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4388
- fix gitlab failing cypress tests by @JanMolcik in https://github.com/shopsys/shopsys/pull/4391
- fixed php-fpm configuration by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4401
- filtered seo category for limited user by @chlebektomas in https://github.com/shopsys/shopsys/pull/4389
- Product detail tabs by @chlebektomas in https://github.com/shopsys/shopsys/pull/4410
- product recalculations now don't wait for full batch size, but process nearly immediately by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4427
- fixed add newly uploaded image in elfinder behavior by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4416
- fixed not proper login failed message in admin by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4449
- Product recalculation deduplication improvements by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4433
- GoPay payment status notify endpoint is now handled by backend instead of storefront by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4439
- withdrawal request email for customer is now sent only once by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4442
- fix price list import by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4444
- Mailer now supports Closures as variables by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4443
- admin: fixed product quick search by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4455
- hide complaint button when an order is withdrawn by @chlebektomas in https://github.com/shopsys/shopsys/pull/4436
- added the ability to tab through Tom Select in the administration by @chlebektomas in https://github.com/shopsys/shopsys/pull/4440
- updated admin category tree toggle button by @chlebektomas in https://github.com/shopsys/shopsys/pull/4438
- Admin: customer user form simplification by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4454
- added sanitization for draggable content in articles and blog articles by @chlebektomas in https://github.com/shopsys/shopsys/pull/4437
- project-base: fix composer patches paths by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4464
- added feed recovery for corrupted or nonexistent files during feed ge… by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4463
- autocomplete search popup no longer flashes on submit and search page now shows correct skeleton by @chlebektomas in https://github.com/shopsys/shopsys/pull/4467
- FE API: rollback mutation & discard async messages on error by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4479
- design: Tom-Select dropdown now renders above the fixed bottom bar by @chlebektomas in https://github.com/shopsys/shopsys/pull/4478
- transport and payment selection is now disabled during in-flight mutations by @chlebektomas in https://github.com/shopsys/shopsys/pull/4480
- complaint error by @chlebektomas in https://github.com/shopsys/shopsys/pull/4482
- fix admin router request matching by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4486
- autocomplete search now shows "no results" message when LuigisBox returns no results by @chlebektomas in https://github.com/shopsys/shopsys/pull/4477
- transport validation errors from last order restore now show as info toast instead of errormol by @chlebektomas in https://github.com/shopsys/shopsys/pull/4468
- product detail parameters aligment by @chlebektomas in https://github.com/shopsys/shopsys/pull/4496
- admin: Tom Select dropdown in modals now renders correctly by @chlebektomas in https://github.com/shopsys/shopsys/pull/4497
- useAddOrderItemsToCart: cart skeleton now shows during repeat order redirect by @chlebektomas in https://github.com/shopsys/shopsys/pull/4498
- OrderGoPayStatusUpdateCronModule improvements by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4451
- fix ckeditor links by @chlebektomas in https://github.com/shopsys/shopsys/pull/4502
- fix multiple TypeErrors by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4475
- Fix feed info to correctly handle only for current time option by @techi602 in https://github.com/shopsys/shopsys/pull/3511
- CKeditor fixes by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4506
- Add to cart race condition by @chlebektomas in https://github.com/shopsys/shopsys/pull/4501
- [SSP-3796] Fix product availability store count by @machacjan in https://github.com/shopsys/shopsys/pull/4418
- fixed shopsys/cli after removal of public runtime config by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4518
- Fixed demo payment transaction statuses to prevent cron errors by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4532
- admin: product edit TOC now use only card-title H3 to render items by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4528
- [GrapesJS] fix cross-domain template loading blocked by CSP by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4531
- Fix child form attr defaults being overridden in MultidomainType by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4544
- Fixed running console commands with --profile option by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4541
- Enforce unique parameter values at database level by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4536
- Add maps.googleapis.com to default CSP script-src by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4550
- Fix issues surfaced by a clean installation of the platform by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4519
- Fix last visited products showing fewer items when some are unavailable by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4549
- [CRUD] fix translations by @henzigo in https://github.com/shopsys/shopsys/pull/4527
- useErrorHandler now applies customMessage only for application toasts by @chlebektomas in https://github.com/shopsys/shopsys/pull/4557
- SEO category filters now keep latest state on rapid toggles by @chlebektomas in https://github.com/shopsys/shopsys/pull/4559
- removed heureka_category_id_seq that was kept by accident by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4565
- Preserve expired-token error code across backend and storefront by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4560
- fixed promo code limit/flags editing by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4562
- replaced missing php bin/console doctrine:query:sql with dbal:run-sql during deploy by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4572
- Fix cypress gitlab flow by @chlebektomas in https://github.com/shopsys/shopsys/pull/4580
- Cart popup polish by @chlebektomas in https://github.com/shopsys/shopsys/pull/4575
- fixed some next static files not being served in prod / devel by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4574
- admin: removed duplicate <li> wrapper from side menu items by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4588
- Fixed GrapesJS video preview in Safari by @chlebektomas in https://github.com/shopsys/shopsys/pull/4590
- fix articles ordering by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4582
- UserConsentForm: GTM consent update event now uses current form valuesvy by @chlebektomas in https://github.com/shopsys/shopsys/pull/4585
- Gift price is always entered with vat by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4583
- Fix annotation-fixer whitespace handling in generic types by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4596
- Replace duplicated category tree forms with generic TreeSelectionType by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4587
- admin: relative dates in cron list are now translatable by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4601
- Catalog page now shows categories independently of navigation by @chlebektomas in https://github.com/shopsys/shopsys/pull/4606
- Hide admin pages requiring parameters from autocomplete search by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4608
- Remove broken FrontendApi namespace sniffs by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4604
- Keep delivery addresses synced in checkout by @chlebektomas in https://github.com/shopsys/shopsys/pull/4612
- Fix language constants with boundary spaces by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4617
- Allow numbered street names in address forms by @chlebektomas in https://github.com/shopsys/shopsys/pull/4615
- Fix skeletons during browser history navigation by @chlebektomas in https://github.com/shopsys/shopsys/pull/4616
- ComplaintResolution: fix typo in property name by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4446
- add missing symfony security-core security-http composer dependencies by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4500
- VolumeDriver: use alias to `elFinderVolumeFlysystem` only if the alias does not exist by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4510
- fix packages builds by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4626
- add GitHub Actions checks for MCP packages by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4628
- DB migration: remove useless column comment by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4627

### :hammer: Developer experience and refactoring

- added updated packages by @chlebektomas in https://github.com/shopsys/shopsys/pull/4341
- refactoring - removed components, utils, hooks, types, imports, exports, dependencies by @chlebektomas in https://github.com/shopsys/shopsys/pull/4358
- Removed crypto-js by @chlebektomas in https://github.com/shopsys/shopsys/pull/4374
- Added knip by @chlebektomas in https://github.com/shopsys/shopsys/pull/4393
- Update storefront UI and Cypress support by @chlebektomas in https://github.com/shopsys/shopsys/pull/4492
- Storefront config now uses window.\_\_ENV instead of Next.js publicRuntimeConfig by @chlebektomas in https://github.com/shopsys/shopsys/pull/4493
- Forms refactor by @chlebektomas in https://github.com/shopsys/shopsys/pull/4503
- [SSP-1136] Refactor advanced search by @machacjan in https://github.com/shopsys/shopsys/pull/4422
- changed heureka key in data fixtures by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4339
- phpstan: keep symfony private-service ignores in monorepo config only by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4474
- drop BatchHandlerWithTimeLimitTrait by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4494
- move ProductEntityFieldMapper::getFlags() from project-base to the frontend-api package by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4509
- project-base single domain check now uses shopsys/cli to configure by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4534
- GetStoreTest: public holiday date is now computed under mocked clock by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4551
- Optimize Cypress failure artifacts by @chlebektomas in https://github.com/shopsys/shopsys/pull/4566
- Removed ReadyCategorySeoMix programatical dependency on $selectedCategorySeoMixCombinationJson by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4579
- DB migration: remove useless column comment by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4627
- createInstance() now serve only intended purpose by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4338
- improved product repository test by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4346
- removed shopsys naming by @chlebektomas in https://github.com/shopsys/shopsys/pull/4354
- build.xml: add "test-demo-data" phing target by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4371
- reverted movement of vendor and node_modules syncing from Mutagen to named volumes by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4381
- Doctrine annotations replaced by PHP attributes by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4395
- removed symfony/proxy-manager-bridge as its functionality is now implemented directly in Symfony by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4402
- Error handling refactoring by @JanMolcik in https://github.com/shopsys/shopsys/pull/4386
- [SSP-3759] Simplify Domain dependency by @machacjan in https://github.com/shopsys/shopsys/pull/4417
- Claude Code skills by @chlebektomas in https://github.com/shopsys/shopsys/pull/4412
- frontend api is now always enabled by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4423
- Updated constraint classes and usages for Symfony 7 compatibility by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4409
- remove unused ParameterWithValues class by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4441
- improved image twig template by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4453
- Removed lot of deprecations blocking upgrade to Symfony 7 by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4431
- Update to Symfony 7.4 by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4448
- updated hooks by @chlebektomas in https://github.com/shopsys/shopsys/pull/4434
- [SSP-1983] Move image config from YAML to attributes by @machacjan in https://github.com/shopsys/shopsys/pull/4421
- Update to PHP 8.5 by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4470
- performance update by @chlebektomas in https://github.com/shopsys/shopsys/pull/4460
- add reference.php to gitignore by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4489
- improved gift data fixtures so it is easier to test different scenarios by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4483
- replaced litipk/php-bignumbers with brick/math by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4484
- reworked cron runner by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4461
- tweak FilterQueryTest::testParameters() and testFlagBrand() by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3734
- fix annotations for getTranslations() method in all translatable entities by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3203
- removed dead code by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4515
- order relations that can influence order item price calculations are now stored during order creation by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4507
- Update to Doctrine ORM 3 and DBAL 4 by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4513
- Auto remove of stale annotations by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4526
- Cron now uses standard crontab syntax for specifying run intervals by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4523
- public files cleanup by @chlebektomas in https://github.com/shopsys/shopsys/pull/4539
- Symfony profiler: add Shopsys panel with domain and context details by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4545
- extracted protected methods from createOrderMutation() by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4563
- SSR and Error handling by @chlebektomas in https://github.com/shopsys/shopsys/pull/4512
- replaced ixdotai/smtp with maildev/maildev for better local DX by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4609
- added New Year's Day as closed day to data fixture by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4342
- upgrade symfony/doctrine-messenger by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4365
- enhance debug error messages & option to ignore them by type by @JanMolcik in https://github.com/shopsys/shopsys/pull/4356
- ProductTest tweaks by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4370
- replace mutagen ignore relative paths with absolute paths by @JanMolcik in https://github.com/shopsys/shopsys/pull/4403
- [SSP-3754] Fix annotations fixer for final, readonly, abstract classes by @machacjan in https://github.com/shopsys/shopsys/pull/4415
- improved product demo data + minor cleanup by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4408
- Update to React 19 by @chlebektomas in https://github.com/shopsys/shopsys/pull/4404
- update phpunit xml schema versions by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4435
- enabled new coding standards to enforce strict types by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4396
- removed unnecessary PGDATA variable by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4450
- ImageFacade and ImageRepository were strip of unnecessary code by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4456
- NotFound exceptions extends Symfony\Component\HttpKernel\Exception\NotFoundHttpException by @malyMiso in https://github.com/shopsys/shopsys/pull/2637
- phpstan: move particular file ignores from configs to inline suppressions by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4466
- claude: added test writing skill by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4465
- make AI docs more general by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4469
- remove obsolete Symfony type-info patch by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4485
- [SSP-3852] Refactor query builder joins by @machacjan in https://github.com/shopsys/shopsys/pull/4426
- Align WARP.md with AGENTS.md source of truth by @JanMolcik in https://github.com/shopsys/shopsys/pull/4488
- [SSP-3105] Unify top product fixtures between domains by @machacjan in https://github.com/shopsys/shopsys/pull/4472
- cypress envs and b2b guard by @chlebektomas in https://github.com/shopsys/shopsys/pull/4508
- recalculations: introduce constants for "all scopes" and "unscoped fields" by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4511
- [SSP-2159] Remove order transport and payment relations by @machacjan in https://github.com/shopsys/shopsys/pull/4473
- Improved error handling and logging in image resizer by @henzigo in https://github.com/shopsys/shopsys/pull/4504
- Add AI skill for generating business-focused PR descriptions by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4537
- bumped storefront dependencies by @chlebektomas in https://github.com/shopsys/shopsys/pull/4538
- Replace Eslint and Prettier with Biome by @chlebektomas in https://github.com/shopsys/shopsys/pull/4543
- improved skill for sprint summary to include necessary information how to generate csv data and added screenshot generation by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4578
- [SSP-3753] Move default stock to domain by @machacjan in https://github.com/shopsys/shopsys/pull/4517
- improved skill for sprint summary to MCP usage and more rules for picking relevant tasks by @chlebektomas in https://github.com/shopsys/shopsys/pull/4605
- few simple tweaks and fixes by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4364
- broaden protected-visibility rule to all Shopsys code by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4618

### :book: Documentation

- improve local CDN testing instructions by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4499
- MkDocs: Force live-reload locally by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4407
- docs: fix broken links and update config by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4411
- docs: add info about FilterQuery::MAXIMUM_REASONABLE_AGGREGATION_BUCKET_COUNT into FAQ section by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/3733
- Update PHP_CodeSniffer repository link by @rodrigoprimo in https://github.com/shopsys/shopsys/pull/4516
- Add project documentation for working with Sentry and MCP servers by @chlebektomas in https://github.com/shopsys/shopsys/pull/4577
- @rodrigoprimo made their first contribution in https://github.com/shopsys/shopsys/pull/4516

### :art: Design & appearance

- Refactor `ManageCustomerUserPopup` by @JanMolcik in https://github.com/shopsys/shopsys/pull/4349
- add graceful degradation for unsupported browsers by @JanMolcik in https://github.com/shopsys/shopsys/pull/4390
- admin: pinnable sidebar menu items with drag-and-drop reordering by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4533
- admin sidebar redesign by @pk16011990 in https://github.com/shopsys/shopsys/pull/4573

### :rocket: Performance

- remove `data-tid` attributes from production build by @JanMolcik in https://github.com/shopsys/shopsys/pull/4359
- Fix keys for React lists & add missing api unique identifiers by @JanMolcik in https://github.com/shopsys/shopsys/pull/4368
- eliminated n+1 query during cron run by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4548
- Update administrator activity once per request by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4613
- Cache storefront translations across route changes by @chlebektomas in https://github.com/shopsys/shopsys/pull/4610

### :cloud: Infrastructure

- review server now can use sentry by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4524
- added GitHub Action for sending notifications to Slack when merging starts, ends or fails by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4375
- upgrade postgreSQL version to 18.x by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4149
- removed Apiary.io functionality as it is being shutdown by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4462
- enable launching application without Sentry and CDN by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4458
- migrate stale config from probot-stale to GitHub Actions workflow by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4505
- cli build-phar workflow: set target_commitish for release by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4520
- nginx: avoid storefront fallback for missing content locale overrides by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4581
- review server: crons are now ran in different times per instance so server has no big load at one time by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4589
- finalize bumps and stabilize Docker/runtime by @JanMolcik in https://github.com/shopsys/shopsys/pull/4481
- bump github actions versions by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4535
- fix packages builds by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4626
- Set Corepack cache path in storefront Docker images by @chlebektomas in https://github.com/shopsys/shopsys/pull/4625

### :warning: Security

- updated nginx to latest version by @TomasLudvik in https://github.com/shopsys/shopsys/pull/4399
- security headers by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4447
- ignored security advisory for not-patched-yet dependency hybridauth/hybridauth by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4530
- Fix CVE-2026-4587 by bumping hybridauth to 3.13 by @grossmannmartin in https://github.com/shopsys/shopsys/pull/4547
- Update Storefront dependencies for security audit by @chlebektomas in https://github.com/shopsys/shopsys/pull/4611
- add referer URL validation for social login to prevent open redirect by @liborplucnarshopsys in https://github.com/shopsys/shopsys/pull/4320

### :placard: Other Changes

- few simple tweaks and fixes by @vitek-rostislav in https://github.com/shopsys/shopsys/pull/4364

## New Contributors

- @rodrigoprimo made their first contribution in https://github.com/shopsys/shopsys/pull/4516

**Full Changelog**: https://github.com/shopsys/shopsys/compare/v18.0.0...v19.0.0
