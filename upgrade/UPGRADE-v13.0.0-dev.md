# [Upgrade from v12.0.0 to v13.0.0-dev](https://github.com/shopsys/shopsys/compare/v12.0.0...13.0)

This guide contains instructions to upgrade from version v12.0.0 to v13.0.0-dev.

**Before you start, don't forget to take a look at [general instructions](https://github.com/shopsys/shopsys/blob/13.0/UPGRADE.md) about upgrading.**
There you can find links to upgrade notes for other versions too.

- split functional and frontend-api tests into separate suites ([#2641](https://github.com/shopsys/shopsys/pull/2641))
    - see #project-base-diff to update your project
- use TestCurrencyProvider from the framework ([#2662](https://github.com/shopsys/shopsys/pull/2662))
    - remove class `Tests\App\Functional\Model\Pricing\Currency\TestCurrencyProvider` and use `Tests\FrameworkBundle\Test\Provider\TestCurrencyProvider` instead
    - see #project-base-diff to update your project
- fix S3Bridge bundle name ([#2648](https://github.com/shopsys/shopsys/pull/2648))
    - see #project-base-diff to update your project
- add opening hours to stores ([#2660](https://github.com/shopsys/shopsys/pull/2660))
    - `Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig`
        - method `__construct` changed its interface
            ```diff
                public function __construct(
                    $id,
                    $url,
                    $name,
                    $locale,
            +       DateTimeZone $dateTimeZone,
                    $stylesDirectory = self::STYLES_DIRECTORY_DEFAULT,
                    $stylesDirectory = self::STYLES_DIRECTORY_DEFAULT,
                    $designId = null,
                ) {
            ```
    - to start using opening hours of stores set store opening hours in administration or create specialized migrations for it
        - after update add this to your migration: `$this->sql('ALTER TABLE stores DROP COLUMN opening_hours');`
- change url for listing personal detail from email ([#2725](https://github.com/shopsys/shopsys/pull/2725))
    - see #project-base-diff to update your project
- fix product video UX ([#2746](https://github.com/shopsys/shopsys/pull/2746))
    - see #project-base-diff to update your project
