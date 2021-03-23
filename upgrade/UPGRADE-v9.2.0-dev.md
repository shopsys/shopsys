# [Upgrade from v9.1.0 to v9.2.0-dev](https://github.com/shopsys/shopsys/compare/v9.1.0...master)

This guide contains instructions to upgrade from version v9.1.0 to v9.2.0-dev.

**Before you start, don't forget to take a look at [general instructions](https://github.com/shopsys/shopsys/blob/7.3/UPGRADE.md) about upgrading.**
There you can find links to upgrade notes for other versions too.

## Application
- use different css classes for javascript and tests ([#2179](https://github.com/shopsys/shopsys/pull/2179))
    - see #project-base-diff to update your project

- class `Shopsys\FrameworkBundle\Component\Csv\CsvReader` is deprecated, use `SplFileObject::fgetcsv()` instead ([#2218](https://github.com/shopsys/shopsys/pull/2218))

- replace html icon tags with new twig tag icon ([#2274](https://github.com/shopsys/shopsys/pull/2274))
    - search for all occurrences of
        ```twig
        <i class="svg svg-<svg-icon-name>"></i>
        ```
        and replace it by
        ```twig
        {{ icon('<svg-icon-name>') }}
        ```
        Full example:
        ```diff
        -   <i class="svg svg-question"></i>
        +   {{ icon('question') }}
        ```
    - for more information read our article [Icon function](https://docs.shopsys.com/en/9.1/frontend/icon-function/)

- replace deprecated authentication constants in Frontend API ([#2279](https://github.com/shopsys/shopsys/pull/2279))
    - this step is necessary only when you are using Frontend API and you have overridden constants from `Shopsys\FrontendApiBundle\Model\Token\TokenAuthenticator`
        - update your `services.yaml` by adding arguments to `TokenAuthenticator`
            
            ```diff
            +   Shopsys\FrontendApiBundle\Model\Token\TokenAuthenticator:
            +       arguments:
            +           $authenticationHeader: '%env(FRONTEND_API_AUTHENTICATION_HEADER)%'
            +           $authenticationScheme: '%env(FRONTEND_API_AUTHENTICATION_SCHEME)%'
            ```

        - following constants are deprecated and should be replaced by environment variables:
            - replace `HEADER_AUTHORIZATION` by an environment variable `FRONTEND_API_AUTHENTICATION_HEADER` 
            - replace `BEARER` by an environment variable `FRONTEND_API_AUTHENTICATION_SCHEME` 
