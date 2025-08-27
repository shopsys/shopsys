# Test Service Injection

This directory contains the modern `#[Inject]` attribute system for test service injection, which works alongside the traditional `@inject` annotation.

## Usage Examples

### Traditional @inject annotation (still supported)
```php
class MyTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private AdministratorFrontSecurityFacade $securityFacade;
}
```

### Modern #[Inject] attribute (new)
```php
use App\Component\Test\Inject;

class MyTest extends TransactionFunctionalTestCase
{
    #[Inject]
    private Domain $domain;
    
    // With explicit service ID
    #[Inject(Domain::class)]
    private Domain $explicitDomain;
}
```

## Benefits of #[Inject] Attribute

1. **Modern PHP 8 Syntax**: Uses PHP 8 attributes instead of docblock annotations
2. **IDE Support**: Better autocompletion and validation in modern IDEs
3. **Type Safety**: Explicit service ID specification with class constants
4. **Backward Compatible**: Works alongside existing `@inject` annotations
5. **Future-Proof**: Aligns with modern PHP development practices

## Implementation

The implementation extends the existing `zalas/phpunit-injector` system:

- `Inject` - PHP 8 attribute for marking injectable properties
- `AttributeServiceExtractor` - Extractor that recognizes both `@inject` and `#[Inject]`
- `AttributeExtractorFactory` - Factory for creating the enhanced extractor

The system automatically detects service IDs from:
1. Explicit parameter in `#[Inject('service.id')]`
2. Type hints on the property
3. Traditional `@inject service.id` annotation syntax