# Basic and Demo Data During Application Installation

All basic data that are vital for Shopsys Framework (e.g. administrator, vat, database functions and triggers, etc.) are created in [database migrations](./database-migrations.md).

As the migrations create data for the first domain only,
after all migrations are executed, necessary data must be created for all the other domains
(e.g. multidomain settings like free transport limit, database indexes for new locale etc.).
This is the responsibility of `phing` task `domains-data-create` that executes [`CreateDomainsDataCommand`](https://github.com/shopsys/shopsys/blob/9.0/packages/framework/src/Command/CreateDomainsDataCommand.php).

All the other data that are not vital (products, customers, etc.) are created afterwards as data fixtures (i.e. demo data)
using `phing` target `db-fixtures-demo`.
We have English and Czech demo data translations by default.
If you have set a different locale, you can use `translations-dump` that will create new translation files in `src/Shopsys/ShopBundle/Resources/translations` and you can translate your demo data in `dataFixtures.xx.po` file.
Default language will be used for languages without translated demo data.
