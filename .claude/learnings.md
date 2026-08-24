# Learnings

Temporary storage for coding patterns and lessons learned. Weekly review will convert these into specific skills.

---

## Backend PHP Development Patterns

### Domain Logic Placement

**Rule**: Business logic belongs in Entity, not in Resolver/Loader/BatchLoader

**Ask yourself**: "Is this a property OF the entity itself?"

| Location            | What belongs there                                        |
| ------------------- | --------------------------------------------------------- |
| **Entity**          | `hasPriceBasedOrdering()`, `isActive()`, `canBeDeleted()` |
| **Facade**          | Orchestration, transactions, calling multiple services    |
| **Resolver/Loader** | Data fetching, filtering based on entity methods          |

**Bad** - logic in BatchLoader:

```php
// ReadyCategorySeoMixBatchLoader.php
protected function isPriceBasedOrdering(?string $ordering): bool
{
    return in_array($ordering, ['price_asc', 'price_desc'], true);
}
```

**Good** - logic in Entity:

```php
// ReadyCategorySeoMix.php (Entity)
public function hasPriceBasedOrdering(): bool
{
    return in_array($this->ordering, [
        ProductListOrderingConfig::ORDER_BY_PRICE_ASC,
        ProductListOrderingConfig::ORDER_BY_PRICE_DESC,
    ], true);
}

// BatchLoader just uses it:
array_filter($mixes, fn ($mix) => !$mix->hasPriceBasedOrdering());
```

---

### Nullable Types Verification

**Rule**: Don't blindly copy nullable (?) from getter signature

**Problem**: Property/getter may be typed as `?string` but business logic guarantees non-null value.

**Process**:

1. Check if value can actually be `null` in database schema
2. Check entity creation - is value always set?
3. Ask senior developer if unsure about business constraints

**Bad** - copying nullable without verification:

```php
// Getter returns ?string
public function getOrdering(): ?string

// Blindly copied to new method
protected function isPriceBasedOrdering(?string $ordering): bool  // Wrong!
```

**Good** - verified with business logic:

```php
// Senior confirmed: ordering is always set at creation, never null
public function hasPriceBasedOrdering(): bool  // No parameter needed
{
    return in_array($this->ordering, [...], true);
}
```

---

### Visibility in packages/ vs project-base/

| Location        | Visibility  | Typehints          | `final`              |
| --------------- | ----------- | ------------------ | -------------------- |
| `packages/`     | `protected` | NO (entities/data) | NO (except FormType) |
| `project-base/` | `private`   | YES everywhere     | YES                  |

**Reason**: `packages/` classes are meant to be extended in `project-base/`

---

## Security Patterns

### Complete Access Control

**Rule**: When filtering UI visibility, ALWAYS also block direct URL/API access

**Think**: "What if user knows the URL directly?"

#### Three Layers of Access Control

```
┌─────────────────────────────────────────────────────┐
│ Layer 1: UI Filtering                               │
│ - Hide links/buttons user shouldn't see             │
│ - Filter items in lists                             │
│ - NOT SUFFICIENT ALONE!                             │
├─────────────────────────────────────────────────────┤
│ Layer 2: API/Query Protection                       │
│ - Block in GraphQL resolvers/queries                │
│ - Return 403 or 404 for unauthorized access         │
│ - Validate permissions before returning data        │
├─────────────────────────────────────────────────────┤
│ Layer 3: Business Logic Protection                  │
│ - Check permissions in Facade methods               │
│ - Validate in Entity if applicable                  │
│ - Last line of defense                              │
└─────────────────────────────────────────────────────┘
```

**Incomplete** (only UI filtering):

```php
// ReadyCategorySeoMixBatchLoader.php
// Filters SEO categories in the list - user doesn't see them
$filteredMixes = array_filter($mixes, fn ($mix) => !$mix->hasPriceBasedOrdering());
```

**Complete** (UI + API protection):

```php
// 1. BatchLoader - filters from list (UI)
$filteredMixes = array_filter($mixes, fn ($mix) => !$mix->hasPriceBasedOrdering());

// 2. ReadyCategorySeoMixQuery - blocks direct URL access (API)
public function categoryOrSeoMixByUuidOrUrlSlugQuery(...)
{
    $seoMix = $this->readyCategorySeoMixFacade->getById($id);

    if ($seoMix->hasPriceBasedOrdering() && !$this->canSeePrices()) {
        throw new AccessDeniedUserError('Access denied');
        // Or return 404 to hide existence
    }

    return $seoMix;
}
```

---

### Permission Check Placement

| Check Type                  | Where                  | Example                             |
| --------------------------- | ---------------------- | ----------------------------------- |
| **Can access feature?**     | Controller/Resolver    | `#[CanView]`, `#[ForRole(...)]`     |
| **Can see specific data?**  | Query/BatchLoader      | Filter results based on permissions |
| **Can perform action?**     | Facade                 | Throw exception if not allowed      |
| **Is data valid for user?** | Entity (if applicable) | `$entity->isAccessibleBy($user)`    |

---

### Security Checklist for New Features

Before marking feature as complete:

- [ ] UI elements hidden for unauthorized users
- [ ] Direct URL access blocked (returns 403 or 404)
- [ ] API endpoints protected
- [ ] Links/references to protected resources filtered
- [ ] Error messages don't leak sensitive information

---

## Testing Patterns

### Test Base Classes - Quick Reference

| Scenario                                | Base Class                          |
| --------------------------------------- | ----------------------------------- |
| Unit tests (no DB)                      | `PHPUnit\Framework\TestCase`        |
| Functional tests (DB access)            | `FunctionalTestCase`                |
| Functional with transactions            | `TransactionFunctionalTestCase`     |
| GraphQL API tests (anonymous)           | `GraphQlTestCase`                   |
| GraphQL API tests (logged in B2C)       | `GraphQlWithLoginTestCase`          |
| **GraphQL API tests (B2B domain)**      | `GraphQlB2bDomainTestCase`          |
| **GraphQL API tests (B2B + logged in)** | `GraphQlB2bDomainWithLoginTestCase` |

### Hierarchy

```
PHPUnit\Framework\TestCase
└── ApplicationTestCase
    └── WebTestCase
        └── FunctionalTestCase
            ├── TransactionFunctionalTestCase
            │   └── ParameterTransactionFunctionalTestCase
            └── GraphQlTestCase
                ├── GraphQlWithLoginTestCase
                │   └── CommonGraphQlWithLoginTestCase
                └── GraphQlB2bDomainTestCase
                    └── GraphQlB2bDomainWithLoginTestCase
```

---

### B2B / Limited User Tests

**Rule**: Use `GraphQlB2bDomainWithLoginTestCase` for limited user permissions

**When to use**:

- Testing features for users without price visibility
- Testing B2B-specific functionality
- Testing role-based access control

**Example**:

```php
class CategorySeoWithLimitedUserTest extends GraphQlB2bDomainWithLoginTestCase
{
    public function testLimitedUserDoesNotSeePriceBasedSeoCategories(): void
    {
        // User is already logged in with limited permissions
        $response = $this->getResponseContentForGql(...);

        // Assert price-based content is hidden
        $this->assertNotContains('/price-sorted-category', $slugs);
    }
}
```

**Wrong** - manually setting up limited user:

```php
class MyTest extends GraphQlWithLoginTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Manually changing user role - unnecessary boilerplate!
        $this->logout();
        $customerUser = $this->getReference(...);
        $customerUserData->roleGroup = $this->getReference(
            CustomerUserRoleGroupDataFixture::ROLE_GROUP_LIMITED_USER
        );
        $this->login();
    }
}
```

---

### Service Injection in Tests

**Rule**: Use `@inject` annotation, not `$this->getContainer()->get()`

**Good**:

```php
class MyTest extends FunctionalTestCase
{
    /**
     * @inject
     */
    private ProductFacade $productFacade;

    public function testSomething(): void
    {
        $product = $this->productFacade->getById(1);
    }
}
```

**Bad**:

```php
class MyTest extends FunctionalTestCase
{
    public function testSomething(): void
    {
        // Don't do this!
        $productFacade = $this->getContainer()->get(ProductFacade::class);
    }
}
```

---

### Test File Locations

| Test Type         | Location                                               |
| ----------------- | ------------------------------------------------------ |
| Unit tests        | `project-base/app/tests/App/Unit/`                     |
| Functional tests  | `project-base/app/tests/App/Functional/`               |
| Smoke tests       | `project-base/app/tests/App/Smoke/`                    |
| GraphQL API tests | `project-base/app/tests/FrontendApiBundle/Functional/` |

---

### Common Test Patterns

#### Getting References to Fixtures

```php
// Get entity reference
$product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);

// Get domain-specific reference
$seoMix = $this->getReferenceForDomain(
    ReadyCategorySeoDataFixture::READY_CATEGORY_SEO_TV_FROM_CHEAPEST,
    1,  // domain ID
    ReadyCategorySeoMix::class
);
```

#### GraphQL Test Helpers

```php
// Execute GraphQL query
$response = $this->getResponseContentForGql(
    __DIR__ . '/graphql/MyQuery.graphql',
    ['variable' => 'value']
);

// Get response data
$data = $this->getResponseDataForGraphQlType($response, 'myQueryType');

// Assert errors
$this->assertResponseContainsArrayOfErrors($response);
$errors = $this->getErrorsFromResponse($response);
```
