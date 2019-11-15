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
- allow to configure PHP-FPM pool for the production in Docker ([#1330](https://github.com/shopsys/shopsys/pull/1330))
    - copy new [production-www.conf file from shopsys/project-base](https://github.com/shopsys/project-base/blob/master/docker/php-fpm/production-www.conf) into `docker/php-fpm/production-www.conf`
    - adjust this configuration to your expected production workload and hardware
    - update your `docker/php-fpm/Dockerfile` to copy this configuration into image during the production build
    ```diff
        FROM base as production

    +   # copy FPM pool configuration
    +   COPY ${project_root}/docker/php-fpm/production-www.conf /usr/local/etc/php-fpm.d/www.conf

        COPY --chown=www-data:www-data / /var/www/html
    ```
- production environment disallow administrators to log in with default credentials [#1360](https://github.com/shopsys/shopsys/pull/1360)
    - register `user_checker` in  `app/config/packages/security.yml`
        ```diff
        administration:
            pattern: ^/(admin/|efconnect|elfinder)
        +   user_checker: Shopsys\FrameworkBundle\Model\Security\AdministratorChecker
            anonymous: ~
            provider: administrators
            logout_on_user_change: true
        ```
        - in case you need to disable this functionality (e.g. to allow easier logging in to a deployed application on your CI server)
        there is an environment variable `IGNORE_DEFAULT_ADMIN_PASSWORD_CHECK` which needs to be set to `1` (for **SECURITY** reasons **DO NOT EVER** do this in real production environment)
            - when you are using kubernetes on CI server change your configuration of:
                - `kubernetes/kustomize/overlays/ci/kustomization.yaml`
                ```diff
                        path: ./ingress-patch.yaml
                +   -   target:
                +           group: apps
                +           version: v1
                +           kind: Deployment
                +           name: webserver-php-fpm
                +       path: ./webserver-php-fpm-patch.yaml
                configMapGenerator:
                    -   name: nginx-configuration
                ```
                - create `kubernetes/kustomize/overlays/ci/webserver-php-fpm-patch.yaml` containing
                ```diff
                +-   op: add
                +    path: /spec/template/spec/containers/0/env/-
                +    value:
                +        name: IGNORE_DEFAULT_ADMIN_PASSWORD_CHECK
                +        value: '1'
                ```
            - when using docker containers without kubernetes add the environment variable to the `docker-compose.yml` file to `php-fpm` definition like in example below
                ```diff
                    php-fpm:
                        build:
                            context: .
                            dockerfile: docker/php-fpm/Dockerfile
                            target: development
                            args:
                                www_data_uid: 1000
                                www_data_gid: 1000
                        container_name: shopsys-framework-php-fpm
                        volumes:
                            - shopsys-framework-sync:/var/www/html
                            - shopsys-framework-vendor-sync:/var/www/html/vendor
                            - shopsys-framework-web-sync:/var/www/html/web
                        ports:
                            - "35729:35729"
                +       environment:
                +           - IGNORE_DEFAULT_ADMIN_PASSWORD_CHECK=1
                ```
            - without containers you must set environment variable on the host machine, typically in unix like OS by executing
                ```
                export IGNORE_DEFAULT_ADMIN_PASSWORD_CHECK=1
                ```
- remove default values of environment variables from your `php-fpm`'s `Dockerfile` ([#1408](https://github.com/shopsys/shopsys/pull/1408))
    ```diff
      FROM php:7.3-fpm-stretch as base

      ARG project_root=.
    - ENV REDIS_PREFIX=''
    - ENV ELASTIC_SEARCH_INDEX_PREFIX=''

    ```
- parametrize variables in kubernetes configuration ([#1384](https://github.com/shopsys/shopsys/pull/1384))
    - walk through your [`.ci/deploy-to-google-cloud.sh`](https://github.com/shopsys/shopsys/blob/v8.0.0/project-base/.ci/deploy-to-google-cloud.sh) and notice every occurrences of using `yq` command which is affecting `yml` or `yaml` files in [`project-base/kubernetes`](https://github.com/shopsys/shopsys/tree/v8.0.0/project-base/kubernetes)
        - in Kubernetes configuration files replace these occurrences with placeholder like this `{{FIRST_DOMAIN_HOSTNAME}}` (the placeholder will be replaced by ENV variable with the same name)
            ```diff
             spec:
                 rules:
            -        -   host: ~
            +        -   host: "{{FIRST_DOMAIN_HOSTNAME}}"
                         http:
                             paths:
            ```
        - in  `deploy-to-google-cloud.sh` replace `yq` commands by new code bellow
            - for better stability build docker images without using cache
                ```diff
                    docker image build \
                        --tag ${DOCKER_USERNAME}/php-fpm:${DOCKER_IMAGE_TAG} \
                        --target production \
                +       --no-cache \
                        -f docker/php-fpm/Dockerfile \
                        . &&
                ```
            - change shebang of `.ci/deploy-to-google-cloud.sh`
                ```diff
                - #!/bin/sh -ex
                + #!/bin/bash -ex
                ```
            - add code bellow to find all Kubernetes configuration files in `kubernetes` folder
                ```diff
                +   FILES=$( find kubernetes -type f )
                ```
            - set the environment variables and specify them into array, e.g.
                ```diff
                +   VARS=(
                +       FIRST_DOMAIN_HOSTNAME
                +       SECOND_DOMAIN_HOSTNAME
                +       DOCKER_PHP_FPM_IMAGE
                +       DOCKER_ELASTIC_IMAGE
                +       PATH_CONFIG_DIRECTORY
                +       GOOGLE_CLOUD_STORAGE_BUCKET_NAME
                +       GOOGLE_CLOUD_PROJECT_ID
                +   )
                ```
            - add a loop to replace defined placeholders automatically
                ```diff
                +   for FILE in $FILES; do
                +       for VAR in ${VARS[@]}; do
                +           sed -i "s|{{$VAR}}|${!VAR}|" "$FILE"
                +       done
                +   done
                ```
            - optionally you may unset variables which you will not need any more
                ```diff
                +   unset FILES
                +   unset VARS
                ```
    - for better understanding we recommend to see given PR on github

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
- enable automated checks and fixes of annotations for extended classes in your project ([#1344](https://github.com/shopsys/shopsys/pull/1344))
    - in your `build.xml` add the following line to include the checks and fixes for annotations of extended classes to all `standards-*` phing targets
    ```diff
    + <property name="check-and-fix-annotations" value="true"/>
      <property name="path.root" value="${project.basedir}"/>
      ...
      <import file="${path.framework}/build.xml"/>
    ```
    - run `php phing annotations-fix` to fix or add all the relevant annotations for your extended classes
    - thanks to the fixes, your IDE (PHPStorm) will understand your code better
    - you can read more about the whole topic in the ["Framework extensibility" article](../introduction/framework-extensibility.md#making-the-static-analysis-understand-the-extended-code)
- for the better quality of code in your project we recommend you to increase your PHPStan level to 4 in your `build.xml` and address all the reported violations ([#1381](https://github.com/shopsys/shopsys/pull/1381))
    ```diff
  - <property name="phpstan.level" value="1"/>
  + <property name="phpstan.level" value="4"/>
    ```
    - a lot of the possible issues should be already resolved if you followed the previous instruction and ran the `php phing annotations-fix` phing command
    - some of the issues related to class extension need to be addressed manually nevertheless (see the ["Framework extensibility" article](../introduction/framework-extensibility.md#problem-3)) for more information
    - you need to resolve all the other reported problems (it is up to you whether you decide to address them directly or add ignores in your `phpstan.neon`). You can find inspiration in [#1381](https://github.com/shopsys/shopsys/pull/1381) and [#1040](https://github.com/shopsys/shopsys/pull/1040)
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
    - if you haven't extended `CronFacade` class in your project you don't have to do anything else during the upgrade
    - the constant `CronFacade::TIMEOUT_SECONDS` was deprecated and the timeout was moved to DIC configuration - if you need to access the timeout from different places, define your own constant and [use it in the YAML config via `!php/const`](https://symfony.com/blog/new-in-symfony-3-2-php-constants-in-yaml-files)
    - these methods are deprecated and will be removed in the next major release:
        - `CronFacade::runModulesForInstance()` use method `runModules()` instead
        - `CronFacade::runModule()` use method `runSingleModule()` instead
- add unique error ID to 500 error pages ([#1393](https://github.com/shopsys/shopsys/pull/1393))
    - add new paragraph in [`error.html.twig`](https://github.com/shopsys/shopsys/blob/master/project-base/src/Shopsys/ShopBundle/Resources/views/Front/Content/Error/error.html.twig)
        ```diff
                <p>
                    {{ 'Please excuse this error, we are working on fixing it. If needed, contact us on %mail% or by phone %phone%.'|trans({ '%mail%': getShopInfoEmail(), '%phone%': getShopInfoPhoneNumber() }) }}
                </p>
        +
        +       <p>
        +           Error ID: <span id="js-error-id">{{ '{{ERROR_ID}}' }}</span>
        +       </p>
            </div>
        ```
        - doing so will require regenerating error page templates by running `php phing error-pages-generate` inside `php-fpm` container
    - create new acceptance test in [`ErrorHandlingCest`](https://github.com/shopsys/shopsys/blob/master/project-base/tests/ShopBundle/Acceptance/acceptance/ErrorHandlingCest.php)
        ```diff
        +    /**
        +     * @param \Tests\ShopBundle\Test\Codeception\AcceptanceTester $me
        +     */
        +    public function test500ErrorPage(AcceptanceTester $me)
        +    {
        +        $me->wantTo('display 500 error and check error ID uniqueness');
        +        $me->amOnPage('/test/error-handler/exception');
        +        $me->see('Oops! Error occurred');
        +        $cssIdentifier = ['css' => '#js-error-id'];
        +        $errorIdFirstAccess = $me->grabTextFrom($cssIdentifier);
        +        $me->amOnPage('/test/error-handler/exception');
        +        $errorIdSecondAccess = $me->grabTextFrom($cssIdentifier);
        +        Assert::assertNotSame($errorIdFirstAccess, $errorIdSecondAccess);
        +    }
        ```
- autowire service `ImageFacade` in data object factories ([#1476](https://github.com/shopsys/shopsys/pull/1476))
    - `src/Shopsys/ShopBundle/Model/Payment/PaymentDataFactory.php`
        ```diff
            public function __construct(
                PaymentFacade $paymentFacade,
                VatFacade $vatFacade,
        -       Domain $domain
        +       Domain $domain,
        +       ImageFacade $imageFacade
            ) {
        -       parent::__construct($paymentFacade, $vatFacade, $domain);
        +       parent::__construct($paymentFacade, $vatFacade, $domain, $imageFacade);
        ```
    - `src/Shopsys/ShopBundle/Model/Product/Brand/BrandDataFactory.php`
        ```diff
             public function __construct(
                 FriendlyUrlFacade $friendlyUrlFacade,
                 BrandFacade $brandFacade,
        -        Domain $domain
        +        Domain $domain,
        +        ImageFacade $imageFacade
             ) {
        -        parent::__construct($friendlyUrlFacade, $brandFacade, $domain);
        +        parent::__construct($friendlyUrlFacade, $brandFacade, $domain, $imageFacade);
             }
        ```

- improve your data fixtures and tests so they are more resistant against domains and locales settings changes [#1425](https://github.com/shopsys/shopsys/pull/1425)
    - if you have done a lot of changes in your data fixtures you might consider to skip this upgrade
    - for detailed information, see [the separate article](upgrade-instructions-for-improved-data-fixtures-and-tests.md)

- cover new rounding functionality with tests, for detail information see [the separate article](upgrade-instructions-for-currency-rounding.md)

- add possibility to override admin styles from project-base
 ([#1472](https://github.com/shopsys/shopsys/pull/1472))
    - delete all files from `src/Shopsys/ShopBundle/styles/admin/` and create two new files in it - `main.less` and `todo.less`

    - todo.less file content:
    ```css
    // file for temporary styles eg. added by a programmer
    ```

    - main.less file content:
    ```css
    // load main.less file from framework, variable frameworkResourcesDirectory is set in gruntfile.js
    @import "@{frameworkResourcesDirectory}/styles/admin/main.less";

    // file for temporary styles eg. added by a programmer
    @import "todo.less";
    ```

    - update `src/Shopsys/ShopBundle/Resources/views/Grunt/gruntfile.js.twig`
    ```diff
        admin: {
            files: {
        -       'web/assets/admin/styles/index_{{ cssVersion }}.css': '{{ frameworkResourcesDirectory|raw }}/styles/admin/main.less'
        +       'web/assets/admin/styles/index_{{ cssVersion }}.css': '{{ customResourcesDirectory|raw }}/styles/admin/main.less'
            },
            options: {
        -       sourceMapRootpath: '../../../'
        +       sourceMapRootpath: '../../../',

        +       modifyVars: {
        +           frameworkResourcesDirectory: '{{ frameworkResourcesDirectory|raw }}',
        +       }
    ```

- update pages layout to webline layout ([#1464](https://github.com/shopsys/shopsys/pull/1464))
    - update your custom created pages and wrap them to
    ```html
        {% block blockname %}
            <div class="web__line">
                <div class="web__container">
                    ...old content here...
                </div>
            </div>
        {% endblock %}
    ```
    - update default pages according this pull request (https://github.com/shopsys/shopsys/pull/1464/files)

    - remove global `.web__line` and `.web__container` and unify main three parts (`.web__header`, `.web__main`, `.web__footer`) in file `src/Shopsys/ShopBundle/Resources/views/Front/Layout/layout.html.twig`

    ```diff
        -    <div class="web__line">
        -        <div class="web__header">
        +    <div class="web__header">
        +        <div class="web__line">
    ```

    ```diff
        -        <div class="web__container">
        -            {% block content %}{% endblock %}
        -        </div>
            </div>
        - </div>
        - <div class="web__footer{% if not isCookiesConsentGiven() %} web__footer--with-cookies js-eu-cookies-consent-footer-gap{% endif %}">
        -    {% include '@ShopsysShop/Front/Layout/footer.html.twig' %}
        +
        +    {% block content %}{% endblock %}
        +
        +    <div class="web__footer{% if not isCookiesConsentGiven() %} web__footer--with-cookies js-eu-cookies-consent-footer-gap{% endif %}">
        +        {% include '@ShopsysShop/Front/Layout/footer.html.twig' %}
        +    </div>
    ```

    - add `.web__line` and `.web__container` around flashmessages in files `src/Shopsys/ShopBundle/Resources/views/Front/Layout/layoutWithPanel.html.twig`, `src/Shopsys/ShopBundle/Resources/views/Front/Layout/layoutWithoutPanel.html.twig`

    ```diff
        - {{ render(controller('ShopsysShopBundle:Front/FlashMessage:index')) }}
        + <div class="web__line">
        +     <div class="web__container">
        +         {{ render(controller('ShopsysShopBundle:Front/FlashMessage:index')) }}
        +     </div>
        + </div>
    ```
- improve functional and smoke tests to be more readable and easier to write [#1392](https://github.com/shopsys/shopsys/pull/1392)
    - add a new package via composer into your project `composer require --dev zalas/phpunit-injector`
    - edit `phpunit.xml` by adding a listener
        ```diff
                </filter>
        +
        +       <listeners>
        +           <listener class="Zalas\Injector\PHPUnit\TestListener\ServiceInjectorListener" />
        +       </listeners>
            </phpunit>
        ```
    - edit `FunctionalTestCase`
        - make the class implement `Zalas\Injector\PHPUnit\TestCase\ServiceContainerTestCase` and implement required method `createContainer()`
        - in `setUp()` remove getting class `Domain` directly from container and add `@inject` annotation to its private property instead
        - change visibility of property `$domain` from `private` to `protected`
        - the diff should look like this
            ```diff
                namespace Tests\ShopBundle\Test;

            +   use Psr\Container\ContainerInterface;
                use Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceFacade;
                use Shopsys\FrameworkBundle\Component\Domain\Domain;
                use Shopsys\FrameworkBundle\Component\Environment\EnvironmentType;
                use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
            +   use Zalas\Injector\PHPUnit\TestCase\ServiceContainerTestCase;

            -   abstract class FunctionalTestCase extends WebTestCase
            +   abstract class FunctionalTestCase extends WebTestCase implements ServiceContainerTestCase
                {
                    /**
                     * @var \Symfony\Bundle\FrameworkBundle\Client
                     */
                    private $client;

                    /**
            -        * @var \Shopsys\FrameworkBundle\Component\Domain\Domain|null
            +        * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
            +        * @inject
                     */
            -       private $domain;
            +       protected $domain;

                    protected function setUpDomain()
                    {
            -           /** @var \Shopsys\FrameworkBundle\Component\Domain\Domain $domain */
            -           $this->domain = $this->getContainer()->get(Domain::class);
            +           $this->domain->switchDomainById(Domain::FIRST_DOMAIN_ID);
                    }
            +
            +       /**
            +        * @return \Psr\Container\ContainerInterface
            +        */
            +       public function createContainer(): ContainerInterface
            +       {
            +         return $this->getContainer();
            +       }
            ```
    - to achieve the goal you should find and replace all occurrences of accessing class directly from container, e.g. `$this->getContainer()->get(FooBar::class)` and define it as a class property with an inject annotation instead
    - in case you want to change it in data provides you will need to say good bye to `@dataProvider` annotations
        - since data providers are called earlier than injecting our services you might need to do some workaround for it
            - in our case there were 4 tests where it was changed
                - `ProductOnCurrentDomainFacadeCountDataTest::testCategory()`
                - `ProductOnCurrentDomainFacadeCountDataTest::testSearch()`
                - `ElasticsearchStructureUpdateCheckerTest::testUpdateIsNotNecessaryWhenNothingIsChanged()`
                - `ElasticsearchStructureUpdateCheckerTest::testUpdateIsNecessaryWhenStructureHasAdditionalProperty()`
            - the workaround is to remove `@dataProvider` from the test and call it directly inside a loop
                ```diff
                -   /**
                -    * @param string $searchText
                -    * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $filterData
                -    * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData $expectedCountData
                -    * @dataProvider searchTestCasesProvider
                -    */
                -   public function testSearch(string $searchText, ProductFilterData $filterData, ProductFilterCountData $expectedCountData): void
                +   public function testSearch(): void
                    {
                        $this->skipTestIfFirstDomainIsNotInEnglish();

                -       $filterConfig = $this->productFilterConfigFactory->createForSearch($this->domain->getId(), $this->domain->getLocale(), $searchText);
                -       $countData = $this->productOnCurrentDomainFacade->getProductFilterCountDataForSearch($searchText, $filterConfig, $filterData);
                -       $this->assertEquals($expectedCountData, $this->removeEmptyParameters($countData));
                +       foreach ($this->searchTestCasesProvider() as $dataProvider) {
                +           /** @var string $category */
                +           $searchText = $dataProvider[0];
                +           /** @var \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $filterData */
                +           $filterData = $dataProvider[1];
                +           /** @var \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData $expectedCountData */
                +           $expectedCountData = $dataProvider[2];
                +
                +           $filterConfig = $this->productFilterConfigFactory->createForSearch($this->domain->getId(), $this->domain->getLocale(), $searchText);
                +           $countData = $this->productOnCurrentDomainFacade->getProductFilterCountDataForSearch($searchText, $filterConfig, $filterData);
                +           $this->assertEquals($expectedCountData, $this->removeEmptyParameters($countData));
                        }
                ```

- remove old Internet Explorer support ([#1461](https://github.com/shopsys/shopsys/pull/1461))
    - search all less files and remove `.is-no-flex` and all definitions in it.
    - search all less files and unify flex width definition like
        ```diff
        - flex: 0 100%;
        + width: 100%;
        ```
    - remove old Internet explorer metatags from page header (search and remove in whole project-base)
        ```diff
        - <!--[if IE 7 ]>    <html lang="{{ app.request.locale }}" class="ie7 no-js"> <![endif]-->
        - <!--[if IE 8 ]>    <html lang="{{ app.request.locale }}" class="ie8 no-js"> <![endif]-->
        - <!--[if IE 9 ]>    <html lang="{{ app.request.locale }}" class="ie9 no-js"> <![endif]-->
        - <!--[if (gt IE 9)|!(IE)]><!--> <html lang="{{ app.request.locale }}" class="no-js"> <!--<![endif]-->
        + <html lang="{{ app.request.locale }}" class="no-js">;
        ```
        ```diff
        - <!--[if lte IE 8 ]>
        -     <link rel="stylesheet" type="text/css" href="{{ asset('assets/frontend/styles/index' ~ getDomain().id ~ '_' ~ getCssVersion() ~ '-ie8.css') }}" media="screen, projection">
        - <![endif]-->
        ```
    - update `package.json` and remove legacssy
        ```diff
        - "grunt-legacssy": "shopsys/grunt-legacssy#v0.4.0-with-grunt1-support",
        ```
    - update `gruntfile.js.twig`
        Update autoprefixer settings to `Last 3 versions` and update settings variable name to `browserlist`
        ```diff
        - require('autoprefixer')({browsers: ['last 3 versions', 'ios 6', 'Safari 7', 'Safari 8', 'ie 7', 'ie 8', 'ie 9']})
        + require('autoprefixer')({browserlist: ['last 3 versions']})
        ```

        Delete whole legacssy part
        ```diff
        -  legacssy: {
        -     admin: {
        -        options: {
        // ...
        -  }
        ```
        Remove legacssy tasks
        ```diff
        - grunt.registerTask('default', ["sprite:admin", "sprite:frontend", "webfont", "less", "postcss", "legacssy"]);
        + grunt.registerTask('default', ["sprite:admin", "sprite:frontend", "webfont", "less", "postcss"]);

        - grunt.registerTask('frontend{{ domain.id }}', ['webfont:frontend', 'sprite:frontend', 'less:frontend{{ domain.id }}', 'less:print{{ domain.id }}', 'legacssy:frontend{{ domain.id }}', 'less:wysiwyg{{ domain.id }}'], 'postcss');
        + grunt.registerTask('frontend{{ domain.id }}', ['webfont:frontend', 'sprite:frontend', 'less:frontend{{ domain.id }}', 'less:print{{ domain.id }}', 'less:wysiwyg{{ domain.id }}'], 'postcss');

        - grunt.registerTask('admin', ['sprite:admin', 'webfont:admin', 'less:admin', 'legacssy:admin' ]);
        + grunt.registerTask('admin', ['sprite:admin', 'webfont:admin', 'less:admin']);

        - grunt.registerTask('frontendLess{{ domain.id }}', ['less:frontend{{ domain.id }}', 'legacssy:frontend{{ domain.id }}', 'less:print{{ domain.id }}', 'less:wysiwyg{{ domain.id }}']);
        + grunt.registerTask('frontendLess{{ domain.id }}', ['less:frontend{{ domain.id }}', 'less:print{{ domain.id }}', 'less:wysiwyg{{ domain.id }}']);

        - grunt.registerTask('adminLess', ['less:admin', 'legacssy:admin' ]);
        + grunt.registerTask('adminLess', ['less:admin']);
        ```
        - for more information you can see the [PR](https://github.com/shopsys/shopsys/pull/1461)

- change required version for `symfony/monolog-bundle` ([#1506](https://github.com/shopsys/shopsys/pull/1506))
    - edit `composer.json`
        ```diff
        -       "symfony/monolog-bundle": "^3.3.1",
        +       "symfony/monolog-bundle": "~3.4.0",
        ```

- add image and iframe LazyLoad ([#1483](https://github.com/shopsys/shopsys/pull/1483))

    - update [listGrid.html.twig](https://github.com/shopsys/project-base/blob/master/packages/framework/src/Resources/views/Admin/Content/Advert/listGrid.html.twig)
        ```diff
          {% if row.advert.type == TYPE_IMAGE %}
        -    {{ image(row.advert, {size: 'original', height: 30}) }}
        +    {{ image(row.advert, {size: 'original', height: 30, lazy: false}) }}
           {% else %}
        ```
    - update [imageuploadFields.html.twig](https://github.com/shopsys/project-base/blob/master/packages/framework/src/Resources/views/Admin/Form/imageuploadFields.html.twig)
        ```diff
          <div class="list-images__item__image js-image-upload-preview {% if isRemoved %}list-images__item__in--removed{% endif %}">
        -     {{ image(image, {size: 'original', height: '100', type: image_type}) }}
        +     {{ image(image, {size: 'original', height: '100', type: image_type, lazy: false}) }}
          </div>
          <div class="form-line__item">
        -      {{ image(entity, { size: 'original', height: 100, type: image_type }) }}
        +      {{ image(entity, { size: 'original', height: 100, type: image_type, lazy: false }) }}
          </div>
        ```

    - update [ajaxMoreLoader.js](https://github.com/shopsys/project-base/blob/master/project-base/src/Shopsys/ShopBundle/Resources/scripts/frontend/components/ajaxMoreLoader.js)
        ```diff
                 $paginationToItemSpan.text(paginationToItem);
                 updateLoadMoreButton();
        +        Shopsys.lazyLoadCall.inContainer($currentList);
                 Shopsys.register.registerNewContent($nextItems);
             }
        ```
    - update [productList.AjaxFilter.js](https://github.com/shopsys/project-base/blob/master/project-base/src/Shopsys/ShopBundle/Resources/scripts/frontend/product/productList.AjaxFilter.js)
        ```diff
                 $productsWithControls.show();
        +        Shopsys.lazyLoadCall.inContainer($productsWithControls);
                 Shopsys.register.registerNewContent($productsWithControls);
             };
        ```
    - add new file [project-base/src/Shopsys/ShopBundle/Resources/scripts/frontend/lazyLoadInit.js](https://github.com/shopsys/shopsys/blob/master/project-base/src/Shopsys/ShopBundle/Resources/scripts/frontend/lazyLoadInit.js)

    - add new file [project-base/src/Shopsys/ShopBundle/Resources/scripts/frontend/plugins/minilazyload.min.js](https://github.com/shopsys/shopsys/blob/master/project-base/src/Shopsys/ShopBundle/Resources/scripts/frontend/plugins/minilazyload.min.js)

    - update admin files
      - [listGrid.html.twig](https://github.com/shopsys/shopsys/blob/master/packages/framework/src/Resources/views/Admin/Content/Advert/listGrid.html.twig)

      ```diff
        {% if row.advert.type == TYPE_IMAGE %}
      -     {{ image(row.advert, {size: 'original', height: 30}) }}
      +     {{ image(row.advert, {size: 'original', height: 30, lazy: false}) }}
        {% else %}
      ```
      - [imageuploadFields.html.twig](https://github.com/shopsys/shopsys/blob/master/packages/framework/src/Resources/views/Admin/Form/imageuploadFields.html.twig})

      ```diff
        <div class="list-images__item__image js-image-upload-preview {% if isRemoved %}list-images__item__in--removed{% endif %}">
      -     {{ image(image, {size: 'original', height: '100', type: image_type}) }}
      +     {{ image(image, {size: 'original', height: '100', type: image_type, lazy: false}) }}
        </div>

        <div class="form-line__item">
            {{ image(entity, { size: 'original', height: 100, type: image_type }) }}
            {{ image(entity, { size: 'original', height: 100, type: image_type, lazy: false }) }}
        </div>
      ```

    - update frontend files and disable image lazyload
      - [index.html.twig](https://github.com/shopsys/shopsys/blob/master/project-base/src/Shopsys/ShopBundle/Resources/views/Front/Content/Default/index.html.twig)

      ```diff
        <div class="box-slider__item">
      -     <a href="{{ item.link }}">{{ image(item) }}</a>
      +     <a href="{{ item.link }}">{{ image(item, { lazy: false }) }}</a>
        </div>
      ```
      - [detail.html.twig](https://github.com/shopsys/project-base/blob/master/project-base/src/Shopsys/ShopBundle/Resources/views/Front/Content/Product/detail.html.twig)

      ```diff
        <div class="box-slider__item">
      -     <a href="{{ item.link }}">{{ image(item) }}</a>
      +     <a href="{{ item.link }}">{{ image(item, { lazy: false }) }}</a>
        </div>
      ```
    - update test files according to PR:
        - [ReadModelBundle/Functional/Twig/ImageExtensionTest.php](https://github.com/shopsys/shopsys/blob/master/project-base/tests/ReadModelBundle/Functional/Twig/ImageExtensionTest.php)
        - [ReadModelBundle/Functional/Twig/Resources/picture.twig](https://github.com/shopsys/shopsys/blob/master/project-base/tests/ReadModelBundle/Functional/Twig/Resources/picture.twig)
        - [ShopBundle/Functional/Twig/Resources/picture.twig](https://github.com/shopsys/shopsys/blob/master/project-base/tests/ShopBundle/Functional/Twig/Resources/picture.twig)

## Configuration
- use DIC configuration instead of `RedisCacheFactory` to create redis caches ([#1361](https://github.com/shopsys/shopsys/pull/1361))
    - the `RedisCacheFactory` was deprecated, use DIC configuration in YAML instead
        ```diff
         shopsys.shop.my_custom_cache:
             class: Doctrine\Common\Cache\RedisCache
        -        factory: 'Shopsys\FrameworkBundle\Component\Doctrine\Cache\RedisCacheFactory:create'
        -        arguments:
        -            - '@snc_redis.my_custom_cache'
        +        calls:
        +            - { method: setRedis, arguments: ['@snc_redis.my_custom_cache'] }
        ```

[shopsys/framework]: https://github.com/shopsys/framework
