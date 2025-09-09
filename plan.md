# Implementation Plan: X+Y Promotion System (Direct Approach)

## Overview
This plan implements the X+Y promotion system (e.g., "3+2 free") entirely within the packages/framework bundle. All logic, services, and extensions will be placed in the framework to provide a complete promotional system.

## Phase 1: Backend Data Model (Framework)

### 1.1 Extend Product Entity
**File**: `packages/framework/src/Model/Product/Product.php`
- Add nullable `promotionX` and `promotionY` integer properties
- Add getter/setter methods (no typehints - packages rules)
- Add business logic methods: `hasPromotion()`, `getPromotionText()`
- Update `setData()` method to handle promotion fields

### 1.2 Extend ProductData
**File**: `packages/framework/src/Model/Product/ProductData.php`
- Add `promotionX` and `promotionY` properties (no types - packages rules)
- Initialize in constructor

### 1.3 Extend Flag Entity
**File**: `packages/framework/src/Model/Product/Flag/Flag.php`
- Add `promotionX`, `promotionY`, `isPromotion` properties
- Add getter/setter methods (no typehints - packages rules)
- Update `setData()` method

### 1.4 Extend FlagData
**File**: `packages/framework/src/Model/Product/Flag/FlagData.php`
- Add `promotionX`, `promotionY`, `isPromotion` properties (no types - packages rules)

### 1.5 Database Migration
**File**: `project-base/src/Migrations/Version20250109120000.php`
- Add `promotion_x`, `promotion_y` columns to `products` table
- Add `promotion_x`, `promotion_y`, `is_promotion` columns to `flags` table
- Add proper constraints and indexes for performance

## Phase 2: Core Services (Framework)

### 2.1 Promotion Flag Manager
**File**: `packages/framework/src/Model/Product/Flag/PromotionFlagManager.php`
- Service for managing promotional flags
- Methods: `findOrCreatePromotionFlag()`, `assignPromotionFlagToProduct()`, `removePromotionFlagFromProduct()`
- Use protected visibility and no typehints (packages rules)
- Use proper repository pattern and validation

### 2.2 Extend ProductFormType
**File**: `packages/framework/src/Form/Admin/Product/ProductFormType.php`
- Add promotion X/Y integer fields with validation
- Add conditional display logic
- Use protected visibility and no typehints (packages rules)

### 2.3 Extend ProductFacade
**File**: `packages/framework/src/Model/Product/ProductFacade.php`
- Add promotion flag management to create/edit methods
- Handle automatic flag assignment/removal
- Use protected visibility and no typehints (packages rules)

## Phase 3: Order Calculation Logic (Framework)

### 3.1 Promotion Calculator Service
**File**: `packages/framework/src/Model/Order/Promotion/PromotionCalculator.php`
- Calculate promotional discounts for cart items
- Handle multiplicative logic (1+1 also applies to 2+2, etc.)
- Return discount amount and explanation text
- Use protected visibility and no typehints (packages rules)

### 3.2 Extend OrderItem
**File**: `packages/framework/src/Model/Order/Item/OrderItem.php`
- Add promotional discount tracking
- Methods for calculating effective quantity and price
- Use protected visibility and no typehints (packages rules)

### 3.3 Update Order Calculation
**File**: `packages/framework/src/Model/Order/OrderPriceCalculation.php`
- Integrate promotion calculator
- Apply promotional discounts to order totals
- Use protected visibility and no typehints (packages rules)

## Phase 4: Frontend API (GraphQL) - Framework

### 4.1 Extend Product API
**File**: `packages/framework/src/Model/Product/ProductGraphQLType.php`
- Add promotion fields to GraphQL schema
- Add computed fields for promotion text and active status
- Use protected visibility and no typehints (packages rules)

### 4.2 Cart API Extensions
**File**: `packages/framework/src/Model/Cart/CartApiExtension.php`
- Add promotion information to cart items
- Include discount calculations and explanations
- Use protected visibility and no typehints (packages rules)

## Phase 5: Admin Interface Extensions (Framework)

### 5.1 Admin Template Extensions
**File**: `packages/framework/src/Resources/views/Admin/Content/Product/edit.html.twig`
- Add promotion fields to product edit form template
- Include validation and user guidance
- Show current promotional flags

### 5.2 Admin Controller Extensions
**File**: `packages/framework/src/Controller/Admin/ProductController.php`
- Handle promotion field processing
- Add validation for promotion values
- Use protected visibility and no typehints (packages rules)

### 5.3 Product Grid Extensions
**File**: `packages/framework/src/Model/Product/Grid/ProductGridFactory.php`
- Show promotion status in product grids
- Add filtering by promotional products
- Use protected visibility and no typehints (packages rules)

## Phase 6: Storefront Integration (Framework)

### 6.1 Twig Extensions
**File**: `packages/framework/src/Twig/PromotionExtension.php`
- Add Twig functions for promotion display
- Handle promotion text generation and formatting
- Use protected visibility and no typehints (packages rules)

### 6.2 Template Extensions
**File**: `packages/framework/src/Resources/views/Front/Content/Product/detail.html.twig`
- Display promotion information prominently
- Show savings calculation
- Handle multiple quantity scenarios

### 6.3 Cart Template Extensions
**File**: `packages/framework/src/Resources/views/Front/Content/Cart/index.html.twig`
- Show promotional discounts applied
- Display savings per item and total
- Update calculations dynamically

### 6.4 Category Filtering Extensions
**File**: `packages/framework/src/Model/Category/CategoryFacade.php`
- Add "Promotional Products" filter option
- Use promotional flags for filtering
- Use protected visibility and no typehints (packages rules)

## Phase 7: Testing & Quality Assurance (Framework)

### 7.1 Unit Tests
**File**: `packages/framework/tests/Unit/Model/Product/Flag/PromotionFlagManagerTest.php`
- Test promotion calculation logic
- Test flag management services
- Test edge cases and validation
- Use protected visibility and no typehints (packages rules)

### 7.2 Functional Tests
**File**: `packages/framework/tests/Functional/Model/Product/ProductFacadeTest.php`
- Test admin form submission
- Test API responses
- Test order calculation integration
- Use protected visibility and no typehints (packages rules)

### 7.3 Integration Tests
**File**: `packages/framework/tests/Integration/Model/Order/PromotionCalculatorTest.php`
- End-to-end promotional product workflow
- Cart calculation accuracy
- Multi-domain scenarios
- Use protected visibility and no typehints (packages rules)

## Implementation Priority

1. **Phase 1**: Framework data model extensions (Essential foundation)
2. **Phase 2**: Framework core services (Business logic)
3. **Phase 3**: Framework order calculations (Core functionality)
4. **Phase 4**: Framework API extensions (Frontend integration)
5. **Phase 5**: Framework admin interface (Content management)
6. **Phase 6**: Framework storefront display (User experience)
7. **Phase 7**: Framework testing (Quality assurance)

## Key Business Rules

1. **Multiplicative Logic**: 1+1 free promotion applies to quantities 2, 4, 6, etc.
2. **Automatic Flag Management**: Setting X+Y creates/assigns promotional flags automatically
3. **Unique Flag Names**: Each X+Y combination gets unique flag (e.g., "Akce 3+2 zdarma")
4. **Validation**: X and Y must be positive integers
5. **Multi-domain Support**: Flags work across all product domains
6. **Flexibility**: System supports any X+Y combination

## Technical Considerations

1. **Performance**: Index promotional fields for filtering queries
2. **Caching**: Cache promotional calculations for frequently accessed products
3. **Internationalization**: Use translation keys for all user-facing text
4. **Validation**: Comprehensive input validation at all levels
5. **Security**: Proper access controls for promotional settings
6. **Maintainability**: Follow Shopsys coding standards and patterns
7. **CLAUDE.md Compliance**: 
   - Framework packages: protected visibility, no typehints/return types, no property types
   - All logic implemented in packages/framework only

## Success Criteria

- [ ] Admin can set X+Y promotions on products (via framework)
- [ ] Promotional flags are automatically created and managed (via framework)
- [ ] Cart calculations correctly apply promotional discounts (via framework)
- [ ] Storefront displays promotion information clearly (via framework templates)
- [ ] System performs well with large product catalogs
- [ ] Code follows Shopsys quality standards and CLAUDE.md packages rules
- [ ] Comprehensive test coverage achieved in framework
- [ ] No project-base modifications required - all functionality in framework