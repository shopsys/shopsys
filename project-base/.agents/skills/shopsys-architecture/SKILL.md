---
name: shopsys-architecture
description: How this Shopsys project is structured and the correct way to extend it — read it before implementing when you need to know WHERE code lives and HOW to extend functionality (model/entity, backend, Frontend API, administration, storefront) without editing the framework. Covers Entity Extension, service and vendor overrides, the Data/Factory/Facade pattern, where things live, and which docs to read. Reach for it on "how do I do X in Shopsys" and when extending an entity, API, admin, or form.
---

> **Monorepo note:** if the repository has top-level `packages/` and `project-base/` directories (the shopsys/shopsys monorepo layout, not a standalone project), also read `.agents/skills/monorepo-vs-project/SKILL.md` and apply its delta (package-first + path/role remap) on top of this skill.

# Shopsys architecture — where to reach and how to extend

This skill orients you so you don't flounder while implementing. It doesn't implement for you and doesn't copy the documentation — it points you to the right place and the right mechanism.

- To *find* existing code: `.agents/skills/codebase-locator/SKILL.md`
- To understand *how* something works: `.agents/skills/codebase-analyzer/SKILL.md`
- For *example patterns* to copy: `.agents/skills/codebase-pattern-finder/SKILL.md`
- For commands (build, tests, schema, checks): `.agents/skills/shopsys-commands/SKILL.md`

## The core model: you consume read-only packages

This repository is a **project** built on Shopsys Platform. The framework ships as Composer packages (`shopsys/framework`, `shopsys/frontend-api`, and others) installed under **`vendor/shopsys/`** — treat that directory as **read-only reference**. You never edit it; you **extend** it from your own code in `app/` and `storefront/`.

Two ways to change platform behavior:

### 1. Entity Extension (model layer)

An entity in the `App\` namespace that extends a `Shopsys\` base entity is wired up automatically. Add fields with Doctrine mapping/attributes, then generate a migration.

- Extend `Shopsys\FrameworkBundle\Model\Product\Product` with `App\Model\Product\Product`.
- Add a new attribute → see the cookbook (link below); generate the migration with the migrations Phing target.
- The write path is the **Data / DataFactory / Facade** trio: a `*Data` object carries form values, a `*DataFactory` builds/populates it from the entity, and the `*Facade` persists it (`em->persist/flush`). Extend the project's `*Data`/`*DataFactory`/`*Facade` (which extend the framework base) rather than reaching into Doctrine directly.

### 2. Service / class overrides (everything else)

Symfony autowiring resolves the vendor class by default. To make your subclass win, alias it in `app/config/services.yaml`:

```yaml
# app/config/services.yaml
Shopsys\FrameworkBundle\Model\Product\ProductRepository:
    alias: App\Model\Product\ProductRepository
```

Without the alias, your custom class is never used. Forms follow the same idea via **FormType extensions** (`*FormTypeExtension`) placed alongside the form they extend — don't edit the vendor FormType.

### Keep static analysis aware of your extensions

After extending an entity, factory, facade, or repository, run **`annotations-fix`** (bundled into `standards-fix` → `.agents/skills/shopsys-commands/SKILL.md`). It regenerates the `@property` and `@method` docblocks across the extension layer so PHPStan understands the extended types — e.g. that a factory now returns your `App\` entity, not the `Shopsys\` base. Skip it and static analysis reports false errors against the base types.

## How to extend, by layer

- **Storefront** — entirely in `storefront/` (React/Next + GraphQL queries); edit directly.
  Docs: *Storefront*, *Frontend*.
- **Model / backend** (entity, facade, repository, data object) — **Entity Extension**: an `App\` entity extends the `Shopsys\` base; add fields via attribute + migration.
  Docs: *Model → Introduction to Model Architecture*, *Extensibility → Entity Extension*, *Cookbook → Adding New Attribute to an Entity*.
- **Frontend API (GraphQL)** — extend types/resolvers in `app/src/FrontendApi/`; after creating or editing `*.types.yaml` type definitions or storefront `.graphql` files, regenerate the schema.
  Docs: *Frontend API*.
- **Administration** — grids, menus and forms have their own patterns; extend via FormType extensions and config.
  Docs: *Administration*, *Extensibility → Extending Form*.

## Where things live

Backend (`app/`):

- **Model / business logic** — `app/src/Model/`
- **Controllers** — `app/src/Controller/`
- **Forms** — `app/src/Form/`
- **Frontend API** — `app/src/FrontendApi/Resolver/`, `app/src/FrontendApi/Mutation/`
- **Commands** — `app/src/Command/`
- **Twig templates (admin)** — `app/templates/`
- **Migrations** — `app/src/Migrations/`
- **Data fixtures (demo data)** — `app/src/DataFixtures/Demo/`
- **Tests** — `app/tests/`
- **Config** — `app/config/` (`services.yaml`, `domains.yaml`, `packages/*.yaml`)

Storefront (`storefront/`): `components/`, `pages/`, `graphql/`, `utils/`, `types/`, `styles/`.

Framework reference (read-only): `vendor/shopsys/*/src/`.

## Resources — in this order

1. **Local `vendor/shopsys/` source = the most precise reference.** Need a service signature, a method, or how something is built? Read the package source directly (Grep/Glob in `vendor/shopsys/`). It matches exactly the version running here. First stop.
2. **[docs.shopsys.com](https://docs.shopsys.com)** — concepts and extension guides:
   - Entity Extension — https://docs.shopsys.com/en/latest/extensibility/entity-extension/
   - Model architecture — https://docs.shopsys.com/en/latest/model/introduction-to-model-architecture/
   - Adding a new attribute to an entity — https://docs.shopsys.com/en/latest/cookbook/adding-new-attribute-to-an-entity/

   Mind the version in the URL (`/en/latest/`). If you know this project's major version, use it (e.g. `/en/20.0/`) so the guide matches.

## Quick analysis before implementing

1. **Find the precedent** — how is a similar thing already done in `app/src/` and in `vendor/shopsys/`? Copy the established pattern (`.agents/skills/codebase-pattern-finder/SKILL.md`).
2. **Standard extension?** (new entity field, attribute, API field, admin grid) → check the Cookbook / Extensibility page for the concrete steps.
3. **Unknown package API?** → read the source in `vendor/shopsys/`.
4. Only then implement. Don't study ahead of need — pull only what the current change requires.
