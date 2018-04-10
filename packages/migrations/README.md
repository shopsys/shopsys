## About

[![Build Status](https://travis-ci.org/shopsys/migrations.svg?branch=master)](https://travis-ci.org/shopsys/migrations)
[![Downloads](https://img.shields.io/packagist/dt/shopsys/migrations.svg)](https://packagist.org/packages/shopsys/migrations)

This bundle provides commands which can be useful for database migrations:
* `shopsys:migrations:check-mapping` checks if ORM mapping is valid.
* `shopsys:migrations:check-schema` checks if database schema is satisfying ORM.
* `shopsys:migrations:count` returns count of migrations to execute.
* `shopsys:migrations:migrate` executes all database migrations in one transaction.
* `shopsys:migrations:generate` generates database migrations if necessary (prompts you to choose if you are developing more than one bundle).

This bundle uses [DoctrineMigrationsBundle](https://symfony.com/doc/current/bundles/DoctrineMigrationsBundle), so you have to install both.

## To be able to use this bundle, you need to do following:
1. Require `shopsys/migrations` in `composer.json`
2. Register bundles in your `AppKernel.php`:

    ```
    new Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle(),
    new Shopsys\MigrationBundle\ShopsysMigrationBundle(),
    ```
3. Configure `DoctrineMigrationsBundle` according to its documentation (see https://symfony.com/doc/current/bundles/DoctrineMigrationsBundle/index.html#configuration)

## Contributing

Report [issues](https://github.com/shopsys/shopsys/issues/new) and send [pull requests](https://github.com/shopsys/shopsys/compare) in the main [Shopsys repository](https://github.com/shopsys/shopsys).
