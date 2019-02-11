# Backward Compatibility Promise

Smooth and safe upgrades of your own e-commerce project are very important to us.
In the same time, we need to be able to improve Shopsys Framework for you by adding functionality, enhancing or simplifying current functions and fixing bugs.
After reading this promise you'll understand backward compatibility, what changes you can expect and how we plan to make changes in the future.

*Note: This BC promise becomes effective with the first stable release (`7.0.0`).*

## Releases and Versioning
This project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html), which means it's release versions are in format `MAJOR.MINOR.PATCH`:

- `MAJOR` version may contain incompatible changes
- `MINOR` version may add new functionality in a backward-compatible manner
- `PATCH` version contains only backward-compatible bug fixes

Even though we keep these rules as best we can, it could happen that a BC breaking change is introduced in a `MINOR` or `PATCH` release.
For example, because of an important security fix or a critical bug fix.
If this would be the case, we will mark the Pull Request with `[BC-BREAK]` in the title and explain the reasons for it in its description.

Released versions will be always marked using git tags with `v` prefix (eg. `v7.0.0`).
Once created, a git tag marking a release will never be removed or edited.

*Note: Pre-release versions may introduce incompatible changes and can be used to try out the new functions and changes.
Pre-release version format is `MAJOR.MINOR.PATCH-<alpha|beta|rc><n>`, eg. `7.0.0-beta5`.*

### Current Release Plan
To be able to develop and improve Shopsys Framework we plan to release `MAJOR` versions almost quarterly, aiming to release a new `MAJOR` every 3-4 months.
We expect this period to increase in the future to yearly releases.

## The BC Promise in Detail
Shopsys Framework is built on the shoulders of giants so we've based our BC promise on the [**Symfony Backward Compatibility Promise**](https://symfony.com/doc/3.4/contributing/code/bc.html).
Exceptions from adhering to Symfony's promise and clarifications for non-PHP source codes can be found below.

### Project-base Repository
The [project-base repository](https://github.com/shopsys/project-base) in not meant to be extended or depended upon.
For this reason the changes in it are not subject to the rules of this BC promise.
It should be viewed as a template for your own e-commerce projects built on top of Shopsys Framework.

This means that the `project-base` should run with any higher minor version of Shopsys Framework, up to the next `MAJOR` version.

Changes to the `project-base` may contain new features for front-end or examples of newly implemented features and configuration option.
You can follow the changes in the repository to see how working with Shopsys Framework changes between the versions and to keep in touch with best practices and recommendations.

During upgrade to next major version, you have to make the changes yourself according to the advice in [UPGRADE.md](/UPGRADE.md).

*Note: The same holds true for the [demoshop repository](https://github.com/shopsys/demoshop) which is a complex example of an e-commerce project using a custom design and modifications.*

### PHP Code
Rules for PHP code are fully covered by [Symfony Backward Compatibility Promise](https://symfony.com/doc/3.4/contributing/code/bc.html).

### Database Migrations
A new version may include database migrations if the structure of [the entities](/docs/introduction/entities.md) changed.

Migrations in `MINOR` versions are backward-compatible.
It means they may not change types of existing columns, rename columns and tables, and remove nullability of a column.

Migrations in `PATCH` versions are backward-compatible and may be used only for bug fixes.

You should always check and test the database migrations before running them on your production data.

*Tip: Read about the possibilities of altering the execution of DB migration using the [`migrations-lock.yml` file](/docs/introduction/database-migrations.md#locking-the-order-of-migrations).*

### Translation Messages
New [translation messages](/docs/introduction/translations.md) may be added or have its translation changed in any release.
However, they may be removed or have their message ID changed only in `MAJOR` releases.

When changing the message (eg. because of a typo) in a `MINOR` or `PATCH` release, only its translation may be changed.
In such instances, it is preferred to keep using the original message ID to ensure backward compatibility with existing user-defined translation.
This might be unintuitive for contributors because we use English text as message IDs.
See an example of fixing a typo in an English translation message:

Initially, there is only the message ID in the `messages.en.po` (translation doesn't have to be filled if):
```
msgid "Exaple translation"
msgstr ""
```

In a `PATCH` or `MINOR` version, the original message ID may not be removed:
```
msgid "Exaple translation"
msgstr "Example translation"
```

In a `MAJOR` version, the original message ID with the typo may be removed:
```
msgid "Example translation"
msgstr ""
```

### Routing
New routes may be added in any release.
Existing routes should not be modified, except for adding a new [optional placeholder](https://symfony.com/doc/3.4/routing/optional_placeholders.html).

Route names may be changed only in a `MAJOR` release.

### Docker Configuration and Orchestration Manifests

### Twig

### Javascript

### CSS

## Summary

### If You Are a User of Shopsys Framework...

### If You Are a Contributor to Shopsys Framework...
