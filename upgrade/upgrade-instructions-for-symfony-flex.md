# Upgrade Instructions for Symfony Flex

In [#1492](https://github.com/shopsys/shopsys/pull/1492) we changed the application to be compatible with Symfony Flex.
You can read more about upgrading Symfony application to Flex <https://symfony.com/doc/current/setup/flex.html>

- copy [`.env`](https://github.com/shopsys/project-base/blob/master/.env) and [`.env.test`](https://github.com/shopsys/project-base/blob/master/.env.test) files from GitHub into the root folder
- add local environment files as ignored into `.gitignore`
```diff
    /docker-compose.yml
    /docker-sync.yml
+   /.env.local
+   /.env.local.php
+   /.env.*.local
```
- copy [`symfony.lock`](https://github.com/shopsys/project-base/blob/master/symfony.lock) file from GitHub into the root folder
- copy [`composer.json`](https://github.com/shopsys/project-base/blob/master/composer.json) file from GitHub into the root folder
    - add any project-specific dependencies
- in `easy-coding-standard.yml` replace
    - `*/tests/ShopBundle/` with `*/tests/App/`
    - `*/src/Shopsys/ShopBundle/` with `*/src/`
- replace `phpunit.xml` with [`phpunit.xml` version from GitHub](https://github.com/shopsys/project-base/blob/master/phpunit.xml)
- replace `phpstan.neon` with [`phpstan.neon` version from GitHub](https://github.com/shopsys/project-base/blob/master/phpstan.neon)
    - add any project-specific configurations
- replace `build/codeception.yml` with [`build/codeception.yml` version from GitHub](https://github.com/shopsys/project-base/blob/master/build/codeception.yml)

- move the original source code from `src/Shopsys/ShopBundle/` to `src/` and update the namespaces of every PHP file to be `App\...` (advanced IDEs can do this automatically)
- remove bundle related files as the application itself is no longer a bundle
    - `src/Shopsys/ShopBundle/ShopsysShopBundle.php`
    - `src/Shopsys/ShopBundle/DependencyInjection/Configuration.php`
    - `src/Shopsys/ShopBundle/DependencyInjection/ShopsysShopExtension.php`
- update application bootstrapping:
    - copy [`src/Kernel.php`](https://github.com/shopsys/project-base/blob/master/src/Kernel.php) file from GitHub into the `src` folder
    - delete `app/AppCache.php`, `app/AppKernel.php`, `app/router.php`, and `Bootstrap.php` as files are no longer necessary
    - change the namespace from `Shopsys` to `App` in `app/Environment.php`
    - replace `web/index.php` with [`web/index.php` version from GitHub](https://github.com/shopsys/project-base/blob/master/web/index.php)
    - replace `bin/console` with [`bin/console` version from GitHub](https://github.com/shopsys/project-base/blob/master/bin/console)

- all configuration files are now located in the `config` folder exclusively
    - copy all config files to your project from the [config folder from the new version](https://github.com/shopsys/project-base/tree/master/config)
    - merge your project-specific configurations from `app/config` and `src/Shopsys/ShopBundle/resources/config` folders into `config`. Keep in mind, that
        - `config.yml`, `config_dev.yml`, and `config_test.yml` should be deleted as they're no longer necessary
        - in `parameters_common.yml` all extended entities in `shopsys.entity_extension.map` have to have new namespace `App\...`
        - some paths in `paths.yml` have changed, so be extra careful when changing with your modifications
        - routing files (`routing.yml`, `routing_dev.yml`, and `routing_test.yml`) are no longer necessary
        - any project-specific routes have to be moved into `routes`, `routes/dev`, or `routes/test` folders into a separate file
        - any project-specific form extensions have to be added into `config/forms.yml`
        - any project-specific image configurations have to be added into `config/images.yml`
        - any project-specific cron definitions have to be added into `config/services/cron.yml`

- move templates from `src/Shopsys/ShopBundle/Resources/views/` to `templates/` folder
- remove `@ShopsysShop` Twig namespace in every template, eg.
    ```diff
    -   {% extends '@ShopsysShop/Front/Layout/base.html.twig' %}
    -   {% use '@ShopsysShop/Front/Layout/header.html.twig' %}
    +   {% extends 'Front/Layout/base.html.twig' %}
    +   {% use 'Front/Layout/header.html.twig' %}
    ```
- remove `@ShopsysShop` Twig namespace from render methods in controllers, eg.
    ```diff
    -   return $this->render('@ShopsysShop/Front/Content/Article/menu.html.twig', [
    +   return $this->render('Front/Content/Article/menu.html.twig', [
            'articles' => $articles,
        ]);
    ```
- change rendering of embedded controllers in templates from three-part notation to standard string syntax for controllers, eg.
    ```diff
    -   {{ render(controller('ShopsysShopBundle:Front/Heureka:embedWidget')) }}
    +   {{ render(controller('App\\Controller\\Front\\HeurekaController:embedWidgetAction')) }}
    ```
    - _Tip: you can use regular expression search for `ShopsysShopBundle:Front/(\w+):(\w+)` and replace with `App\\Controller\\Front\\$1Controller:$2Action` in Twig files_
- update constant in `templates/Front/Content/Product/filterFormMacro.html.twig`
    ```diff
        {% if isSearch %}
            <input
                type="hidden"
    -           name="{{ constant('Shopsys\\ShopBundle\\Controller\\Front\\ProductController::SEARCH_TEXT_PARAMETER') }}"
    +           name="{{ constant('App\\Controller\\Front\\ProductController::SEARCH_TEXT_PARAMETER') }}"
    ```

- change redirect/forward calls in controllers from three-part notation to fully qualified name, eg.
    ```diff
    -   return $this->forward('ShopsysShopBundle:Front/FlashMessage:index');
    +   return $this->forward(FlashMessageController::class . ':indexAction');
    ```
- move overridden bundle templates from `app/Resources/views/` to `templates/bundle` (see more about template overriding <https://symfony.com/doc/current/bundles/override.html#templates>)

- move translation files from `src/Shopsys/ShopBundle/Resources/translations/` to `translations/`

- move rest of a files from `src/Shopsys/ShopBundle/Resources/` to `src/Resources/`

- update Grunt template directory in `templates/Grunt/gruntfile.js.twig` (2 occurrences)
    ```diff
        destHtml: 'docs/generated',
        htmlDemo: true,
    -   htmlDemoTemplate: '{{ customResourcesDirectory|raw }}/views/Grunt/htmlDocumentTemplate.html',
    +   htmlDemoTemplate: '{{ gruntTemplateDirectory|raw }}/htmlDocumentTemplate.html',
        htmlDemoFilename: 'webfont-admin-svg',
    ```

- update config file paths from `app/config` to `config` in following files any others you may have create
    - `.ci/build_kubernetes.sh`
    - `.ci/deploy-to-google-cloud.sh`
    - `scripts/install.sh`
    - `kubernetes/kustomize/base/kustomization.yaml`

- change namespaces in `migrations-lock.yml` file from `Shopsys\ShopBundle\...` to `App\...`, eg.
    ```diff
        20191114101504:
    -       class: Shopsys\ShopBundle\Migrations\Version20191114101504
    +       class: App\Migrations\Version20191114101504
            skip: false
    ```


- move tests from `tests/ShopBundle/` to `tests/App/` and update the namespaces of every PHP file to be `Tests\App\...` (advanced IDEs can do this automatically)
- change namespace from `Tests\ShopBundle\...` to `Tests\App\...` in following files
    - `tests/App/Acceptance/acceptance.suite.yml`
    - `tests/App/Functional/Component/Javascript/Compiler/Constant/JsConstantCompilerPassTest.php`
    - `tests/App/Functional/Component/Javascript/Compiler/Constant/testClassName.expected.js`
    - `tests/App/Functional/Component/Javascript/Compiler/Constant/testClassName.js`
    - `tests/App/Functional/Component/Javascript/Compiler/Constant/testDefinedConstant.js`
    - `tests/App/Functional/Component/Javascript/Compiler/Constant/testUndefinedConstant.js`
- update `tests/App/Test/Codeception/Helper/SymfonyHelper.php` with the new Kernel class
    ```diff
    -   use AppKernel;
    +   use App\Kernel;
        use Codeception\Configuration;
        use Codeception\Module;

        ...

            {
                require_once Configuration::projectDir() . '/../app/autoload.php';

    -           $this->kernel = new AppKernel(EnvironmentType::TEST, EnvironmentType::isDebug(EnvironmentType::TEST));
    +           $this->kernel = new Kernel(EnvironmentType::TEST, EnvironmentType::isDebug(EnvironmentType::TEST));
                $this->kernel->boot();
            }
    ```

- change namespace from `Shopsys/ShopBundle` to `App` in constants in following JS files and any others you created
    - `src/Resources/scripts/frontend/cart/cartRecalculator.js`
    - `src/Resources/scripts/frontend/validation/form/customer.js`
    - `src/Resources/scripts/frontend/validation/form/order.js`
    - `src/Resources/scripts/frontend/promoCode.js`

- run `composer update`
- clean caches and regenerate assets with `phing clean clean-redis npm assets grunt`
