# [Upgrade from v9.1.2 to v9.1.3-dev](https://github.com/shopsys/shopsys/compare/v9.1.2...9.1)

This guide contains instructions to upgrade from version v9.1.2 to v9.1.3-dev.

**Before you start, don't forget to take a look at [general instructions](https://github.com/shopsys/shopsys/blob/7.3/UPGRADE.md) about upgrading.**
There you can find links to upgrade notes for other versions too.

- fix memory_limit set for PHPUnit ([#2398](https://github.com/shopsys/shopsys/pull/2398))
    - see #project-base-diff to update your project
- **\[BC break\]** upgrade `codeception/codeception` to `^4.1.22` so you get rid of [the security problem](https://github.com/advisories/GHSA-4574-qv3w-fcmg)
  - you might need to update your `Tests\App\Test\Codeception\AcceptanceTester` to respect changes (added strict typehints) in `Tests\FrameworkBundle\Test\Codeception\ActorInterface`
  - in `StrictWebDriver::seeInElement`, use `assertStringContainsString` instead of `assertContains`
  - see #project-base-diff
