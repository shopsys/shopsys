# FAQ and Common Issues

This section provides only the basic answers to some of the most frequently asked questions.
For more detailed information about Shopsys Platform, please see [Shopsys Platform Knowledge Base](../index.md).

[TOC]

## What are the phing targets?

Every phing target is a task that can be executed simply by `php phing <target-name>` command.
See more about phing targets in [Console Commands for Application Management (Phing Targets)](./console-commands-for-application-management-phing-targets.md).

## What are the data fixtures good for?

Data fixtures are actually demo data available in the Shopys Framework.
For their installation, use the phing target `db-fixtures-demo`.
This phing target is usually triggered as the part of other phing targets, because it requires the application in a certain state (e.g., with configured domains and an existing database structure), see [`build.xml`](https://github.com/shopsys/shopsys/blob/master/packages/framework/build.xml).
Demo data are used for automatic tests and also for installation of demo shop with prepared data.

## How to change a domain URL?

The change of domain url requires two steps.
In the first step, you need to modify the domain url in the configuration file `config/domains_urls.yaml`.
In the second step, you need to replace all occurrences of the old url address in the database with the new url address.
This scenario is described in more detail in the tutorial [How to Set Up Domains and Locales (Languages)](./how-to-set-up-domains-and-locales.md#4-change-the-url-address-for-an-existing-domain).

## How to use database migrations and why the developers should use shopsys:migrations:generate instead of the default Doctrine one?

Migrations (also known as database migrations) are used to unify the database schema with ORM.
On Shopsys Platform, you can use the phing target `db-migrations-generate` for migrations generation.
Compared to the standard migrations generation process from Doctrine, this phing target does not generate "irreversible" migrations, such as migrations with the operations `DROP` and `DELETE`.
Migrations are described more in detail in the docs [Database Migrations](./database-migrations.md)

## Do I have to run coding standards check over all files?

No, you do not have to.
Some of the coding standards check commands are available in two forms.
The first basic form is used to check all files.
The second additional form, commands with the suffix `-diff`, is used to check only modified files.
For example, the phing target `standards` starts checking of all files in the application while the phing target `standards-diff` starts checking only the modified files.
Modifications are detected via git by comparison against the origin/master version.

## Is the application https ready or does it need some extra setting?

Shopsys Platform is fully prepared for HTTPS.
You can just use `https://<your-domain>` in your `config/domains_urls.yaml` configuration file.
Of course, an SSL certificate must be installed on your server.

## How can I easily translate and set up my new language constants?

To set up the user translations of labels and messages, use the files `messages.en.po` and `validators.en.po`, where `en` represents the locale.
These files are generated for each locale you use, and you can find them in the `translations/` directory.
Language settings are described more in detail in the tutorial [How to Set Up Domains and Locales (Languages)](./how-to-set-up-domains-and-locales.md#3-locale-settings).
For more information about translations, see [the separate article](./translations.md).

## How to set up deployment and production server?

We recommend installation using the Docker for production.
See how to install Shopsys Platform in production and how to proceed when deploying in the tutorial [Installation Using Docker on Production Server](../installation/installation-using-docker-on-production-server.md).

## How to set up the administration with a different locale/language (e.g., Czech)?

The administration uses `en` locale by default.
If you want to switch it to the another locale, set a parameter `shopsys.admin_locale` in your `config/parameters_common.yaml` configuration.
However, the selected locale has to be one of registered domains locale.
This scenario is described in more detail in the tutorial [How to Set Up Domains and Locales (Languages)](./how-to-set-up-domains-and-locales.md#36-locale-in-administration).

## What are the differences between "listable", "sellable", "offered" and "visible" products?

Products can be grouped into several groups according to their current status or according to what they are used for.
These groups are described in more detail in the article [How to Work with Products](../model/how-to-work-with-products.md).

## How calculated attributes work?

Some attributes that are used on Shopsys Platform are not set directly, but their value is automatically calculated based on other attributes.
For example, if a category of products does not have a name for a locale of the specific domain, this category will be automatically set as not visible on this domain.
See more about calculated attributes in the article [How to Work with Products](../model/how-to-work-with-products.md).

## How do I change the environment (PRODUCTION/DEVELOPMENT/TEST)?

The environment is determined by the existence of the files `PRODUCTION`, `DEVELOPMENT`, `TEST` in the root of your project.
This file is created automatically during the run of a command `composer install`.
If the command `composer install` is executed, the file `DEVELOPMENT` is created.
If the command `composer install --no-dev` is executed, the file `PRODUCTION` is created.

You can change the environment manually by using the command `php bin/console shopsys:environment:change`.

## Are some periodic tasks part of Shopsys Platform (cron)?

Yes, there is some prepared configuration for Shopsys Platform cron commands in a file `src/Resources/config/services/cron.yaml` in `FrameworkBundle`.
Do not forget to set up a cron on your server to execute [`php phing cron`](./console-commands-for-application-management-phing-targets.md#cron) every 5 minutes.

## Why are you using entity data instead of entities for Symfony forms?

We are using [entity data objects](../model/entities.md#entity-data) instead of [entities](../model/introduction-to-model-architecture.md#entity)
because Symfony forms need setters for all fields and we don't want to mess entities with them.

## What is the configuration file `services_test.yaml` good for?

[`services_test.yaml`](https://github.com/shopsys/shopsys/blob/master/project-base/config/services_test.yaml)
is a service configuration file that is loaded in TEST environment in addition to
the standard configuration defined in [`services.yaml`](https://github.com/shopsys/shopsys/blob/master/project-base/config/services.yaml) as sometimes the configuration differs from the standard one and we need to override it.
E.g., by default, all our services are defined as private. However, in tests, we are retrieving some services directly from the container hence we need to have them public in TEST environment.

## How to change the behavior of the product search on the front-end?

Full-text product search on the front-end is handled via Elasticsearch.
If you want to change its behavior (e.g., make the EAN not as important or change the way the search string is handled - whether to use an n-gram or not) please see [Product Searching](../model/front-end-product-searching.md).

## Where does the business logic belong?

The business logic should be implemented directly in an entity every time when there is no need for external services.
Otherwise, the logic is in facades (resp. the facades are used as delegates to other services, e.g., another _Facade_, _Repository_, _Calculation_, etc.). You can read more about the model architecture in [Introduction to model architecture](../model/introduction-to-model-architecture.md).

## How can I create a friendly URL for my entity?

See [Friendly URL](./friendly-url.md) article.

## How can I create Front-end Breadcrumb navigation?

See [Front-end Breadcrumb Navigation](./front-end-breadcrumb-navigation.md) article.

## Do you have any tips how to debug emails during development in Docker?

Yes we have, you can easily use [`maildev/maildev`](https://github.com/maildev/maildev) library that provides you web UI where you can see the emails including their headers:

-   In your `docker-compose.yml`, change the `smtp-server` service:

```diff
smtp-server:
-        image: ixdotai/smtp:latest
+        image: maildev/maildev
         container_name: shopsys-framework-smtp-server
+        ports:
+            - "1080:1080"
+            - "1025:1025"
```

-   Run `docker compose up -d`
-   change the port in `MAILER_DSN` environment variable value (you can redefine the value in your `.env.local` file):

```diff
- MAILER_DSN=smtp://smtp-server:25?verify_peer=false
+ MAILER_DSN=smtp://smtp-server:1025?verify_peer=false
```

-   Now you are able to see all the application emails in the inbox on [`http://127.0.0.1:8025`](http://127.0.0.1:8025).

_Note: Beware, by using this setting, no emails are delivered to their original recipients.
See [Outgoing emails](https://github.com/djfarrelly/MailDev#outgoing-email) in the documentation of the library for more information._

_Note: You can also view the outgoing emails in the Symfony profiler._

## Can I see what is really happening in the Codeception acceptance tests when using Docker?

Yes, you can! Check [the quick guide](../automated-testing/running-acceptance-tests.md#how-to-watch-what-is-going-on-in-the-selenium-browser).

## Why is there a faked PHP 8.3 platform in the Composer config?

As a general rule, packages and libraries that depend on PHP 8.3 will work as expected even on any higher 8.x version, but not vice versa.
Maintainers of PHP are focusing on backward-compatibility (even if there were [some incompatible changes](https://www.php.net/manual/en/migration81.incompatible.php) introduced in PHP 8.3, in practice it doesn't cause issues).

Using [the `config.platform.php` option](https://getcomposer.org/doc/06-config.md#platform) in `composer.json` allows us to force Composer to install such dependencies, that work for all supported versions of PHP by Shopsys Platform.
These dependencies are locked during each release of Shopsys Platform so users that install it can download exact versions of all libraries and tools that were tested and proved working.
This helps to eliminate unforeseen issues during installation.
See [Composer docs](https://getcomposer.org/doc/01-basic-usage.md#installing-with-composer-lock) for more details on version locking.

Without this forced platform version, you could encounter issues when working on your project with developers that use a different version of PHP.
For example, your `composer.lock` could contain dependencies that not all developers can install.
If that's not your case, you can safely remove the `config.platform.php` option from your `composer.json` and run `composer update` to use higher versions of your dependencies.

## How to make PHPStorm and PHPStan understand that I use extended classes?

There is a phing target that automatically fixes all relevant `@var` and `@param` annotations, and adds proper `@method` and `@property` annotations to your classes so the static analysis understands the class extensions properly.
You can read more in the ["Framework extensibility" article](../extensibility/framework-extensibility.md#making-the-static-analysis-understand-the-extended-code).

## SMTP container cannot send email with error "Helo command rejected: need fully-qualified hostname"

SMTP container should have set hostname to the domain of the server, where your application is running.
You can set this hostname in your `docker-compose` file like this:

```diff
  smtp-server:
      restart: always
      image: namshi/smtp:latest
+     hostname: my-host-machine-hostname.provider.org
```

## Why I see Enum classes in the Framework that are not actually PHP enums?

The main reason, why we do not use PHP enums, is that [they are not extensible](https://www.php.net/manual/en/language.enumerations.object-differences.inheritance.php).
The class inheritance is one of the main approaches how to extend the Shopsys Framework functionality on a project, so we decided to use classes instead of enums to enable the extensibility.
Therefore, you can see e.g. `ProductListTypeEnum` which is a class with constants instead of the PHP enum.
Moreover, the class extends `AbstractEnum` that provides `getAllCases()` method.
The method uses reflection to return all the public constants of the class which simulates the behavior of `cases()` method of the PHP enums.
The key factor here is that the method is not static, and therefore it would include also the constants of the child class when the "enum" class is extended on a project.

## I am not able to clone the repository because of the error "Filename too long"

If you are using Windows, you can encounter the error "Filename too long" when cloning the repository.
This is because the repository contains files with long paths (e.g. Cypress tests screenshots).
To resolve this issue, you can modify your Git configuration to allow long paths by running the following command:

```bash
git config --system core.longpaths true
```
