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
- [ ] **Phase 2 — Composer metadata overrides for the 6 blocking packages**
  - [ ] add inline `package` repositories (same code/dist, relaxed Symfony constraints) to root `composer.json`
  - [ ] mirror the change in `project-base/app/composer.json`
- [ ] **Phase 3 — Switch to Symfony 8.1**
  - [ ] bump all `symfony/*` `^7.4` → `^8.1` and `extra.symfony.require` in root + `project-base/app` composer.json
  - [ ] `composer update`, commit new `composer.lock` + `symfony.lock`
- [ ] **Phase 4 — Fix breaks**
  - [ ] application build (`phing build-dev-quick`), container compilation, cache warmup
  - [ ] PHPStan pass (fix removed/changed APIs; prefer changes also compatible with 7.4)
- [ ] **Phase 5 — Tests**
  - [ ] unit + functional + smoke + frontend-api test suites green
- [ ] **Phase 6 — Monorepo packages**
  - [ ] bump `symfony/*` constraints in `packages/*/composer.json` to `^8.1`
- [ ] **Phase 7 — Coding standards & final validation**
  - [ ] `phing standards` + PHPStan green
- [ ] **Phase 8 — Upgrade notes**
  - [ ] write upgrade notes in `upgrade-notes/` following project conventions

## Decision log

- 2026-07-03: 6 packages without SF 8 support → composer metadata override (chosen by Petr from options: override / forks / vendor-in / per-package).
- 2026-07-03: `enlightn/security-checker` kept with metadata override as well (instead of switching to `composer audit`).
