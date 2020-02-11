# How to Set Up Domains and Locales (Languages)

This article describes how to work with domains and languages during the development of your project.
For an explanation of the basic terms, please read [domain, multidomain and multilanguage](domain-multidomain-multilanguage.md) article first.

!!! note
    Demo data on the Shopsys Framework are only translated to `en` and `cs` locales.
    If you have set a different locale, you can use `translations-dump` that will create new translation files in `translations` directory and you can translate your demo data in `dataFixtures.xx.po` file.

## Settings and working with domains

### 1. How to create a single domain application

#### 1.1 Domain configuration
Modify the configuration of the domain in `config/domains.yml`.
This configuration file contains information about the domain ID, the domain identifier for the domain tabs in the administration, and the domain locale.

#### 1.2 Set up the url address
Set the url address for the domain in `config/domains_urls.yml`.

#### 1.3 Locale settings
Set up the locale of the domain according to the instructions in the section [Locale settings](#3-locale-settings)

#### 1.4 Build
Start the build, for example using a phing target
```sh
php phing build-demo-dev
```
!!! hint
    In this step you were using Phing target `build-demo-dev`.  
    More information about what Phing targets are and how they work can be found in [Console Commands for Application Management (Phing Targets)](./console-commands-for-application-management-phing-targets.md)

!!! note
    During the execution of `build-demo-dev phing target`, there will be installed 3-rd party software as dependencies of Shopsys Framework by [composer](https://getcomposer.org/doc/01-basic-usage.md#installing-dependencies) and [npm](https://docs.npmjs.com/about-the-public-npm-registry) with licenses that are described in document [Open Source License Acknowledgements and Third-Party Copyrights](https://github.com/shopsys/shopsys/blob/9.0/open-source-license-acknowledgements-and-third-party-copyrights.md)

After the build is completed, a singledomain application is created.

#### 1.5 Tests
Some tests are prepared for the specific configuration and test only the behavior for the default locales (`en` and/or `cs`).
For example `Tests\App\Functional\Twig\PriceExtensionTest` is expecting the specific format of displayed currency.
If you want to use already created tests for your specific configuration, you may need to modify these tests to be able to test your specific configuration of the domain.

!!! note "Notes"
    - Some smoke and functional tests are only executed for a single domain or a multiple domain configuration. Search for `@group singledomain` or `@group multidomain` in your test methods' annotations respectively.
    - Some functional tests (e.g. the ones for searching a specific phrase) are also skipped when the first domain locale is other than `en`. Search for usages of `FunctionalTestCase::skipTestIfFirstDomainIsNotInEnglish()` method.

### 2. How to add a new domain

#### 2.1 Domain configuration
Modify the configuration of the domain in `config/domains.yml`.
This configuration file contains pieces of information about the domain ID, the domain identifier for the domain tabs in the administration, and the domain locale.

#### 2.2 Set up the url address
Set the url address for the domain in `config/domains_urls.yml`.

!!! note
    When you add a domain with the new url address on the MacOS platform, you need to enable this url address also in the network interface, see [Installation Using Docker for MacOS](../installation/installation-using-docker-macos.md#12-enable-second-domain-optional)

#### 2.3 Locale settings
Set up the locale of the domain according to the instructions in the section [Locale settings](#3-locale-settings)

#### 2.4 Create multidomains data
There need to be created some multidomain data for the newly added domain.
Run the phing target
```sh
php phing domains-data-create
```
This command performs multiple actions:

- multidomain attributes from the first domain are copied for this new domain, see `FrameworkBundle/Component/Domain/DomainDataCreator.php`, where the `TEMPLATE_DOMAIN_ID` constant is defined.
- if a new locale is set for the newly added domain, the empty rows with this new locale will be created for multilang attributes
- pricing group with the name Default is created for every new domain
- the last step of this command is the start of automatic recalculations of prices, availabilities, and products visibilities.

#### 2.5 Multilang attributes
Demo data of Shopsys Framework are translated only for `en` and `cs` locales.
If you have set a different locale, you can use `translations-dump` that will create new translation files in `translations` directory  and you can translate your demo data in `dataFixtures.xx.po` file.

#### 2.6 Generate assets for the new domain
In order to properly display the new domain, assets need to be generated
```sh
php phing grunt
```

#### 2.7. Create elasticsearch definition for the new domain
The configuration for elasticsearch must be created for each domain in a separate json file.
By default, the configurations for the domain 1 and 2 are already parts of a project-base.
Configuration for elasticsearch can be found in `src/Resources/definition/`.
If you add a new domain, you need to create an elasticsearch configuration for this new domain.

After you create the configuration, you have to create the index in elasticsearch and fill it by data
```sh
php phing elasticsearch-index-recreate
php phing elasticsearch-export
```

### 3. Locale settings
Some parts of these instructions are already prepared for the locales `en` and `cs`.

#### 3.1 Set up the locale for domain
Set up the locale of the domain in `config/domains.yml`.
This configuration file contains pieces of information about the domain ID, the domain identifier for the domain tabs in the administration, and the domain locale.

#### 3.2 Frontend routes
Create a file with the frontend routes for the added locale if this file is not already created for this locale.
Create this file in the directory `config/shopsys-routing` with the name `routing_front_xx.yml` where `xx` replace for the code of added locale.

#### 3.3 Translations and messages
In order to correctly display the labels like *Registration*, *Cart*, ..., create a file with translations of messages in `translations` directory.
Override the Phing property `translations.dump.locales` in the `build.xml` and set a space-separated list of locales you want to dump.
For example, if you want to add `xx` to the locales, add `<property name="translations.dump.locales" value="cs en xx"/>` to your `build.xml`.

Then run
```sh
php phing translations-dump
```
There will be created files for translations of messages for the new locale in `translations` directory, which you'll need to translate:

* `messages.xx.po` for translations of common strings
* `validators.xx.po` for translations of validation messages
* `dataFixtures.xx.po` for translations of demo data

For more information about translations, see [the separate article](./translations.md).

#### 3.4 Generate database functions for the locale use
Within the database functions, it is necessary to regenerate the default database functions for the locale use that are already created for the `en` locale as default.
Regenerate database functions by running a phing target
```sh
php phing domains-db-functions-create
```

#### 3.5 Multilang attributes
Demo data of Shopsys Framework are prepared only for `en` and `cs` locales.
If you have set a different locale, you can use `translations-dump` that will create new translation files in `translations` directory and you can translate your demo data in `dataFixtures.xx.po` file.

#### 3.6 Locale in administration
Administration is by default in `en` locale.
This means that for example product list in administration tries to display translations of product names in `en` locale.
If you want to switch it to the another locale, set a parameter `shopsys.admin_locale` in your `config/parameters_common.yml` configuration to desired locale.
However, the selected locale has to be one of registered domains locale.
When you change admin locale, you have to update acceptance tests, to have administration use cases tested properly.

You can change administration translations by adding messages into your `translations/messages.xx.po`.

#### 3.7 Sorting in different locales
Alphabetical sorting on frontend uses Elasticsearch and its [ICU analysis plugin](https://www.elastic.co/guide/en/elasticsearch/plugins/6.3/analysis-icu.html).  
Every domain needs to have `language` parameter for field `name.keyword` in `src/Resources/definition/product/*.json` set in order to sort correctly for given locale.

An example for domain that uses English language:
```json
"name": {
    "type": "text",
    "analyzer": "stemming",
    "fields": {
        "keyword": {
            "type": "icu_collation_keyword",
            "language": "en",
            "index": false
        }
    }
}
```

#### 3.8 Default application locale
In most cases, when working with multilanguage attributes, you do not need to specify any locale as it is set automatically from the request so you can just use e.g. `Product::getName()` and you get the proper translation.
However, sometimes, there is no request (i.e. in CLI commands or in tests) so you need to tell your application, which locale should be used - either using a parameter in the method (`Product::getName('es')`) or by setting a default application locale.

To change the default application locale, set `locale` parameter to you desired locale (e.g. `es` for Spanish) in your [`parameters_common.yml`](https://github.com/shopsys/shopsys/blob/9.0/project-base/config/parameters_common.yml).
The value is then used for setting [`default_locale` Symfony parameter](https://symfony.com/doc/3.4/translation/locale.html#setting-a-default-locale) (see your [`config/packages/translation.yaml`](https://github.com/shopsys/shopsys/blob/9.0/project-base/config/packages/translation.yaml) config).

### 4. Change the url address for an existing domain

#### 4.1 Change the url address
Change the url address in the configuration of the domain in `config/domains_urls.yml`.

!!! note
    When you add a domain with the new url address on the MacOS platform, you need to enable this url address also in the network interface, see [Installation Using Docker for MacOS](../installation/installation-using-docker-macos.md#12-enable-second-domain-optional)

#### 4.2 Replace the old url address
Run the phing target
```sh
php phing domains-urls-replace
```
Running this command will ensure replacing all occurrences of the old url address in the text attributes in the database with the new url address.

### 5. Change the locale for an existing domain
This scenario is not supported by default because of the fact, that change of the locale within an already running eshop almost never happens.
However, there is workaround even for this scenario.

#### 5.1 Change the locale to the locale that is already used by another domain
If you need to change the locale of a specific domain to another locale that is already used by another domain, just set the required locale for this domain in the `config/domains.yml`.

#### 5.2 Change the locale to the locale that is not yet used by another domain
If you need to change the locale of a specific domain to another locale that is not yet already used by another domain, add new temporary domain with this new locale and follow the instructions of [How to add a new domain](#2-how-to-add-a-new-domain).
The following procedure is the same as in the case with [Change the locale to the locale that is already used by another domain](#change-the-locale-to-the-locale-that-is-already-used-by-another-domain).

### 6. Change domains appearance
If you need to distinguish your domains visually, see [Creating a Multidomain Design cookbook](../cookbook/creating-a-multidomain-design.md).
