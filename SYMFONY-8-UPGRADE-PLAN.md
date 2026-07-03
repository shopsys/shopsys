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
    - [x] `upgrade-notes/backend_20260703_144531.md` (PR number placeholder `#XXXX` to be filled when the PR exists)

## Decision log

- 2026-07-03: 6 packages without SF 8 support → composer metadata override (chosen by Petr from options: override / forks / vendor-in / per-package).
- 2026-07-03: `enlightn/security-checker` kept with metadata override as well (instead of switching to `composer audit`).
- 2026-07-03: 3 of the overridden bundles load DI config from XML which Symfony 8 removed — solved with composer patches
  converting the definitions to PHP config files (consistent with the already used patching of `overblog/graphql-bundle`).

## Result

Upgrade finished on 2026-07-03: Symfony 8.1.1, all test suites green, standards + PHPStan clean.
