# Implementation Plan: X+Y Promotion System (Finalized)

## Overview

This plan implements the X+Y promotion system (e.g., "3+2 free"). Core backend logic lives in `packages/framework`, GraphQL changes in `packages/frontend-api`, storefront updates in `project-base/storefront`, and emails in framework templates. Translations use `t()` with English source strings and are dumped via `php phing translations-dump` in the php-fpm container.

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

- Add `promotionX`, `promotionY` properties (nullable)
- Add getter/setter methods (no typehints - packages rules)
- Update `setData()` method

### 1.4 Extend FlagData

**File**: `packages/framework/src/Model/Product/Flag/FlagData.php`

- Add `promotionX`, `promotionY` properties (no types - packages rules)

### 1.5 Database Migration

**File**: `packages/framework/src/Migrations/Version20250109120000.php`

- Add `promotion_x`, `promotion_y` columns to `products` table (nullable ints)
- Add `promotion_x`, `promotion_y` columns to `flags` table (nullable ints)
- No `is_promotion` column (presence of x/y implies promotion)
- Optional: add indexes later if needed (not required now)

## Phase 2: Core Services (Framework)

### 2.1 Promotion Flag Manager

**File**: `packages/framework/src/Model/Product/Flag/PromotionFlagManager.php`

- Service for managing promotional flags
- Methods: `findOrCreatePromotionFlag()`, `assignPromotionFlagToProduct()`, `removePromotionFlagFromProduct()`
- Use protected visibility and no typehints (packages rules)
- Enforce single shared Flag per (X,Y) in service logic (no DB unique)
- Generate localized names via `t()` when creating new flags; never overwrite later admin edits

### 2.2 Extend ProductFormType

**File**: `packages/framework/src/Form/Admin/Product/ProductFormType.php`

- Add promotion X/Y integer fields with validation
- Place under the stock section; labels/help via `t()` with English source texts
- Validation: 1+; clearing both to null removes promo; 0 invalid
- Use protected visibility and no typehints (packages rules)

### 2.3 Extend ProductFacade

**File**: `packages/framework/src/Model/Product/ProductFacade.php`

- Add promotion flag management to create/edit methods
- Handle automatic flag assignment/removal
- Reassign flags when (X,Y) changes; do not auto-delete unused flags
- Use protected visibility and no typehints (packages rules)

## Phase 3: Order Calculation Logic (Framework)

### 3.1 Promotion Calculator Service

**File**: `packages/framework/src/Model/Order/Promotion/PromotionCalculator.php`

- Calculate promotional discounts for cart items
- Handle multiplicative logic and partial groups per confirmed formula
- Use protected visibility and no typehints (packages rules)
- Freebies formula: freebies = floor(q/(X+Y))\*Y + max(0, min((q mod (X+Y)) - X, Y)); paid = q - freebies
- Represent effect as a line-level discount = freebies × unit price

### 3.2 Extend OrderItem

**File**: `packages/framework/src/Model/Order/Item/OrderItem.php`

- Add promotional discount tracking
- Methods for calculating effective quantity and price
- Use protected visibility and no typehints (packages rules)

### 3.3 Update Order Calculation

**File**: `packages/framework/src/Model/Order/OrderPriceCalculation.php`

- Integrate promotion calculator
- Apply promotional discounts to order totals
- Apply X+Y before other per-item/cart-level discounts (stacking confirmed)
- Use protected visibility and no typehints (packages rules)

## Phase 4: Frontend API (GraphQL)

### 4.1 Extend Product API (packages/frontend-api)

**Files**: `packages/frontend-api/` (schema + resolvers)

- Add product fields: `promotionX`, `promotionY`, `promotionText` (localized)
- `promotionText` uses `t()` and localized templates

### 4.2 Cart API Extensions (packages/frontend-api)

**Files**: `packages/frontend-api/` (schema + resolvers)

- Add cart line fields: `paidQuantity`, `freeQuantity`, `totalPriceBeforePromotion` (Price type: with/without VAT)
- No `promotionText` on cart lines

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

- Add filter: simple boolean "Has X+Y promotion" (no column)
- Use protected visibility and no typehints (packages rules)

## Phase 6: Storefront Integration (Storefront + Framework Email)

### 6.1 Storefront Product Detail (Next.js)

**Files**: `project-base/storefront` (product detail page/components)

- Under price, show:
    - Line 1: "Buy {X} get {Y} free" / "Akce {X} + {Y} zdarma"
    - Line 2 (quantity-specific): "Buy {X}, get {Y} free." / "Při nákupu {X} ks získáte {Y} ks zdarma."
- Data via GraphQL: `promotionX`, `promotionY`, `promotionText`

### 6.2 Storefront Cart & Order Detail (Next.js)

**Files**: `project-base/storefront` (cart and order detail components)

- Show strike-through `totalPriceBeforePromotion` and discounted total
- Add concise applied-promotion label under product name (no paid/free counts)

### 6.3 Emails (Framework)

**File**: `packages/framework/src/Resources/views/Mail/Order/products.html.twig`

- Mirror cart presentation (strike-through before-promotion total + discounted total)
- Show concise applied-promotion label under product name

Remove: Category filtering changes not required (filtering already handled via flags).

## Phase 7: Testing & Quality Assurance

### 7.1 Backend Unit Tests (Framework)

**Files**: `packages/framework/tests/`

- Test PromotionCalculator freebies/paid formula and rounding
- Test PromotionFlagManager create/reuse/assign/reassign behavior

### 7.2 Functional Tests (Framework)

**Files**: `packages/framework/tests/`

- ProductFacade: create/edit with X+Y, flag assignment
- Order calculation integration applying X+Y before other discounts

### 7.3 Scope

- Skip acceptance tests per requirements. Keep tests focused on core logic.

## Implementation Priority

1. **Phase 1**: Framework data model extensions (Essential foundation)
2. **Phase 2**: Framework core services (Business logic)
3. **Phase 3**: Framework order calculations (Core functionality)
4. **Phase 4**: Frontend API extensions (packages/frontend-api)
5. **Phase 5**: Framework admin interface (Content management)
6. **Phase 6**: Storefront + emails (User experience)
7. **Phase 7**: Testing (Quality assurance)

## Key Business Rules

1. **Freebies Formula**: freebies = floor(q/(X+Y))\*Y + max(0, min((q mod (X+Y)) - X, Y)); paid = q - freebies
2. **Automatic Flag Management**: Create/reuse shared (X,Y) flag and assign automatically
3. **Flag Names via Translations**: Localized names via `t()` (e.g., "Akce X + Y zdarma")
4. **Validation**: X and Y positive; both null removes promo
5. **Global Promotions**: No per-domain overrides; variants not treated specially
6. **Stacking**: Apply X+Y first, then other discounts

## Technical Considerations

1. **Performance**: Index promotional fields for filtering queries
2. **Caching**: Cache promotional calculations for frequently accessed products
3. **Internationalization**: Use `t()` with English source strings; dump via `phing translations-dump`
4. **Validation**: Comprehensive input validation at all levels
5. **Security**: Proper access controls for promotional settings
6. **Maintainability**: Follow Shopsys coding standards and patterns
7. **CLAUDE.md Compliance**:
    - Framework code: protected visibility, no typehints/return types, no property types
    - GraphQL lives in `packages/frontend-api`
    - Run `make generate-schema` after schema changes

## Success Criteria

- [ ] Admin can set X+Y promotions on products (framework)
- [ ] Promotional flags are automatically created/reused and assigned (framework)
- [ ] Cart/order calculations apply X+Y before other discounts (framework)
- [ ] Product grid can filter by "Has X+Y promotion" (framework)
- [ ] Product detail shows promotion text; cart/order detail shows strike-through and promo label (storefront)
- [ ] Order email products list mirrors cart presentation (framework template)
- [ ] GraphQL exposes specified product and cart fields (frontend-api) and schema is synced
- [ ] Code follows Shopsys standards and CLAUDE.md rules; focused unit/functional tests pass
