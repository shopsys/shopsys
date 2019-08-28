# [Upgrade from v8.0.1-dev to v8.1.0-dev](https://github.com/shopsys/shopsys/compare/8.0...HEAD)

This guide contains instructions to upgrade from version v8.0.1-dev to v8.1.0-dev.

**Before you start, don't forget to take a look at [general instructions](/UPGRADE.md) about upgrading.**
There you can find links to upgrade notes for other versions too.

## [shopsys/framework]

### Infrastructure
- remove the unnecessary volume mount of `php.ini` from your `docker/conf/docker-compose-win.yml.dist` and possibly `docker-compose.yml` ([#1276](https://github.com/shopsys/shopsys/pull/1276))
    ```diff
      volumes:
          -   shopsys-framework-sync:/var/www/html
          -   shopsys-framework-vendor-sync:/var/www/html/vendor
          -   shopsys-framework-web-sync:/var/www/html/web
    -     -   ./docker/php-fpm/php-ini-overrides.ini:/usr/local/etc/php/php.ini
      ports:
    ```
- upgrade the Adminer Docker image to 4.7 ([#1354](https://github.com/shopsys/shopsys/pull/1354))
    - change the Docker image of Adminer from `adminer:4.6` to `adminer:4.7` in your `docker-compose.yml` config, `docker-compose*.yml.dist` templates and `kubernetes/deployments/adminer.yml`:
        ```diff
        - image: adminer:4.6
        + image: adminer:4.7
        ```
    - run `docker-compose up -d` so the new image is pulled and used

### Tools
- let Phing properties `is-multidomain` and `translations.dump.locales` be auto-detected ([#1309](https://github.com/shopsys/shopsys/pull/1309))
    - stop overriding the Phing properties `is-multidomain` and `translations.dump.locales` in your `build.xml`, these properties should not be used anymore
        ```diff
          <property name="path.framework" value="${path.vendor}/shopsys/framework"/>

        - <property name="is-multidomain" value="true"/>
        - <property name="translations.dump.locales" value="cs en xx"/>
          <property name="phpstan.level" value="1"/>
        ```
    - if you use the deprecated properties in your `build.xml` yourself, make the particular Phing target dependent on `domains-info-load` and use new auto-detected properties `domains-info.is-multidomain` and `domains-info.locales` instead
        ```diff
        - <target name="my-custom-localization-target">
        + <target name="my-custom-localization-target" depends="domains-info-load">
              <exec executable="${path.custom-localization.executable}" passthru="true" checkreturn="true">
        -         <arg line="${translations.dump.locales}"/>
        +         <arg line="${domains-info.locales}"/>
              </exec>
          </target>
        ```
- test installation of your project on Travis `scripts/install.sh` ([#1342](https://github.com/shopsys/shopsys/pull/1342/))
    - create file `.travis.yml` in your project root with this content:
        ```yaml
        language: bash
        dist: xenial
        stages:
            - Linux
            - MacOS
        services:
            - docker
        os:
            - linux
        jobs:
            include:
                -   stage: Linux
                    name: "Test linux install script"
                    script:
                        - echo 1 | ./scripts/install.sh
                        - docker-compose exec php-fpm ./phing checks tests-acceptance
                -   stage: MacOS
                    name: "Test MacOS script on linux"
                    script:
                        - sudo apt install ruby
                        - gem install docker-sync
                        - sed -i -r "s#sed -i -E#sed -i -r#" ./scripts/install.sh
                        - mkdir -p ./var/elasticsearch-data
                        - chmod -R 777 ./var/elasticsearch-data
                        - echo 2 | ./scripts/install.sh --skip-aliasing
                        - docker-compose exec php-fpm ./phing checks tests-acceptance
        ```
    - update your `scripts/install.sh` file like this:
        ```diff
        echo "Creating docker configuration.."
        case "$operatingSystem" in
            "1")
                cp -f docker/conf/docker-compose.yml.dist docker-compose.yml
        +
        +        sed -i -r "s#www_data_uid: [0-9]+#www_data_uid: $(id -u)#" ./docker-compose.yml
        +        sed -i -r "s#www_data_gid: [0-9]+#www_data_gid: $(id -g)#" ./docker-compose.yml
                ;;
            "2")
                cp -f docker/conf/docker-compose-mac.yml.dist docker-compose.yml
                cp -f docker/conf/docker-sync.yml.dist docker-sync.yml

        -        echo "You will be asked to enter sudo password in case to allow second domain alias in your system config.."
        -        sudo ifconfig lo0 alias 127.0.0.2 up
        +        sed -i -E "s#www_data_uid: [0-9]+#www_data_uid: $(id -u)#" ./docker-compose.yml
        +        sed -i -E "s#www_data_gid: [0-9]+#www_data_gid: $(id -g)#" ./docker-compose.yml
        +
        +        if [[ $1 != --skip-aliasing ]]; then
        +            echo "You will be asked to enter sudo password in case to allow second domain alias in your system config.."
        +            sudo ifconfig lo0 alias 127.0.0.2 up
        +        fi

                mkdir -p ${projectPathPrefix}var/postgres-data ${projectPathPrefix}var/elasticsearch-data vendor
        ```

        ```diff
        docker-compose up -d --build

        echo "Installing application inside a php-fpm container"

        - docker-compose exec php-fpm composer install
        -
        - docker-compose exec php-fpm ./phing db-create test-db-create build-demo-dev-quick error-pages-generate
        + docker-compose exec -T php-fpm composer install
        + docker-compose exec -T php-fpm ./phing db-create test-db-create build-demo-dev-quick error-pages-generate
        ```

### Database migrations
- run database migrations so products will use a DateTime type for columns for "Selling start date" (selling_from) and "Selling end date" (selling_to) ([#1343](https://github.com/shopsys/shopsys/pull/1343))
    - please check [`Version20190823110846`](https://github.com/shopsys/shopsys/blob/master/packages/framework/src/Migrations/Version20190823110846.php)

## Application
- redirect logged users from the registration page to the personal data page ([#1285](https://github.com/shopsys/shopsys/pull/1285))
    - modify your `Shopsys\ShopBundle\Controller\Front\RegistrationController::registerAction()`:
        ```diff
          use Shopsys\FrameworkBundle\Model\Security\Authenticator;
        + use Shopsys\FrameworkBundle\Model\Security\Roles;
          use Shopsys\ShopBundle\Form\Front\Registration\RegistrationFormType;
        ```  
        ```diff
         public function registerAction(Request $request)
         {
        +    if ($this->isGranted(Roles::ROLE_LOGGED_CUSTOMER)) {
        +        return $this->redirectToRoute('front_homepage');
        +    }
        ```
    - to test this behavior, modify your `ShopBundle\Smoke\Http\RouteConfigCustomization::configureFrontendRoutes()`:
        ```diff
        + ->customizeByRouteName('front_login', function (RouteConfig $config) {
        +     $config->addExtraRequestDataSet('Logged user on login page is redirected onto homepage')
        +         ->setAuth(new BasicHttpAuth('no-reply@shopsys.com', 'user123'))
        +         ->setExpectedStatusCode(302);
        + })
          ->customizeByRouteName(['front_order_index', 'front_order_sent'], function (RouteConfig $config) {
              $debugNote = 'Order page should redirect by 302 as the cart is empty by default.';
              $config->changeDefaultRequestDataSet($debugNote)
        ```
- fix a typo in your `ProductController` (in `listByCategoryAction()`, and `searchAction()` methods), and in all the related Twig templates ([#1336](https://github.com/shopsys/shopsys/pull/1336))
    ```diff
    -    'filterFormSubmited' => $filterForm->isSubmitted(),
    +    'filterFormSubmitted' => $filterForm->isSubmitted(),
    ```
- make promo codes editable on separate page ([#1319](https://github.com/shopsys/shopsys/pull/1319))
    - replace `PromoCodeInlineEditCest` with [`VatInlineEditCest`](https://github.com/shopsys/project-base/blob/master/tests/ShopBundle/Acceptance/acceptance/VatInlineEditCest.php) in order to have inline edit still acceptance tested
    - if you want to have promo codes editable on separate page change argument `useInlineEditation` to `false` for `PromoCodeController` in `services.yml` as inline editation for promo codes is deprecated and will be removed in the next major
        ```diff
            Shopsys\FrameworkBundle\Controller\Admin\PromoCodeController:
        +       arguments:
        +           $useInlineEditation: false
        ```
- add `setlocale(LC_NUMERIC, 'en_US.utf8');` in your `Bootstrap.php` right behind `setlocale(LC_CTYPE, 'en_US.utf8');` ([#1313](https://github.com/shopsys/shopsys/pull/1313/))
    - add [`\Tests\ShopBundle\Unit\NumberFormattingTest`](https://github.com/shopsys/shopsys/blob/master/project-base/tests/ShopBundle/Unit/NumberFormattingTest.php)
- add support to display date-time values in different timezone ([#1343](https://github.com/shopsys/shopsys/pull/1343))
    - you can read more about how to [work with display timezone in documentation](/docs/introduction/working-with-date-time-values.md)
    - in Twig templates have to be used `formatDate`, `formatTime` and `formatDateTime` filters exclusively to format date-time values
        - change the filter in `src/Shopsys/ShopBundle/Resources/views/Front/Content/Article/detail.html.twig` file
            ```diff
                 </h1>

            -    <p>{{ article.createdAt|date('j.n.Y') }}</p>
            +    <p>{{ article.createdAt|formatDate }}</p>

                 <div class="in-user-text">
          ```
    - you can delete test `tests/ShopBundle/Functional/Twig/DateTimeFormatterExtensionTest.php` as it was moved to the FrameworkBundle, if you do not test any your specific use-case
    - class `CustomDateTimeFormatterFactory` is deprecated and should not be used anymore
        - if you have extended this factory to alter configuration of `DateTimeFormatPatternRepository`, extend new `DateTimeFormatPatternRepositoryFactory` instead (as `DateTimeFormatPatternRepository` configuration was moved to this class from the factory)
        - custom `DateTimeFormatter` has to be created with the DIC directly if needed
            ```yaml
            Shopsys\FrameworkBundle\Component\Localization\DateTimeFormatterInterface:
                class: 'Shopsys\ShopBundle\Component\Localization\DateTimeFormatter'
            ```
    - class `DateTimeFormatter` no longer supports extending via `shopsys.entity_extension.map` as it is not entity
        -if you have extended this class with `shopsys.entity_extension.map`, register your class for `DateTimeFormatterInterface` in `services.yml` instead
    - if you use any custom date-time related FormTypes, set option `view_timezone` to value provided by `DisplayTimeZoneProviderInterface` class
        - you can find inspiration in new [`Shopsys\FrameworkBundle\Form\DateTimeType`](https://github.com/shopsys/shopsys/blob/master/packages/framework/src/Form/DateTimeType.php) FormType, or you can use this FormType directly
- use `CronModuleExecutor` as a service in DIC ([#1314](https://github.com/shopsys/shopsys/pull/1314))
    - the constant `CronFacade::TIMEOUT_SECONDS` was deprecated and the timeout was moved to DIC configuration - if you need to access the timeout from different places, define your own constant and [use it in the YAML config via `!php/const`](https://symfony.com/blog/new-in-symfony-3-2-php-constants-in-yaml-files)
    - these methods are deprecated and will be removed in the next major release:
        - `CronFacade::runModulesForInstance()` use method `runModules()` instead
        - `CronFacade::runModule()` use method `runSingleModule()` instead

[shopsys/framework]: https://github.com/shopsys/framework
