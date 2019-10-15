# [Upgrade from v7.3.2 to v7.3.3-dev](https://github.com/shopsys/shopsys/compare/v7.3.2...7.3)

This guide contains instructions to upgrade from version v7.3.2 to v7.3.3-dev.

**Before you start, don't forget to take a look at [general instructions](https://github.com/shopsys/shopsys/blob/7.3/UPGRADE.md) about upgrading.**
There you can find links to upgrade notes for other versions too.

## [shopsys/framework]

### Application
- use Environment from DIC parameter in ErrorController ([#1389](https://github.com/shopsys/shopsys/pull/1389))
    - change your `services.yml` to inject `%kernel.environment%` to your `Shopsys\ShopBundle\Controller\Front\ErrorController`
        ```diff
        +   Shopsys\ShopBundle\Controller\Front\ErrorController:
        +       arguments:
        +           $environment: '%kernel.environment%'
        ```
    - change `Shopsys\ShopBundle\Controller\Front\ErrorController` to use injected environment instead of `Environment::getEnvironment(false)`
        ```diff
        +   /**
        +    * @var string
        +    */
        +   private $environment;

            /**
             * @param \Shopsys\FrameworkBundle\Component\Error\ExceptionController $exceptionController
             * @param \Shopsys\FrameworkBundle\Component\Error\ExceptionListener $exceptionListener
             * @param \Shopsys\FrameworkBundle\Component\Error\ErrorPagesFacade $errorPagesFacade
             * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
        +    * @param string $environment
             */
            public function __construct(
                ExceptionController $exceptionController,
                ExceptionListener $exceptionListener,
                ErrorPagesFacade $errorPagesFacade,
        -       Domain $domain
        +       Domain $domain,
        +       string $environment
            ) {
                $this->exceptionController = $exceptionController;
                $this->exceptionListener = $exceptionListener;
                $this->errorPagesFacade = $errorPagesFacade;
                $this->domain = $domain;
        +       $this->environment = $environment;
            }

            //...

            private function createUnableToResolveDomainResponse(Request $request): Response
                {
                    $url = $request->getSchemeAndHttpHost() . $request->getBasePath();
                    $content = sprintf("You are trying to access an unknown domain '%s'.", $url);

        -           if (EnvironmentType::TEST === Environment::getEnvironment(false)) {    
        +           if ($this->environment === EnvironmentType::TEST) {
        ```
- remove unused usages of property `ProductData::$price` in following tests ([#1459](https://github.com/shopsys/shopsys/pull/1459))
    - `tests/ShopBundle/Functional/Model/Cart/CartItemTest.php`
    - `tests/ShopBundle/Functional/Model/Cart/CartTest.php`
    - `tests/ShopBundle/Functional/Model/Cart/Watcher/CartWatcherTest.php`
