# Symfony 8 Upgrade Plan

Goal: upgrade the monorepo from Symfony 7.4 to Symfony 8.1 (latest) with as few incompatible changes as possible.
Work is done on branch `pk-symfony-8` as a series of small commits.

## Analysis summary (2026-07-03)

- Current state: Symfony 7.4.14, PHP 8.5 — PHP version already satisfies Symfony 8 requirements (>= 8.4).
- Target: Symfony 8.1.1 (latest stable).
- A full `composer update --dry-run` against `^8.1` resolves cleanly except for 6 packages without Symfony 8 support:
    - `jms/translation-bundle` (max SF 7.1) — used by translation extraction
    - `becklyn/ordered-form-bundle` (max SF 7.0) — `position` form option, actively used
    - `prezent/doctrine-translatable-bundle` (max SF 7.0) — entity translations glue
    - `overblog/dataloader-bundle` (max SF 7.0) — GraphQL dataloaders (frontend-api)
    - `overblog/graphiql-bundle` (max SF 7.0) — GraphiQL dev UI
    - `enlightn/security-checker` (max SF 7.x) — `composer security-check` script
- Agreed resolution (per Petr): keep all 6 packages on their current code and override their
  composer metadata via inline `package` repositories with Symfony 8 allowed in constraints.
  Compatibility is verified by the test suites. Easy to revert once upstream releases SF 8 support.
- `scheb/2fa-*` has v8.6.0 supporting SF `^7.4 || ^8.0` — plain version bump.
- `overblog/graphql-bundle` 1.10.0 (already installed) and `symfony/mcp-bundle` 0.8.0 already support Symfony 8.

## Phases

- [x] **Phase 0 — Analysis & plan** (this file)
- [x] **Phase 1 — Preparatory bumps compatible with Symfony 7.4**
    - [x] bump `scheb/2fa-bundle`, `scheb/2fa-email`, `scheb/2fa-google-authenticator` `^7.6` → `^8.6`
    - [x] fix: scheb v8 tags its service template (with abstract args) with `kernel.reset` — excluded it
          from the `tagged_iterator` in Shopsys `ServicesResetter` to keep the container dumpable
- [x] **Phase 2 — Composer metadata overrides for the 6 blocking packages**
    - [x] add inline `package` repositories (same code/dist, relaxed Symfony constraints) to root `composer.json`
    - [x] mirror the change in `project-base/app/composer.json`
- [x] **Phase 3 — Switch to Symfony 8.1**
    - [x] bump all `symfony/*` `^7.4` → `^8.1` and `extra.symfony.require` in root + `project-base/app` composer.json
    - [x] `composer update` (composer.lock is gitignored in the monorepo; `symfony.lock` unchanged)
    - [x] composer patches for `jms/translation-bundle`, `overblog/graphiql-bundle` and
          `prezent/doctrine-translatable-bundle` — Symfony 8 removed the XML DI config format,
          the patches convert their service definitions to PHP config files
- [x] **Phase 4 — Fix breaks**
    - [x] `Bundle::build()` now requires `: void` return type (product feed bundles)
    - [x] `framework.annotations` config option removed
    - [x] `UserInterface::eraseCredentials()` removed — dropped empty implementations
    - [x] `#[TaggedLocator]` removed — replaced by `#[AutowireLocator]`
    - [x] container compiles, cache warms up, admin + storefront respond (dev)
    - [x] application build (`phing build-dev-quick`), incl. `translations-dump` (JMS patch verified, no diff in dumped translations)
    - [x] PHPStan pass (4 errors fixed: `Length` constraint named args ×2, stale phpstan-ignore, missing `void` return type)
    - [x] `composer security-check` works again (enlightn patched for Console 8)
    - [x] runtime verification: admin, storefront, GraphQL endpoint and GraphiQL UI respond correctly
- [x] **Phase 5 — Tests**
    - [x] unit + functional + smoke + frontend-api test suites green
          (2 unit tests fixed — they doubled Symfony classes that are `final` since Symfony 8)
- [x] **Phase 6 — Monorepo packages**
    - [x] bump `symfony/*` constraints in `packages/*/composer.json` to `^8.1`
- [x] **Phase 7 — Coding standards & final validation**
    - [x] `phing standards` + PHPStan green
    - [x] `RequireOverrideAttributeSniff` fixed to ignore private parent methods (PHP rejects `#[Override]` for them,
          Symfony 8.1 kernels declare `configureContainer()` as private in a trait)
    - [x] new Symplify `RemovePropertyVariableNameDescriptionFixer` skipped — it conflicts with Slevomat `InlineDocCommentDeclarationSniff`
- [x] **Phase 8 — Upgrade notes**
    - [x] `upgrade-notes/backend_20260703_144531.md` (PR [#4685](https://github.com/shopsys/shopsys/pull/4685))

## Decision log

- 2026-07-03: 6 packages without SF 8 support → composer metadata override (chosen by Petr from options: override / forks / vendor-in / per-package).
- 2026-07-03: `enlightn/security-checker` kept with metadata override as well (instead of switching to `composer audit`).
- 2026-07-03: 3 of the overridden bundles load DI config from XML which Symfony 8 removed — solved with composer patches
  converting the definitions to PHP config files (consistent with the already used patching of `overblog/graphql-bundle`).

- 2026-11-10: rebased onto current `20.0` and re-checked upstream Symfony 8 support of the 7 overridden packages (Packagist):
  none has a tagged release allowing Symfony 8 — `becklyn/ordered-form-bundle` 1.4.0, `enlightn/security-checker` 2.0.0,
  `jms/translation-bundle` 2.7.0 (3.0.0-beta1 still `^7.1`), `overblog/graphiql-bundle` 1.0.0, `prezent/doctrine-translatable` 5.0.0,
  `prezent/doctrine-translatable-bundle` 3.0.0 are all still capped at Symfony 7; only `overblog/dataloader-bundle` allows `^8.0`
  on `dev-master` (2026-08-08) without a tag. All overrides and patches therefore stay; the overrides already point to the latest tags.
  Symfony 8.2 is only BETA1 (2026-11-04), so the constraint stays `^8.1` (resolves to 8.1.11).


## Upstream status of the overridden packages (checked 2026-11-10)

| Package | License | Last upstream activity | Symfony 8 upstream | Verdict |
| --- | --- | --- | --- | --- |
| `overblog/dataloader-bundle` | MIT | merge 2026-08-08 (mcg-web) | PR #40 "add Symfony 8.0" **merged 2026-08-08**, issue #39 asks for a tag (2026-10-06, no answer); master is 16 commits ahead of v1.1.0 (`AsDataLoader` attribute, config changes) | alive, only the release is missing |
| `jms/translation-bundle` | Apache-2.0 | 2.7.0 + 3.0.0-beta1 on 2026-04-18 (goetas, deguif) | no issue nor PR about Symfony 8, `2.x` and `master` still `^5.4 \|\| ^6.4 \|\| ^7.1`; CI matrix 5.4/6.4/7.2 on PHP 8.3/8.4 | alive, accepts community PRs, nobody started Symfony 8 |
| `prezent/doctrine-translatable` + `-bundle` | MIT | 5.0.0 / 3.0.0 on 2025-12-03 (Robert-Jan Bijl), org active in 2026 | no issue nor PR; external PR #31 (2023) ignored without a comment; CI only `.travis.yml` (dead) | alive, single maintainer, Symfony 8 only when they need it |
| `overblog/graphiql-bundle` | MIT | commit 2024-11-22 | PR #47 (2025-12-18) does the same XML → PHP config change as our patch, 2026-07 question "is this package abandoned?" unanswered | neglected (maintainer only works on GraphQLBundle) |
| `becklyn/ordered-form-bundle` | BSD-3-Clause | commit 2024-01-17 (external PR), 5 stars, 0 issues | nothing, the only fork (klapaudius) is on Symfony 7 too | dormant, the company lives, the bundle does not |
| `enlightn/security-checker` | MIT | commit 2023-12-10, maintainer silent | PR #40 "allow symfony 8" (dbu, 2026-03-23) unanswered even after a ping in 2026-05; issue #39 notes it duplicates `composer audit` | dead |

Other facts gathered for the decisions:

- `position` form option (becklyn) is a documented extensibility feature (`docs/extensibility/form-extension.md`) and is used in `PromoCodeFormType`;
  the bundle is 10 PHP files / 554 lines (+ 289 lines of tests): a compiler pass replacing `form.resolved_type_factory`,
  `OrderedResolvedFormType(Factory)`, `FormFieldSorter`, `RelativePosition` and `FormPositionExtension`.
- Prezent is referenced from 49 PHP files (47 in `packages/framework`) and from entity annotations in every project; maintained alternatives
  (`knplabs/doctrine-behaviors` 3.1 with Symfony 8, Gedmo translatable via the already installed `stof/doctrine-extensions-bundle`) use a different
  translation model, so a migration is not a reasonable option.
- `prezent/doctrine-translatable` 5.0.0 requires the abandoned `doctrine/common`, the only abandoned package in our tree. Composer 2.11 defaults to
  `audit.abandoned = fail`, so a plain `composer audit` currently exits with 1 because of it.
- Our Symfony 8 patches: JMS = `JMSTranslationExtension` + new `Resources/config/{services,console}.php`; Prezent bundle = `PrezentDoctrineTranslatableExtension`
  + new `Resources/config/{services,sonata}.php`; the PHP config format works on Symfony 5.4+, so both can ship as a minor release.
  JMS additionally extends `Symfony\Component\HttpKernel\DependencyInjection\Extension`, deprecated since Symfony 8.1 in favour of
  `Symfony\Component\DependencyInjection\Extension\Extension` (available in all supported versions).
- The `shopsys-php-fpm:latest` image has PHP 8.5, Composer 2.11, git and unzip, so upstream test suites can run with
  `docker run --rm -v "$PWD/../<repo>:/app" -w /app shopsys-php-fpm:latest sh -c 'SYMFONY_REQUIRE=^8.1 composer update && vendor/bin/simple-phpunit'`
  without installing anything on the host.

## Phase 9 — Resolve the overridden packages (proposal, pending confirmation)

- [ ] **overblog/dataloader-bundle — wait for the tag.** Keep the metadata override; when v1.2 (or similar) is tagged, bump the constraint, drop the override
      and review the 16 commits since v1.1.0 (`AsDataLoader`, config changes) in the upgrade.
- [ ] **jms/translation-bundle — upstream PR from a shopsys fork, minor release 2.8 supporting Symfony 5.4 / 6.4 / 7.x / 8.x.**
    - fork `schmittjoh/JMSTranslationBundle` under the shopsys org, clone to `../JMSTranslationBundle`, branch from `2.x`
    - changes: `|| ^8.0` for `symfony/*` in `require` and `require-dev` (+ `symfony/phpunit-bridge ^7.2 || ^8.0`), XML → PHP DI config (our patch),
      base class `Symfony\Component\DependencyInjection\Extension\Extension`, CI matrix + `^8.1` on PHP 8.4 with the `sensio/framework-extra-bundle` removal step
    - run the suite in Docker for `^5.4`, `^6.4`, `^7.2`, `^8.1` (command above), then open the PR against `2.x`
    - until it is released: either keep override + patch, or switch the override to a `vcs` repository on the fork (`dev-symfony-8 as 2.8.0`); after the release drop both
- [ ] **prezent/doctrine-translatable + bundle — same approach, two PRs.**
    - lib: `symfony/cache ^6.4|^7.1|^8.0`, drop the `doctrine/common` requirement (abandoned, nothing from it is needed with ORM 3); bundle: `|| ^8.0` constraints, XML → PHP config (our patch)
    - both repos have only a dead `.travis.yml`, so verification is our Docker run of their PHPUnit suites
    - fallback if the PRs get no reaction within ~1 month: publish `shopsys/doctrine-translatable` and `shopsys/doctrine-translatable-bundle` on Packagist from the forks
      (MIT allows it, keep the copyright notice and LICENSE) and `replace` the prezent packages in our composer.json
- [ ] **becklyn/ordered-form-bundle — inline into `packages/form-types-bundle`, drop the dependency.**
    - move the 10 classes (+ tests) under the Shopsys namespace, register the compiler pass in `ShopsysFormTypesBundle::build()`, keep the `position` option API
    - BSD-3-Clause: keep the Becklyn copyright notice and license text (file headers + `open-source-license-acknowledgements-and-third-party-copyrights.md`), do not use the Becklyn name for promotion
    - upgrade note for projects: remove `BecklynOrderedFormBundle` from `bundles.php` and the composer requirement; the `position` option keeps working
- [ ] **enlightn/security-checker — replace by `composer audit`.**
    - `"security-check": "composer audit --locked"` keeps `composer security-check` and `project-base/.gitlab-ci.yml` working; remove the package, its patch and its override
    - set `config.audit.abandoned` to `report` until the prezent PR removes `doctrine/common`, otherwise the check fails on the abandoned package
    - `composer install/update` already prints the same audit automatically (Composer ≥ 2.4), the explicit script stays for CI
- [ ] **overblog/graphiql-bundle — keep the patch (dev-only UI), low priority.** Optionally +1 upstream PR #47, which is the same change.
- [ ] Order of work: becklyn (no upstream dependency) → enlightn → JMS PR → Prezent PRs → dataloader when tagged; each step removes its override/patch and gets an upgrade note.

### Status 2026-11-10 (everything prepared locally, nothing pushed yet — Petr reviews before anything is sent)

- **becklyn + enlightn** are handled by the separate branch `pk/remove-unmaintained-dependencies` (based on `20.0`, 3 commits: takeover into
  `shopsys/form-types-bundle` with tests, `composer security-check` → `@composer audit` with `audit.abandoned: report`, upgrade notes).
  Standards, PHPStan and the full test suite are green there. On this branch the commits `allowed Symfony 8 for becklyn/ordered-form-bundle and
  enlightn/security-checker via composer metadata overrides` and `patched enlightn/security-checker for Symfony 8 console` carry a note that they are
  to be removed once that PR is merged (PR number to be filled in).
- **jms/translation-bundle**: local branch `symfony-8` in `../JMSTranslationBundle` on top of `2.x` (commit "Add Symfony 8 support, drop Symfony 5.4"):
  PHP DI config, `Symfony\Component\Routing\Attribute\Route` (the `Annotation\Route` class is gone in Symfony 8, the attribute one exists since 6.4,
  hence 5.4 is dropped), DI `Extension` base class, Symfony 8 test kernel config, CI matrix 6.4 / 7.2 / 8.1. Suite green in Docker for all three
  (170 tests, same as baseline).
- **prezent/doctrine-translatable**: local branch `symfony-8` in `../doctrine-translatable` (commits "Allow Symfony 8" — `symfony/cache ^8.0`,
  `doctrine/event-manager` instead of the abandoned `doctrine/common`, native lazy objects in the ORM test case — and "Remove no-op
  ReflectionProperty::setAccessible() call" for PHP 8.5). Suite green for Symfony 7.4 and 8.1.
- **prezent/doctrine-translatable-bundle**: local branch `symfony-8` in `../doctrine-translatable-bundle` (commit "Add Symfony 8 support": PHP DI config,
  constraints). Suite green for Symfony 7.4 and 8.1 against the local library branch.
- **overblog/dataloader-bundle** and **overblog/graphiql-bundle**: unchanged, waiting for the upstream tag / low priority.

## Result

Upgrade finished on 2026-07-03: Symfony 8.1.1, all test suites green, standards + PHPStan clean.
Rebased onto `20.0` on 2026-11-10: Symfony 8.1.11, overrides and patches unchanged (no upstream Symfony 8 releases yet).
