# [Upgrade from v9.1.2 to v9.1.3](https://github.com/shopsys/shopsys/compare/v9.1.2...9.1)

This guide contains instructions to upgrade from version v9.1.2 to v9.1.3.

**Before you start, don't forget to take a look at [general instructions](https://github.com/shopsys/shopsys/blob/7.3/UPGRADE.md) about upgrading.**
There you can find links to upgrade notes for other versions too.

- fix memory_limit set for PHPUnit ([#2398](https://github.com/shopsys/shopsys/pull/2398))
    - see #project-base-diff to update your project
- update your composer dependencies ([#2397](https://github.com/shopsys/shopsys/pull/2397)) (see #project-base-diff)
  - **\[BC break\]** upgrade `codeception/codeception` to `^4.1.22` so you get rid of [the security problem](https://github.com/advisories/GHSA-4574-qv3w-fcmg)
    - you might need to update your `Tests\App\Test\Codeception\AcceptanceTester` to respect changes (added strict typehints) in `Tests\FrameworkBundle\Test\Codeception\ActorInterface`
    - in `StrictWebDriver::seeInElement`, use `assertStringContainsString` instead of `assertContains`
    - beware, the entry in `phpstan.neon` was fixed in the follow-up pull request ([#2404](https://github.com/shopsys/shopsys/pull/2404))
      - see #project-base-diff to update your project
  - allow plugins in your `composer.json`
    - this is required when using composer in version `2.2.0` and above. If you are running your project in docker, you might need to re-build your docker image to get the upgraded composer.
  - upgrade `composer/composer` to `^1.10.23` so you get rid of [the security problem](https://github.com/composer/composer/security/advisories/GHSA-frqg-7g38-6gcf) 
  - fix your standards
    - extract parts of `CustomerUserDataFixture::getCustomerUserUpdateData()` into private methods to lower the cyclomatic complexity of the method
  - see #project-base-diff to update your project
- extract part of `install.sh` script into a new separate file (`configure.sh`) ([#2404](https://github.com/shopsys/shopsys/pull/2404))
  - see #project-base-diff to update your project
- update to latest version heureka/overeno-zakazniky package ([#2534](https://github.com/shopsys/shopsys/pull/2534))
  - see #project-base-diff to update your project
