# Start Building Your Application

Installation of Shopsys Platform is complete, and now you can start building your application.  
Here are the first steps you should start with.

!!! note

    If you don't have a working application, [install it](../installation/installation-guide.md) first.

## Set up timezone to display dates

Dates are internally stored in UTC.
That supports portability and eases integration with other systems.

To see dates properly in the desired timezone, you can change `timezone` setting in `app/config/domains.yaml` file.

_Note: Read more about [working with date-time values](./working-with-date-time-values.md)_

## Set up domains

A domain can be understood as one instance of the shop.
For example, just furniture can be bough on the domain shopsys-furniture.com while only electronics can be found on the domain shopsys-electro.com.

_Note: Learn more about domain concept fully in [Domain, Multidomain, Multilanguage](../basic-concepts/domain-multidomain-multilanguage.md#domain) article._

When you install a new project, domains are set like this

-   `shopsys` on the URL `http://127.0.0.1:8000`
-   `2.shopsys` on the URL `http://127.0.0.2:8000`

Read [settings and working with domain](../configuration/how-to-set-up-domains-and-locales.md#settings-and-working-with-domains) to learn how to set your domains correctly.

-   If you have a project with only one domain, read [how to create a single domain application](../configuration/how-to-set-up-domains-and-locales.md#1-how-to-create-a-single-domain-application).
-   If you have a project with more than two domains, read [how to add a new domain](../configuration/how-to-set-up-domains-and-locales.md#2-how-to-add-a-new-domain).

We highly recommend setting up domains at the beginning of your project correctly.
It will save you a lot of time.

!!! note

    If you add a domain, please create and upload an icon for the new domain (Administration > Settings >  E-shop identification).
    You'll make shop administrators happy.

## Set up locales

A locale is a combination of language and national settings like a collation of language-specific characters.
We use [ISO 639-1 codes](https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes) to specify locale _(e.g., `cs`, `de`, `en`, `sk`)_.
Every domain has defined one locale and also administration has defined its locale.

When you install a new project, locales are set like this

-   `shopsys` _(1st domain)_: `en`
-   `2.shopsys` _(2nd domain)_: `cs`
-   administration: `en`

In case you want to change domain locale read [locale settings](../configuration/how-to-set-up-domains-and-locales.md#3-locale-settings) or in case you want to change default administration locale read [locale in administration](../configuration/how-to-set-up-domains-and-locales.md#36-locale-in-administration).

## Set up domain type

Each domain can be configured with a type.
The type can be either `b2c` (Business-to-Consumer) or `b2b` (Business-to-Business).
This option is set using the `type` parameter in the domain configuration.

By default, if the `type` parameter is not present, the domain is considered as `b2c`.

Here is an example of how to set up the domain type:

```yaml
domains:
    - id: 1
      name: shopsys
      locale: en
      url: http://127.0.0.1:8000
      type: b2c
    - id: 2
      name: 2.shopsys
      locale: cs
      url: http://127.0.0.2:8000
      type: b2b
```

In this example, the first domain is set as B2C and the second domain is set as B2B.

!!! note

    Switching a domain to `b2b` brings some new features. For example, the company number becomes unique throughout the domain.
    This means that no two companies on the same domain can have the same company number.

## Set up Elasticsearch

We use Elasticsearch on the frontend for product searching, filtering and for fast listing of products to provide better performance.
You are likely to adjust the Elasticsearch configuration, for example, if you have a technical shop where the inflection of product names doesn't make sense (we use inflection during searching by default).

!!! note

    Find more in detailed article about [Elasticsearch](../model/elasticsearch.md) usage.

Every domain has defined one [Elasticsearch index](../model/elasticsearch.md#elasticsearch-index-setting).
Definition of this index can be found in `app/src/Resources/definition/<domain_id>.json` files.
The most frequent change is adding [fields](https://www.elastic.co/guide/en/elasticsearch/reference/current/mapping.html) and changing [analysis](https://www.elastic.co/guide/en/elasticsearch/reference/current/analysis.html) to justify search behavior.

## Set up routing

Routing is a mechanism that maps URL path to controllers and methods.
You are likely to adjust routing when you need translated routes for a new locale (e.g., when you have a domain with German localization, and want to have a list of orders under URL `/befehle`).

_We use Symfony routing, so please find more in the [official documentation](https://symfony.com/doc/5.4/routing.html)_

You can adjust the routing in `app/config/shopsys-routing/routing_friendly_url.yaml` file and [locale specific](./how-to-set-up-domains-and-locales.md#32-frontend-routes) in `config/shopsys-routing/routing_front_xx.yaml` files.

## Set up default currency

A default currency is a currency displayed when showing a price in a certain part of the system.
The default currency is different for administration and for each of the domains, and you can adjust the default currency for each one individually.

The administration default currency is used in twig templates e.g., as `{{ value|priceWithCurrencyAdmin }}`.
The default currency for domain is used e.g., as `{{ cartItemDiscount.priceWithVat|price }}`.

_Note: Read more in a dedicated article about [price filters](../model/how-to-work-with-money.md#price) and [administration price filter](../model/how-to-work-with-money.md#pricewithcurrencyadmin)._

When you install a new project, default currencies are set like this

-   `shopsys` _(1st domain)_: `CZK`
-   `2.shopsys` _(2nd domain)_: `EUR`
-   administration: `CZK`

You can change default currencies in administration `Pricing > Currencies`, but this change will not last after application rebuild (operation that you do often during development).

### How to set default currency permanently

You can adjust the demo data to match your project.
This takes a bit more effort, but once you adjust demo data, the change will be applied every time the application is rebuilt.

#### How to set administration default currency

Class `SettingValueDataFixture`, method `load()`

```diff
+ $eurCurrency = $this->getReference(CurrencyDataFixture::CURRENCY_EUR);
+ $this->setting->set(PricingSetting::DEFAULT_CURRENCY, $eurCurrency->getId());
```

#### How to set first domain default currency

Class `SettingValueDataFixture`, method `load()`

```diff
+ $eurCurrency = $this->getReference(CurrencyDataFixture::CURRENCY_EUR);
+ $this->setting->setForDomain(PricingSetting::DEFAULT_DOMAIN_CURRENCY, $eurCurrency->getId(), Domain::FIRST_DOMAIN_ID);
```

#### How to set next domains default currency

Class `SettingValueDataFixture`, method `setDomainDefaultCurrency()`

```diff
- $defaultCurrency = $this->getReference(CurrencyDataFixture::CURRENCY_EUR);
+ $defaultCurrency = $this->getReference(CurrencyDataFixture::CURRENCY_CZK);
```

or you can even use switch logic to provide different default currencies for different domains like

```php
switch ($domainId) {
    case 2: $defaultCurrency = $this->getReference(CurrencyDataFixture::CURRENCY_EUR); break;
    case 3: $defaultCurrency = $this->getReference(CurrencyDataFixture::CURRENCY_CZK); break;
    // ...
```

## Fine-tune your configuration

If all developers working on your project use the same version of PHP (e.g., because all use Shopsys Platform via Docker), you can use higher versions of the libraries and tools installed via Composer.
To do so, remove the `config.platform.php` option from your `composer.json`:

```diff
     "config": {
         "preferred-install": "dist",
-        "component-dir": "project-base/web/components",
-        "platform": {
-            "php": "8.3"
-        }
+        "component-dir": "project-base/web/components"
     },
```

Run `composer update` to install updated versions of your dependencies (versions that don't support the lowest PHP version supported by Shopsys Platform).
Then commit the changed `composer.json` and `composer.lock` so all the devs can share the same configuration.

If you're interested in why we use the forced PHP version in the first place, read [our FAQ](../introduction/faq-and-common-issues.md#why-is-there-a-faked-php-83-platform-in-the-composer-config).

---

On the other hand, if you're planning to run your project in production on a natively installed PHP, you should respect the version installed on that server.
We recommend using the same version in your `php-fpm`'s `Dockerfile`, so that developers using Docker run the app in the same environment.
After all, your production server is the one that matters the most.

First, run `php -v` on your server to find out the exact version, for example:

```no-highlight
PHP 8.3.2 (cli)
Copyright (c) The PHP Group
Zend Engine v4.1.3, Copyright (c) Zend Technologies
    with Zend OPcache v8.1.3, Copyright (c), by Zend Technologies
```

Then change the version in your `docker/php-fpm/Dockerfile`:

```diff
- FROM php:8.3-fpm-bullseye as base
+ FROM php:8.3.2-fpm-bullseye as base
```

After running `docker compose up -d --build` you'll have the application running on the same PHP.

Now you can modify the version in your `composer.json` as well so all packages will always be installed in a compatible version.

```diff
         "platform": {
-            "php": "8.3"
+            "php": "8.3.2"
         }
```

To apply the new setting, execute `composer update` and commit the changes.
