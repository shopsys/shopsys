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

[shopsys/framework]: https://github.com/shopsys/framework
