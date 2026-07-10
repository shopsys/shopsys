---
name: test-writing
description: >
  This skill MUST be used when the user asks to write tests, add tests, create test cases,
  run/execute/re-run/debug tests, test a specific class or method, or when working on any
  *Test.php file in this Shopsys application. Applies codebase-specific best practices across
  unit, functional, GraphQL API, smoke, and acceptance test layers.
version: 1.0.0
---

> **Monorepo note:** if the repository has top-level `packages/` and `project-base/` directories (the shopsys/shopsys monorepo layout, not a standalone project), also read `.agents/skills/monorepo-vs-project/SKILL.md` and apply its delta (package-first + path/role remap) on top of this skill.

# Shopsys Test Writing Guide

## Trigger Scope

Use this skill whenever the request is about tests in this repository, including:
- writing or modifying tests
- running or re-running tests
- debugging failing tests
- selecting the right command/suite/configuration for a test run

## 1. Layer Selection — Pick the Right Base Class First

```
What am I testing?
│
├─ Pure logic, no I/O, all deps injectable or mockable?
│   └─► Unit Test
│       extends PHPUnit\Framework\TestCase
│       namespace mirrors the source location:
│         app/src/...  → Tests\App\Unit\...
│
├─ Service that reads+writes the DB (mutations, creates, deletes)?
│   └─► TransactionFunctionalTestCase  ← auto-rollback after each test
│       extends Tests\App\Test\TransactionFunctionalTestCase
│       namespace Tests\App\Functional\...
│
├─ Service that only reads the DB (no mutations)?
│   └─► FunctionalTestCase
│       extends Tests\App\Test\FunctionalTestCase
│       namespace Tests\App\Functional\...
│
├─ GraphQL API endpoint?
│   ├─ Anonymous request            → GraphQlTestCase
│   ├─ Authenticated B2C customer   → GraphQlWithLoginTestCase  (auto-calls login())
│   ├─ B2B domain, anonymous        → GraphQlB2bDomainTestCase
│   └─ B2B domain + authenticated   → GraphQlB2bDomainWithLoginTestCase
│   All in namespace Tests\FrontendApiBundle\Functional\...
│
├─ HTTP endpoint returns the correct status code?
│   └─► Smoke test via RouteConfigCustomization
│       app/tests/App/Smoke/Http/
│
├─ Admin UI workflow (Symfony/Twig)?
│   └─► Acceptance test (Codeception + Selenium)
│       app/tests/App/Acceptance/
│
└─ Storefront user flow (Next.js)?
    └─► E2E test (Cypress)
        storefront/cypress/
```

**Always state the chosen layer and why** before writing the first line of code.

---

## 2. Behavioral Testing — The Golden Rule

> **Mental check**: "Would this test still pass if I emptied the method body?"
> If YES → the test is wrong. Rewrite it to assert observable state or output.

### Anti-pattern: interaction mock that survives an empty body

```php
// BAD — the mock records the call but can't verify what the method does
$mailerMock->expects($this->atLeastOnce())->method('send');

// If send() body is deleted, the mock still satisfies ->expects()
// because PHPUnit replaces the method with a stub regardless.
```

### Preferred: assert observable state

```php
// GOOD — assert what changed in the system
$order = $this->orderFacade->getById($orderId);
$this->assertSame(OrderStatus::STATUS_DONE, $order->getStatus()->getId());

// GOOD — assert DB side-effects
$this->em->clear();
$product = $this->productFacade->getById($productId);
$this->assertSame('Updated Name', $product->getName($this->getFirstDomainLocale()));
```

**When mock expectations ARE appropriate**: verifying that an external service was
called (e.g., email dispatch to a real mailer you cannot query). In that case use
`createMock()` — but document why a state assertion is not possible.

Use `createStub()` for pure return-value fakes (no expectation on calls).

---

## 3. AAA Pattern

Always structure tests as Arrange / Act / Assert with a blank line between sections:

```php
public function testProductIsSoldOutAfterStockIsDepleted(): void
{
    $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);
    $productData = $this->productDataFactory->createFromProduct($product);
    $productData->stockQuantity = 0;

    $this->productFacade->edit($product->getId(), $productData);
    $this->handleDispatchedRecalculationMessages();
    $this->em->clear();

    $updated = $this->productFacade->getById($product->getId());
    $this->assertTrue($updated->isCalculatedSellingDenied(Domain::FIRST_DOMAIN_ID));
}
```

---

## 4. Naming Conventions

- Method names: **camelCase**, start with `test`, describe the **scenario not the implementation**
- Good: `testOrderStatusChangesToShippedWhenTrackingNumberIsSet`
- Bad: `testUpdateOrderStatusMethod`, `testOrderFacade`
- Class name: `<TestedClass>Test`, in the same namespace mirror as the source

### Data Providers

Use `#[DataProvider]` (PHP attribute, **not** the `@dataProvider` docblock — that was removed in PHPUnit 10+). The provider method must be `public static` and return `iterable`. Use named `yield` keys for readable test output:

```php
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @return iterable<string, array{stopOnFailure: bool, expectedCalls: int}>
 */
public static function getStopOnFailureData(): iterable
{
    yield 'stops on first failure when enabled' => ['stopOnFailure' => true, 'expectedCalls' => 1];
    yield 'continues after failure when disabled' => ['stopOnFailure' => false, 'expectedCalls' => 2];
}

#[DataProvider('getStopOnFailureData')]
public function testRunScheduledModulesRespectsStopOnFailureConfiguration(
    bool $stopOnFailure,
    int $expectedCalls,
): void { ... }
```

---

## 5. Service Injection — Always Use @inject

Never use `$this->getContainer()->get(...)` in functional/GraphQL tests.

```php
/**
 * @inject
 */
private ProductFacade $productFacade;

/**
 * @inject
 */
private ProductDataFactory $productDataFactory;
```

Rules:
- Always use `private` visibility
- The annotation is a docblock: `/** @inject */`, not a PHP attribute
- Injection happens automatically in `setUp()` — no manual wiring needed

---

## 6. Fixture References

```php
// Global reference
$brand = $this->getReference(BrandDataFixture::BRAND_CANON, Brand::class);

// Domain-specific reference
$vat = $this->getReferenceForDomain(VatDataFixture::VAT_HIGH, Domain::FIRST_DOMAIN_ID, Vat::class);
$seoMix = $this->getReferenceForDomain(
    ReadyCategorySeoDataFixture::READY_CATEGORY_SEO_TV_FROM_CHEAPEST,
    1,
    ReadyCategorySeoMix::class,
);

// Refresh after flush
$this->em->clear();
$fresh = $this->productFacade->getById($product->getId());
```

Fixture classes live in `app/src/DataFixtures/Demo/`.
Always use the class constant (e.g. `BrandDataFixture::BRAND_CANON`), never raw strings.

---

## 7. GraphQL API Tests

### File layout

```
app/tests/FrontendApiBundle/Functional/Brand/
├── BrandTest.php
└── graphql/
    └── BrandQuery.graphql     ← never inline the GQL string in PHP
```

### The helper trio

```php
// 1. Send the query
$response = $this->getResponseContentForGql(
    __DIR__ . '/graphql/BrandQuery.graphql',
    ['uuid' => $this->brand->getUuid()],
);

// 2. Extract data (fails the test if 'errors' key is present)
$data = $this->getResponseDataForGraphQlType($response, 'brand');

// 3. Access errors when testing error paths
$errors = $this->getErrorsFromResponse($response);
```

### Assertion helpers

```php
$this->assertResponseContainsArrayOfDataForGraphQlType($response, 'products');
$this->assertResponseContainsArrayOfErrors($response);
$this->assertResponseContainsArrayOfExtensionValidationErrors($response);
$this->assertAccessDeniedError($response);
$this->assertUserError($response, 'user-code', 403);
```

### Login variant

```php
// GraphQlWithLoginTestCase calls $this->login() automatically in setUp()
// Credentials: CommonGraphQlWithLoginTestCase::DEFAULT_USER_EMAIL / DEFAULT_USER_PASSWORD
```

---

## 8. Functional Test Skeleton

```php
<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use App\Model\Product\ProductFacade;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Tests\App\Test\TransactionFunctionalTestCase;

final class ProductFacadeTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private ProductFacade $productFacade;

    private Product $product;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);
    }

    public function testProductNameIsUpdated(): void
    {
        $productData = $this->productDataFactory->createFromProduct($this->product);
        $productData->name[$this->getFirstDomainLocale()] = 'New Name';

        $this->productFacade->edit($this->product->getId(), $productData);
        $this->em->clear();

        $updated = $this->productFacade->getById($this->product->getId());
        $this->assertSame('New Name', $updated->getName($this->getFirstDomainLocale()));
    }
}
```

---

## 9. GraphQL Test Skeleton

```php
<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use Override;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

final class ProductQueryTest extends GraphQlTestCase
{
    private Product $product;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);
    }

    public function testProductQueryReturnsCorrectName(): void
    {
        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/ProductQuery.graphql',
            ['uuid' => $this->product->getUuid()],
        );
        $data = $this->getResponseDataForGraphQlType($response, 'product');

        $this->assertSame(
            $this->product->getName($this->getFirstDomainLocale()),
            $data['name'],
        );
    }
}
```

---

## 10. Code Style Checklist

- `declare(strict_types=1);` at the top of every test file
- `final` on all test classes
- `private` properties/methods in all test classes
- `#[Override]` attribute on every `setUp()` / `tearDown()` override
- Full type hints and docblocks on all injected properties (aids IDE autocompletion)
- **Don't promote `setUp()` locals to class properties unless test methods need them.** If a variable is only used inside `setUp()` to build shared state (e.g. a stub used solely to register a service and extract its class name), keep it local. Only promote to a property when at least one test method references it directly.

---

## 11. Running Tests

All commands below run **inside the `php-fpm` container**.

### Running a single test with phpunit

Use `vendor/bin/phpunit` with the correct `--configuration` for where the test lives:

```bash
# Functional / Frontend-API / Smoke / Unit test
vendor/bin/phpunit --colors=always \
  --configuration app/phpunit.xml \
  --testsuite <Suite> \
  --filter <testMethodOrClassName>
```

Pick `--testsuite` to match the test layer:

| Layer | `--testsuite` | `--configuration` |
|---|---|---|
| Unit | `Unit` | `app/phpunit.xml` |
| Functional | `Functional` | `app/phpunit.xml` |
| Frontend API | `FrontendApiFunctional` | `app/phpunit.xml` |
| Frontend API B2B | `FrontendApiFunctionalB2b` | `app/phpunit.xml` |
| Smoke | `Smoke` | `app/phpunit.xml` |

### Running test suites via Phing

```bash
php phing tests              # all (unit + functional + smoke)
php phing tests-unit         # unit tests
php phing tests-functional   # = tests-functional-application + tests-functional-frontend-api + tests-functional-frontend-api-b2b
```

### phpunit.xml strict configuration

The `phpunit.xml` configs have strict flags (`failOnNotice`, `failOnPhpunitNotice`, `failOnWarning`, etc.). The most common hidden failure:

### `createMock()` without expectations → PHPUnit Notice → build failure

Phing treats PHPUnit Notices as errors. A mock created with `createMock()` that has no `expects(...)` call triggers:

```
No expectations were configured for the mock object [...].
Consider refactoring your test code to use a test stub instead.
```

**Rule**: Use `createStub()` when you only need return-value faking. Use `createMock()` only when you also set `expects(...)`.

```php
// BAD — createMock() with no expects() → PHPUnit Notice → build failure
$facadeMock = $this->createMock(SomeFacade::class);
$facadeMock->method('find')->willReturn($entity);

// GOOD — createStub() for pure return-value fakes
$facadeStub = $this->createStub(SomeFacade::class);
$facadeStub->method('find')->willReturn($entity);

// GOOD — createMock() when verifying a call was made
$mailerMock = $this->createMock(Mailer::class);
$mailerMock->expects($this->once())->method('send');
```

### Two stubs of the same interface share the same class name

`createStub(FooInterface::class)` called twice returns objects of the **same generated class**. If you register both in an array keyed by `get_class()`, the second overwrites the first — only one entry survives.

**Rule**: Use explicit string keys as service IDs instead of `get_class()`. The service ID is just an arbitrary string — it doesn't need to match the class name:

```php
// BAD — both stubs have the same class name; array collapses to one entry
$firstStub = $this->createStub(SimpleCronModuleInterface::class);
$secondStub = $this->createStub(SimpleCronModuleInterface::class);
$services = [get_class($firstStub) => $firstStub, get_class($secondStub) => $secondStub]; // only 1 entry!

// GOOD — one stub registered under two distinct explicit service IDs
$moduleStub = $this->createStub(SimpleCronModuleInterface::class);
$cronConfig = $this->createCronConfigWithRegisteredServices(['first' => $moduleStub, 'second' => $moduleStub]);
```

---

## Related

- Architecture and how to extend functionality: `.agents/skills/shopsys-architecture/SKILL.md`
- Finding where code lives: `.agents/skills/codebase-locator/SKILL.md`
