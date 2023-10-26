# Introduction to Automated Testing

Testing is a crucial part of the development and maintenance of reliable software.  
For this reason, Shopsys Platform comes with 5 types of automated tests:

-   [Unit tests](#unit-tests)
-   [Functional tests](#functional-tests)
-   [Application tests](#application-tests)
-   [HTTP smoke tests](#http-smoke-tests)
-   [Acceptance tests](#acceptance-tests-aka-functional-tests-or-selenium-tests)
-   [Performance tests](#performance-tests)

Software testing, in general, is a very broad topic and requires learning and practice.
The following paragraphs should help you in your path to answering questions like _"What should I test?"_, _"Which type of tests should I use for this particular functionality?"_ or _"How many tests is enough?"_

## The purpose(s) of automated tests

For code that you are currently writing, tests can give you immediate feedback that your code works.
In connection with _tests-first_ approach (e.g., _Test-Driven Development_), tests also help you design your code because you focus on how the production code will be used before you write it.

Existing tests give you the confidence to make changes and refactoring without breaking things.
They will notify you when something previously worked no longer works and help you localize the error.

Tests can also help you when reading new code written by someone else. Tests can be seen as runnable documentation that shows how the code should be used.
High-level tests (e.g., [acceptance tests](#acceptance-tests-aka-functional-tests-or-selenium-tests)) can be used to discover how users can interact with the application.

## Rules of thumb for what should be tested

### Is the functionality critical for your business?

If the answer is yes, then you should test the feature thoroughly.
It would be best if you write automated tests for all crucial scenarios.

You can even test the part using multiple types of tests (e.g., both [unit tests](#unit-tests) and [acceptance tests](#acceptance-tests-aka-functional-tests-or-selenium-tests)).
Let's say that we consider promo codes to be a crucial part of the business.
There will be two types of promo codes: fixed price (e.g., $10 from the total price) and percentage (e.g., 15% from the total price).  
We could write unit tests to calculate the discount for both promo code types.
But if working promo codes are essential for us, we should also write an end-to-end acceptance test to verify that the user can add a promo code to the order and that the discount is really applied to a created order.

### Does some part of the application break often?

You may have already encountered a situation when some software feature used to work properly but is broken in the current release.
This type of issue is so common that it even has its own name - _a regression bug_.

In an ideal world, every feature is tested from the beginning, so regression bugs do not arise.
But in reality, it is tough (and costly) to test every aspect of your application.

However, if you run into a bug in a feature that used to work before, it is a good sign that the code implementing the feature is brittle and should be verified by tests.
Also, nobody wants angry users to repeatedly report the same bug that was already fixed once.
It is a good practice to write tests that verify the fixing of regression bugs.

### Do you want to refactor some existing functionality?

Refactoring is a process of enhancing code quality without changing its functionality.

If you want to refactor some parts of your application, you should have automatic tests beforehand to ensure that you did not break the application during the refactoring.

### Does your code depend on undocumented features?

When your application depends on some specific feature in a 3rd party system that is not documented, you can write tests to verify the expected behavior.

The fact that the feature is not documented may indicate that the authors did not consider the behavior a real feature and may change in future versions. If it does, you will be notified by your tests.

## Types of automated tests available in Shopsys Platform

### Unit tests

They are used to test the smallest possible amount of code (the "unit", i.e. class/method). To isolate the tested unit, it is useful to mock other objects - create a dummy object mimicking the real implementation of collaborating classes.

Unit tests in Shopsys Platform are built on [PHPUnit testing framework](https://phpunit.de/).

#### Advantages:

-   execution is really fast
-   precise localization of the problem

#### Disadvantages:

-   tested code must be designed in a specific way (e.g., using _dependency injection principle_)
-   mocking sometimes leads to unintuitive behavior (e.g., returning `null` when not expected)

#### Great for:

-   testing isolated components with clear responsibilities
-   testing edge cases (using large data sets)
-   test driven development

#### Example:

See test class `\Tests\FrameworkBundle\Unit\Model\Cart\CartTest` in the `shopsys/framework` package.
Notice that test method names describe the tested scenario. Also, notice that each test case focuses just on one specific class behavior.
When a test fails, it provides detailed feedback to the developer.

You can create similar unit tests anywhere in your directory `tests/Unit/`.
If they are named with the prefix `Test` and are extending `\PHPUnit\Framework\TestCase`, they will be executed during the [`tests` Phing target](../introduction/console-commands-for-application-management-phing-targets.md#tests).

### Functional tests

Even when all parts are working, it is not guaranteed they work well together. Mocking can still be used for isolation when appropriate.

Functional tests build DI container, so you can get any service you want. The service includes all dependencies as in a real application, so you test how the service **functions**.
These tests use a separate database not to affect your application data, so you can still use the application in _DEVELOPMENT_ environment. It is still **not recommended** to run tests on a production server because things like the filesystem are shared among all kernel environments.

#### Advantages:

-   demo data can be used for testing with [`PersistentReferenceFacade`](https://github.com/shopsys/framework/blob/master/src/Component/DataFixture/PersistentReferenceFacade.php)

#### Disadvantages:

-   arranging the testing data is typically more complex than in unit tests

#### Great for:

-   higher level testing of collaboration of units
-   low-level testing of components that are hard to unit-test

#### Example:

See test class [`\Tests\App\Functional\Model\Cart\CartFacadeTest`](https://github.com/shopsys/shopsys/blob/master/project-base/tests/App/Functional/Model/Cart/CartFacadeTest.php). Notice usage of demo data instead of preparing own entities.

#### Choose base test class

We have two base classes that you can choose from

##### [`\Tests\App\Test\TransactionFunctionalTestCase`](https://github.com/shopsys/shopsys/blob/master/project-base/tests/App/Test/TransactionFunctionalTestCase.php)

All tests are isolated from each other thanks to database transactions. This means they can be executed in any order, as each has the same starting conditions.
`TransactionFunctionalTestCase` is always a safe choice.

##### [`\Tests\App\Test\FunctionalTestCase`](https://github.com/shopsys/shopsys/blob/master/project-base/tests/App/Test/FunctionalTestCase.php)

Tests do not use database transactions, so they are quicker.
Use `FunctionalTestCase` if you are sure that you won't commit anything to the database.

### Application tests

When you need to check your direct response from the application by accessing it directly via URL, application tests are the way to go.
`ApplicationTestCase` is used, for example, in our frontend API testing `GraphlQlTestCase`

#### Advantages:

-   application behavior is tested directly, not via mocked code

#### Disadvantages:

-   tests can be slower than functional or unit tests

#### Example:

See test class [`\Tests\App\Functional\Controller\HomepageControllerTest`](https://github.com/shopsys/shopsys/blob/master/project-base/tests/App/Functional/Controller/HomepageControllerTest.php).

### HTTP smoke tests

Test HTTP codes returned by individual controller actions provided by the routing (e.g., product detail page should return _200 OK_ for a visible product and _404 Not Found_ for a hidden one).

They help you prevent breaking your application by checking the very wide scope of the application.
You will no longer cause _500 Server Error_ on some random page by a seemingly unrelated change.

#### Advantages:

-   all new controller actions are checked automatically (almost maintenance free)

#### Disadvantages:

-   validate only HTTP codes, not the actual contents

#### Great for:

-   protection from unhandled exceptions in controller actions

#### Example:

See configuration of HTTP smoke (and [performance](#performance-tests)) tests in [`\Tests\App\Smoke\Http\RouteConfigCustomization`](https://github.com/shopsys/shopsys/blob/master/project-base/tests/App/Smoke/Http/RouteConfigCustomization.php).

!!! tip

    You can read more about the customization of HTTP smoke tests in their [own documentation on GitHub](https://github.com/shopsys/http-smoke-testing).

### Acceptance tests (a.k.a. functional tests or Selenium tests)

Provide a way of fully end-to-end testing your application as if a real human used it.

Built on [Selenium](http://www.seleniumhq.org/) and [Codeception](http://codeception.com/), running in [Google Chrome](https://www.google.com/chrome/) browser.

More information can be found in [Running Acceptance Tests](running-acceptance-tests.md).

#### Advantages:

-   end-to-end testing
-   cover errors that occur only in the browser
-   can test JavaScript code
-   demo data can be used for testing with [`PersistentReferenceFacade`](https://github.com/shopsys/framework/blob/master/src/Component/DataFixture/PersistentReferenceFacade.php)

#### Disadvantages:

-   take a while to execute
-   whole application is switched to _ACCEPTANCE_ environment
-   occasional false negative reports (due to WebDriver brittleness)
-   requires installation of [Google Chrome](https://www.google.com/chrome/browser/desktop/) and [ChromeDriver](https://sites.google.com/a/chromium.org/chromedriver/)

#### Great for:

-   validating business-critical scenarios (e.g., order creation)

#### Example:

See acceptance test for product filter in administration in [`\Tests\App\Acceptance\acceptance\AdminProductAdvancedSearchCest`](https://github.com/shopsys/shopsys/blob/master/project-base/tests/App/Acceptance/acceptance/AdminProductAdvancedSearchCest.php). Notice the usage of auto-wired Page objects [`LoginPage`](https://github.com/shopsys/shopsys/blob/master/project-base/tests/App/Acceptance/acceptance/PageObject/Admin/LoginPage.php) and [`ProductAdvancedSearchPage`](https://github.com/shopsys/shopsys/blob/master/project-base/tests/App/Acceptance/acceptance/PageObject/Admin/ProductAdvancedSearchPage.php). They provide a way to reuse code that interacts with user interface.

### Performance tests

These tests assert that key actions do not take too long. They are similar to [HTTP smoke tests](#http-smoke-tests) but also measure response time. In addition to routes tested by HTTP smoke tests, these tests also request and measure the regeneration of all product feeds.

Before execution of the test suite, the testing database is filled with a large amount of data simulating the production environment of a big e-commerce project. You will no longer unknowingly slow down a page because you are developing with only a small data set.

It is advised to run these tests on a separate server that is not under load at the time for consistent results (e.g., only at nighttime).

#### Advantages:

-   can test performance on large amounts of data

#### Disadvantages:

-   takes really long time to execute (approx. 1.5 hours including import of performance data)
-   must be running on a server without load for consistent results

#### Great for:

-   discovering the performance impact of code modifications
-   preventing application collapse on production data load

#### Example:

See configuration of performance (and [HTTP smoke](#http-smoke-tests)) tests in [`\Tests\App\Smoke\Http\RouteConfigCustomization`](https://github.com/shopsys/shopsys/blob/master/project-base/tests/App/Smoke/Http/RouteConfigCustomization.php).

For testing the performance of something other than controller actions, see the implementation of the feed performance test in [`\Tests\App\Performance\Feed\AllFeedsTest`](https://github.com/shopsys/shopsys/blob/master/project-base/tests/App/Performance/Feed/AllFeedsTest.php).

## How many tests should you write

> The crucial question you should ask yourself is this: Do I care about the future of my code?  
> Tests are meant to allow for safe refactoring later on.
>
> \- Matthias Noback in _Principles of package design_

There is no definite answer to how many tests are enough.
It depends on how much we want to be sure that things will not break in future and how much time are we willing to invest into that.

Be aware that very high test coverage can lead to expensive maintenance that may outweigh the benefits.

## Running tests

This command builds a clean test environment, initializes the test data into a separate database, and prepares the tests to run.
Subsequently, unit, functional and smoke tests are performed.

```sh
php phing tests
```

### Run only functional tests

If you have already run tests and want to run only functional tests, you can use the following command to do so.
However, keep in mind that the test environment must already be prepared, see previous point.

```sh
php phing tests-functional
```

### Run a single functional test

Sometimes, you may want to debug individual test without running the whole functional test suite (which can take several minutes).

```sh
php phing tests-functional-single
```

### Run only smoke tests

If you have already run tests and want only smoke tests, use the following command.
However, keep in mind that the test environment must already be prepared, see previous point.

```sh
php phing tests-smoke
```
