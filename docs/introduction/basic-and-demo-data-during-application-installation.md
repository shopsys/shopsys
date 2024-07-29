# Basic and Demo Data During Application Installation

All basic data that are vital for Shopsys Platform (e.g., administrator, vat, database functions and triggers, etc.) are created in [database migrations](./database-migrations.md).

As the migrations create data for the first domain only,
after all migrations are executed, necessary data must be created for all the other domains
(e.g., multidomain settings like free transport limit, database indexes for new locale, etc.).
This is the responsibility of `phing` task `domains-data-create` that executes [`CreateDomainsDataCommand`](https://github.com/shopsys/shopsys/blob/master/packages/framework/src/Command/CreateDomainsDataCommand.php).

All the other data that are not vital (products, customers, etc.) are created afterward as data fixtures (i.e., demo data)
using `phing` target `db-fixtures-demo`.
We have English and Czech demo data translations by default.
If you have set a different locale, you can use `translations-dump` that will create new translation files in `translations` directory, and you can translate your demo data in `dataFixtures.xx.po` file.
The default language will be used for languages without translated demo data.

## Loading demo data only for certain domains

It is possible to load data fixtures only for specific domains rather than all domains at once, providing finer control over data setup in multi-domain environments.
This reduces the time needed to load demo data for setup with larger number of domains, improving developer efficiency.

Each domain in `domains.yaml` config can set `load_demo_data` to `true` or `false` to specify whether demo data should be loaded for that domain.

```yaml
domains:
    - id: 1
      load_demo_data: true
      locale: en
      name: shopsys
      styles_directory: common
      timezone: Europe/Prague
      type: b2c

    - id: 2
      load_demo_data: true
      locale: cs
      name: 2.shopsys
      styles_directory: domain2
      timezone: Europe/Prague
      type: b2b

    - id: 3
      load_demo_data: false
      locale: cs
      name: 3.shopsys
      styles_directory: domain2
      timezone: Europe/Prague
      type: b2b
```

!!! note

    By default, the application uses the first and second domain data for testing.
    These domains are critical for ensuring consistent test results and should generally be loaded.
