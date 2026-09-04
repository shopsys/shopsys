---
name: cypress-tests
description: 'Write, fix, and review Cypress acceptance tests for Shopsys storefront. Use when working with Cypress test files (.cy.ts), adding test IDs (TIDs), or debugging test failures. Enforces project conventions: TID-based selectors, proper snapshot setup, and support file patterns.'
---

# Cypress Test Writing for Shopsys Storefront

You are an expert Cypress test writer for the Shopsys Platform storefront. Follow these conventions EXACTLY when writing, reviewing, or fixing tests.

## Project Structure

```
project-base/storefront/cypress/
  tids.ts                           # TID enum — central registry of all data-tid values
  cypress.config.ts                 # Cypress configuration
  cypress.d.ts                      # Custom command type declarations
  fixtures/demodata.ts              # Static test data (users, products, categories, URLs)
  fixtures/translationKeys.ts       # Translation key constants
  support/index.ts                  # Custom commands, helpers, SNAPSHOT_GROUP enum
  support/api.ts                    # GraphQL API commands (login, addToCart, createOrder)
  support/navigation.ts             # Entity navigation helpers (visitEntityByUuid)
  support/translations.ts           # Translation loading and lookup
  e2e/
    featureName/
      featureName.cy.ts             # Test file
      featureNameSupport.ts          # Support helpers for this feature
```

### Existing Test Categories

| Directory            | Tests                                                                        | Support File                  |
| -------------------- | ---------------------------------------------------------------------------- | ----------------------------- |
| authentication/      | login, registration                                                          | authenticationSupport.ts      |
| b2b/                 | accountantViewOnly, editProfileB2b, orderWithdrawal                          | b2bSupport.ts                 |
| cart/                | cartInHeader, cartLogin, cartPage, productAddToCart                          | cartSupport.ts                |
| comparison/          | productComparison                                                            | comparisonSupport.ts          |
| complaints/          | complaints                                                                   | complaintsSupport.ts          |
| customerUsers/       | customerUsers                                                                | customerUsersSupport.ts       |
| filterAndSort/       | categoryDetailFilterAndSort, parameterFilter                                 | —                             |
| freeShipping/        | freeShipping                                                                 | freeShippingSupport.ts        |
| giftWithProduct/     | giftWithProduct                                                              | giftWithProductSupport.ts     |
| graphql/             | graphQLErrorHandling                                                         | —                             |
| limitedUser/         | limitedUser                                                                  | limitedUserSupport.ts         |
| matrix/              | matrixTest                                                                   | —                             |
| order/               | contactInformation, createOrder, createOrderWithDeliveryAddress, orderRepeat | orderSupport.ts               |
| seoCategory/         | seoCategory                                                                  | —                             |
| ssr/                 | serverSideRendering                                                          | —                             |
| stores/              | stores                                                                       | storesSupport.ts              |
| transportAndPayment/ | lastOrderTransportAndPayment, paymentSelect, transportSelect                 | transportAndPaymentSupport.ts |
| visits/              | repeatedVisits, simpleVisitsWithScreenshots                                  | visitsSupport.ts              |

## Running Tests

```bash
# GUI mode (auto-reloads .cy.ts files)
make open-acceptance-tests-base

# Headless specific test
make run-specific-test-base SPEC=e2e/path/to/test.cy.ts

# All base tests headless
make run-acceptance-tests-base

# Specific group
make selected-acceptance-tests-base  # Interactive group selection
```

When storefront `.tsx` files change (adding TIDs), the make command automatically rebuilds.

**Reference site** for inspecting page structure: `https://19-0.odin.shopsys.cloud/`

## CRITICAL RULE: Search Existing Interaction Patterns First

Before writing or changing a Cypress interaction, search the whole Cypress codebase for the same component,
TID, helper, and interaction type. Trace the complete user flow — setup, opening, input, submission, closing,
navigation, and assertions — rather than copying only its primary action.

- Reuse an established helper or support-file pattern whenever one exists.
- Search for equivalent real-event usage such as `realMouseMove`, `realPress`, or other interaction-specific
  commands before introducing `trigger()` or custom event simulation.
- Inspect the relevant React component when lifecycle behavior matters. Verify desktop/mobile variants,
  popovers versus drawers, portals, mounted versus merely hidden content, disabled/loading states, debounce,
  and animations before choosing assertions.
- Add a new interaction pattern only when no project pattern fits, and model it on the closest verified flow.

## CRITICAL RULE: Always Use cy.getByTID()

Every element interaction MUST use `cy.getByTID()` or scope via TIDs. This is the single most important convention.

### How getByTID works

```typescript
// Single TID → [data-tid=value]
cy.getByTID([TIDs.store_list]);

// TID with suffix → [data-tid=prefix_suffix]
cy.getByTID([[TIDs.blocks_product_list_listeditem_, '9177759']]);

// Nested (descendant) → [data-tid=parent] [data-tid=child]
cy.getByTID([[TIDs.blocks_product_list_listeditem_, catnum], TIDs.product_compare_button]);
```

**Important**: getByTID uses EXACT match (`=`), NOT starts-with (`^=`). You cannot use it to match all items with a prefix.

### NEVER use these anti-patterns:

```typescript
// NEVER: Translation-based finding
t('Store detail').then((translated) => {
    cy.contains(translated).click();
});

// NEVER: Manual data-tid selectors
cy.get('[data-tid="blocks_product_addtocart"]');
cy.get(`[data-tid^="${TIDs.blocks_product_list_listeditem_}"]`);

// NEVER: Title/aria-label selectors for clickable elements
cy.get('[title="Add to comparison"]');
cy.get(`button[aria-label="${translations.filter.clearAll}"]`);

// NEVER: Raw h1 when page_title TID exists
cy.get('h1')
    .should('contain', 'text')

    // NEVER: Text-based button matching in popups
    .find('button')
    .contains(/yes|remove/i)
    .click();
```

### ALWAYS use these patterns:

```typescript
// Direct TID
cy.getByTID([TIDs.page_title]).should('contain.text', linkText);
cy.getByTID([TIDs.popup_confirm_button]).click();

// TID + find for native HTML elements (checkboxes, inputs)
cy.getByTID([TIDs.filter_panel]).find('input[type="checkbox"]').first().check({ force: true });
cy.getByTID([TIDs.filter_price_input_min]).find('input').clear().type('200').blur();

// Expandable items (collapsed by default)
cy.getByTID([TIDs.store_list]).find('[aria-expanded="false"]').first().click();

// Form fields by ID (acceptable — form libraries generate IDs)
cy.get('#customer-change-profile-form-street').should('not.be.disabled');

// Conditional existence check (when element may not exist)
cy.get('body').then(($body) => {
    if ($body.find(`[data-tid="${TIDs.clear_all_filters_button}"]`).length > 0) {
        cy.getByTID([TIDs.clear_all_filters_button]).first().click({ force: true });
    }
});
```

## Adding New TIDs

### Step 1: Register in tids.ts

```typescript
// project-base/storefront/cypress/tids.ts
export enum TIDs {
    my_new_element = 'my_new_element',
}
```

### Step 2: Add to React component

```tsx
// Regular HTML element:
<div data-tid={TIDs.my_new_element}>

// Button component (uses `tid` prop, NOT `data-tid`):
<Button tid={TIDs.my_new_element}>

// LinkButton component:
<LinkButton tid={TIDs.my_new_element}>
```

**Multiple items with the same TID is OK** (e.g., `product_compare_button` on every product card). Use `.first()` or scope with parent TID.

### TID Reference

All available TIDs are defined in `project-base/storefront/cypress/tids.ts`. Read this file to find existing TIDs before adding new ones. TIDs ending with `_` are **prefixes** used with a suffix (e.g., `blocks_product_list_listeditem_` + catnum).

## Test File Template

```typescript
import { staticData } from 'fixtures/demodata';
import {
    getSnapshotIndexingFunction,
    initializePersistStoreInLocalStorageToDefaultValues,
    SNAPSHOT_GROUP,
    takeSnapshotAndCompare,
} from 'support';
import { TIDs } from 'tids';

const SUBGROUP_INDEX = 0; // unique per test file within the SNAPSHOT_GROUP
const getSnapshotFullIndexAsString = getSnapshotIndexingFunction(SNAPSHOT_GROUP.MY_GROUP, SUBGROUP_INDEX);

describe('Feature Tests (SSP-XXXX)', () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
    });

    it('[Test Name] should do something', () => {
        cy.visitAndWaitForStableAndInteractiveDOM('/');
        cy.getByTID([TIDs.some_element]).should('be.visible').click();
        cy.waitForStableAndInteractiveDOM();

        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'snapshot name', {
            blackout: [
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });
    });
});
```

### B2B Test Template

```typescript
import { b2bUrl, staticData } from 'fixtures/demodata';
import {
    getSnapshotIndexingFunction,
    initializePersistStoreInLocalStorageToDefaultValues,
    SNAPSHOT_GROUP,
    takeSnapshotAndCompare,
} from 'support';
import { TIDs } from 'tids';

const SUBGROUP_INDEX = 0;
const getSnapshotFullIndexAsString = getSnapshotIndexingFunction(SNAPSHOT_GROUP.B2B, SUBGROUP_INDEX);

describe('B2B Feature Tests', () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
        cy.loginB2b(staticData.b2bOwner.email, staticData.b2bOwner.password);
    });

    it('[Test Name] should do something', () => {
        cy.visitB2bAndWaitForStableAndInteractiveDOM(b2bUrl.customer.orders);
        cy.url().should('contain', b2bUrl.customer.orders);
        // ... assertions
    });

    it('should show 403 for unauthorized user', () => {
        cy.loginB2b(staticData.b2bCatalogUser.email, staticData.b2bCatalogUser.password);
        cy.visitB2bAndWaitForStableAndInteractiveDOM(b2bUrl.customer.users, { failOnStatusCode: false });
        cy.getByTID([TIDs.error_403_page]).should('exist');
    });
});
```

## Support File Template

```typescript
import { staticData, b2bUrl } from 'fixtures/demodata';
import { checkAndHideSuccessToast, changeElementText, checkUrl } from 'support';
import { TIDs } from 'tids';

// Login helpers
export const loginAsB2bOwner = () => {
    cy.loginB2b(staticData.b2bOwner.email, staticData.b2bOwner.password);
};

// Navigation helpers
export const navigateToFeaturePage = () => {
    cy.visitB2bAndWaitForStableAndInteractiveDOM(b2bUrl.customer.complaints);
};

// Assertion helpers
export const checkElementIsVisible = () => {
    cy.getByTID([TIDs.complaints_list]).should('exist');
};

// Action helpers
export const performAction = () => {
    cy.getByTID([TIDs.some_button]).should('be.visible').click();
    cy.waitForStableAndInteractiveDOM();
};

// Dynamic content replacement for snapshots
export const replaceDynamicContent = () => {
    changeElementText(TIDs.store_opening_hours, staticData.openingHours, false);
};

// 403 check
export const check403PageIsVisible = () => {
    cy.getByTID([TIDs.error_403_page]).should('exist').and('be.visible');
};
```

## Snapshot / Visual Regression

### SNAPSHOT_GROUP values

Each test category needs a unique value in `support/index.ts`. Always check the actual enum for current values — gaps in numbering are expected (deleted groups are not backfilled). Pick any unused number for new groups.

**New test categories**: Add a new enum value with a unique number that doesn't conflict with existing ones. Read `support/index.ts` to see the current values.

### Snapshot indexing

```typescript
const SUBGROUP_INDEX = 0; // unique per .cy.ts file in the group
const getSnapshotFullIndexAsString = getSnapshotIndexingFunction(SNAPSHOT_GROUP.MY_GROUP, SUBGROUP_INDEX);

// Each call returns: "groupIndex-subgroupIndex-counter"
// e.g.: "13-0-0", "13-0-1", "13-0-2"
```

### Blackout rules

- `blackoutBeforeScreenshot` uses `cy.getByTID([tid]).each(...)` — **FAILS with timeout if TID doesn't exist on page**
- Only blackout TIDs present on the page being screenshotted
- Common blackouts for ALL pages: `footer_social_links`, `footer_payment_images`, `footer_copyright`
- Dynamic images: `product_list_item_image`, `comparison_product_image`, `stores_map`, `store_opening_status`, `category_bestseller_image`
- Use `zIndex` option when blackout needs to cover overlapping elements

### IMPORTANT: Always blackout product images and transport/payment icons

When taking snapshots, NEVER forget to blackout images and icons that may differ between environments:

**Product images by page:**

| Page | TID to blackout |
| --- | --- |
| Product detail | `product_detail_main_image` |
| Add-to-cart popup | `add_to_cart_popup_image` |
| Cart | `cart_list_item_image` |
| Order summary sidebar | `order_summary_cart_item_image` |
| Order confirmation | `order_summary_cart_item_image` (same component as order summary) |
| Order list (customer orders) | `order_list_product_image` |
| Order detail | `order_detail_item_image` |

**Transport & payment icons by page:**

| Page | TID to blackout |
| --- | --- |
| Transport & payment step | `transport_and_payment_list_item_image` |
| Order summary sidebar | `order_summary_transport_and_payment_image` |
| Order detail | `order_list_transport_and_payment_image` (shared `ElementWithImage` component) |

### Capture modes

```typescript
// Full page (default)
takeSnapshotAndCompare(name, 'label', { capture: 'fullPage' });

// Viewport only
takeSnapshotAndCompare(name, 'label', { capture: 'viewport' });

// Specific element by TID
takeSnapshotAndCompare(name, 'label', { capture: TIDs.layout_popup });
```

### Dynamic content handling

Replace dynamic content (dates, times, etc.) before taking snapshots:

```typescript
import { changeElementText } from 'support';
changeElementText(TIDs.store_opening_hours, staticData.openingHours, false);
changeElementText(TIDs.blog_article_publication_date, staticData.blogArticle.publicationDate);
```

The expected delivery date message (computed from the current date) is shown on the transport-and-payment
page and on pickup places. Before EVERY snapshot of that page, call
`changeExpectedDeliveryDateMessagesToStaticDemodata()` from `transportAndPaymentSupport.ts` — it replaces
each `TIDs.expected_delivery_date_message` element with `staticData.expectedDeliveryDateMessage`, or with
`staticData.expectedPersonalPickupDateMessage` when the element's real text starts with the translated
"Personal pickup" prefix (the delivery vs. pickup wording is deterministic, so snapshots keep it truthful).

## Known Gotcha: .within() + waitForStableAndInteractiveDOM

**NEVER** call `cy.waitForStableAndInteractiveDOM()` inside `.within()` — it does `cy.get('body[data-hydrated="true"]')` which fails when scoped to a non-body element.

```typescript
// BAD — hydration error
cy.getByTID([TIDs.store_list]).within(() => {
    cy.waitForStableAndInteractiveDOM(); // FAILS!
});

// GOOD — use .find() instead
cy.getByTID([TIDs.store_list]).find('[aria-expanded="false"]').first().click();
cy.waitForStableAndInteractiveDOM();
```

## Custom Commands Reference

### Navigation & DOM Stability

| Command                                                        | Description                                                                                                                                          |
| -------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------- |
| `cy.visitAndWaitForStableAndInteractiveDOM(url)`               | Visit B2C page + wait for hydration + stable DOM                                                                                                     |
| `cy.visitB2bAndWaitForStableAndInteractiveDOM(path, options?)` | Visit B2B page (prepends b2bDomain.baseUrl) + wait                                                                                                   |
| `cy.reloadAndWaitForStableAndInteractiveDOM()`                 | Reload + wait                                                                                                                                        |
| `cy.waitForStableAndInteractiveDOM()`                          | Wait for: stable DOM, no skeleton, no nprogress, no loader, hydrated                                                                                 |
| `cy.waitForHydration()`                                        | Wait for `body[data-hydrated="true"]`                                                                                                                |
| `visitEntityByUuid(type, uuid)`                                | Navigate to entity by UUID via GraphQL slug lookup. Types: 'category', 'product', 'blogCategory', 'blogArticle', 'article', 'brand', 'flag', 'store' |

### GraphQL API Commands

| Command                                              | Description                                                              |
| ---------------------------------------------------- | ------------------------------------------------------------------------ |
| `cy.addProductToCartForTest(uuid?, qty?)`            | Add product to cart via GraphQL (defaults to helloKitty, qty 1)          |
| `cy.addPromoCodeToCartForTest(promoCode)`            | Apply promo code                                                         |
| `cy.preselectTransportForTest(uuid, pickupPlaceId?)` | Select transport method                                                  |
| `cy.preselectPaymentForTest(uuid)`                   | Select payment method                                                    |
| `cy.login(email?, pw?)`                              | B2C login via GraphQL (defaults to staticData.user). Caches tokens.      |
| `cy.loginB2b(email, pw)`                             | B2B login via GraphQL. Sets cookies for B2B domain.                      |
| `cy.logout()`                                        | Logout via GraphQL + clear cookies                                       |
| `cy.createOrder(variables)`                          | Create order via GraphQL. Returns `{ urlHash }`.                         |
| `cy.registerAsNewUser(input, shouldLogin?)`          | Register new user. If shouldLogin=true (default), sets auth cookies.     |
| `.checkGQL(operationName)`                           | Chain after `cy.request()` to validate GraphQL response and extract data |

### Helper Functions (from support/index.ts)

| Function                                                                  | Description                                                          |
| ------------------------------------------------------------------------- | -------------------------------------------------------------------- |
| `initializePersistStoreInLocalStorageToDefaultValues()`                   | Reset localStorage persist store — **call in every beforeEach**      |
| `checkAndHideSuccessToast(text?)`                                         | Assert success toast exists (optionally with text), click to dismiss |
| `checkAndHideErrorToast(text?)`                                           | Assert error toast exists, click to dismiss                          |
| `checkAndHideInfoToast(text?)`                                            | Assert info toast exists, click to dismiss                           |
| `checkUrl(url)`                                                           | Assert `cy.url().should('contain', url)`                             |
| `getHeaderElementByTID(tid)`                                              | Scroll to the top and find a descendant in the main header           |
| `openHeaderUserMenu()`                                                    | Scroll to the top and open the main header account menu              |
| `checkIsUserLoggedIn()`                                                   | Assert my_account_link contains "My account" translation             |
| `checkIsUserLoggedOut()`                                                  | Assert my_account_link contains "Login" translation                  |
| `goToEditProfileFromHeader()`                                             | Open account menu, click edit profile link                           |
| `checkLoaderOverlayIsNotVisibleAfterTimePeriod(ms?)`                      | Wait then assert loader_overlay not exist                            |
| `changeElementText(tid, text, isRightAfterSSR?)`                          | Replace element text (for dynamic content in snapshots)              |
| `loseFocus()`                                                             | Blur currently focused element                                       |
| `checkPopupIsVisible(shouldClose?)`                                       | Assert layout_popup visible, optionally press Esc                    |
| `changeCartItemQuantityWithSpinboxInput(qty, catnum)`                     | Type quantity into cart item's spinbox                               |
| `changeProductListItemQuantityWithSpinboxInput(qty, catnum)`              | Type quantity into product list item's spinbox                       |
| `goToPageThroughSimpleNavigation(index)`                                  | Click pagination page by index                                       |
| `checkCanGoToNextOrderStep()`                                             | Assert order next button is visible and not disabled                 |
| `takeSnapshotAndCompare(name, label, options?, callbackBeforeBlackout?)`  | Take visual regression snapshot                                      |
| `getSnapshotIndexingFunction(group, subgroup)`                            | Returns counter function for snapshot naming                         |
| `checktHeadlineText(translationKey)`                                      | Check h1 text matches translation (with fallback)                    |
| `checkFormLineError(errorText?)`                                          | Assert form_line_error exists with optional translated text          |
| `checkNumberOfApiRequestsTriggeredByActions(actions, count, requestName)` | Intercept and count GraphQL requests                                 |

## Static Test Data (fixtures/demodata.ts)

### Products

```typescript
staticData.products.helloKitty; // { uuid, name, catnum: '9177759' }
staticData.products.philips32PFL4308; // { uuid, catnum: '9176508' }
staticData.products.televisionPhilipsM; // { uuid }
staticData.products.a4techMouse; // { uuid, catnum: '5960453', name }
staticData.products.philips54CRT; // { uuid, catnum: '9176588' }
staticData.products.panasonicDmcFt5ep; // { catnum: '5965907', name: 'PANASONIC DMC FT5EP' }
staticData.products.delonghi; // { uuid, catnum: '9771339', name } — has gift plan (gift: giftTicket100czk)
staticData.products.giftTicket100czk; // { uuid, catnum: '9176544MS', name } — gift product
```

### Categories

```typescript
staticData.categories.electronics; // { uuid }
staticData.categories.personalComputers; // { uuid }
```

### Users

```typescript
staticData.user; // { email: 'no-reply@shopsys.com', password: 'user123', uuid }
staticData.customer1; // { email, emailRegistered, firstName, lastName, phone, billingStreet, billingCity, billingPostCode, password }
staticData.b2bOwner; // { email: 'jozef.novotny@shopsys.com', password: 'user123' }
staticData.b2bUser; // { email: 'marek.horvat@shopsys.com', password }
staticData.b2bLimitedUser; // { email: 'peter.kovac@shopsys.com', password }
staticData.b2bCatalogUser; // { email: 'jiri.katalogovy@shopsys.com', password }
staticData.b2bAccountant; // { email: 'jana.ucetni@shopsys.com', password }
```

### Delivery Addresses

```typescript
staticData.deliveryAddress; // { firstName: 'Janek', lastName, company, phone, street, city, postCode, country: 'CZ' }
staticData.deliveryAddress2; // { firstName: 'Tomáš', ... }
```

### Payment & Transport UUIDs

```typescript
staticData.payment.creditCard.uuid;
staticData.payment.cash.uuid;
staticData.payment.onDelivery.uuid;

staticData.transport.personalCollection.uuid;
staticData.transport.personalCollection.storeOstrava; // { uuid, name }
staticData.transport.personalCollection.storePardubice; // { uuid, name }
staticData.transport.czechPost.uuid;
staticData.transport.ppl.uuid;
```

### URLs

```typescript
// B2C URLs
(url.cart,
    url.search,
    url.brandsOverview,
    url.login,
    url.registration,
    url.stores,
    url.contactForm,
    url.productComparison);
(url.order.transportAndPayment, url.order.contactInformation, url.order.orderConfirmation, url.order.orderDetail);
(url.customer.orders,
    url.customer.editProfile,
    url.customer.complaints,
    url.customer.newComplaint,
    url.customer.complaintDetail);

// B2B URLs
(b2bUrl.login, b2bUrl.cart, b2bUrl.registration);
(b2bUrl.order.transportAndPayment,
    b2bUrl.order.contactInformation,
    b2bUrl.order.orderConfirmation,
    b2bUrl.order.orderDetail,
    b2bUrl.order.orderWithdrawal);
(b2bUrl.customer.users,
    b2bUrl.customer.complaints,
    b2bUrl.customer.newComplaint,
    b2bUrl.customer.complaintDetail,
    b2bUrl.customer.orders,
    b2bUrl.customer.editProfile,
    b2bUrl.customer.changePassword);
```

### Other Static Data

```typescript
staticData.promoCode; // 'test'
staticData.openingHours; // '09:00 - 11:00, 13:00 - 17:00'
staticData.orderNote; // 'Just a tiny note in the order.'
staticData.expectedDeliveryDateMessage; // 'Delivery on Tuesday 10/26'
staticData.expectedPersonalPickupDateMessage; // 'Personal pickup on Tuesday 10/26'
staticData.order.number; // '1234567890'
staticData.order.numberHeading; // 'Order number 1234567890'
staticData.order.creationDate; // '10/26/1999 10:10 AM'
staticData.blogArticle.publicationDate; // '10/26/1999'
staticData.blogArticle.grapesJs.uuid;
staticData.article.creationDate; // '10/26/1999'
staticData.article.forPress.uuid;
```

## Translation System

The test suite has a multi-layer translation system that supports running tests in different locales (en, cs, sk). Understanding this is essential for writing assertions on visible text.

### Architecture Overview

```
translationKeys.ts          → Defines English key strings used for lookups
support/translations.ts     → Core module: t() function, loadAllTranslations()
support/index.ts            → Global `translations` object, auto-loaded before all tests
public/locales/{locale}/common.json  → Primary translation source (JSON)
app-translations/dataFixtures.{locale}.po → Fallback translations (.PO format)
```

### Translation Fallback Chain

When `t(key)` is called, it resolves in this order:

1. **common.json** — `public/locales/{TEST_LOCALE}/common.json` (primary)
2. **.po files** — `dataFixtures.{locale}.po` from `/app/app-translations/` (fallback for demo data)
3. **Key itself** — returns the original key string if not found anywhere

### Two Ways to Use Translations in Tests

#### 1. Global `translations` object (pre-loaded, synchronous access)

The `translations` object is loaded once in a `before()` hook via `loadAllTranslations()` and available globally. It contains translated values for all keys defined in `fixtures/translationKeys.ts`.

```typescript
import { translations } from 'support';

// Use for placeholder assertions
cy.get('#registration-form-email').should('have.attr', 'placeholder', translations.placeholder.email);

// Use for toast messages
checkAndHideSuccessToast(translations.toast.success.accountCreated);

// Use for checking logged-in state
cy.getByTID([TIDs.my_account_link]).should('be.visible').contains(translations.link.myAccount);
```

**Available categories on the `translations` object** (mirrors `translationKeys.ts` structure):

- `translations.placeholder.*` — Form placeholder texts
- `translations.payment.*` — Payment method names
- `translations.transport.*` — Transport names
- `translations.order.created`, `translations.order.confirmation.*`, `translations.order.promoCode`
- `translations.link.*` — Link texts
- `translations.button.*` — Button texts
- `translations.filter.*` — Filter labels
- `translations.toast.success.*` / `translations.toast.error.*` / `translations.toast.info.*`

#### 2. `t()` function (async, for one-off lookups)

For translation keys NOT in `translationKeys.ts`, use the `t()` function directly. It returns a `Cypress.Chainable<string>` and supports parameter substitution.

```typescript
import { t } from 'support/translations';

// Simple lookup
t('Please enter email').then((translatedText) => {
    cy.getByTID([TIDs.form_line_error]).contains(translatedText).should('be.visible');
});

// With parameter substitution ({{ param }} syntax)
t('Order number {{ orderNumber }} has been sent', { orderNumber: '12345' }).then((text) => {
    cy.contains(text);
});
```

### Translation-Aware Helper Functions

These helpers use translations internally — pass English key strings and they handle lookup:

```typescript
// checkFormLineError — looks up the key via t(), finds it in form_line_error TID
checkFormLineError('Please enter email');
checkFormLineError('Please enter password');
checkFormLineError(); // just check any error exists

// checktHeadlineText — checks h1 against key (tries direct match first, then t() lookup)
checktHeadlineText('Stores');
checktHeadlineText('Product comparison');

// checkIsUserLoggedIn / checkIsUserLoggedOut — uses translations.link.myAccount / translations.button.login
checkIsUserLoggedIn();
checkIsUserLoggedOut();
```

### Real-World Translation Patterns

```typescript
// Form filling with placeholder verification
cy.get('#registration-form-firstName')
    .should('have.attr', 'placeholder', translations.placeholder.firstName)
    .type('John');

cy.get('#registration-form-street')
    .should('have.attr', 'placeholder', translations.placeholder.street)
    .type('Main Street 123');

// Toast with translated text
checkAndHideSuccessToast(translations.toast.success.userSaved);
checkAndHideInfoToast(translations.toast.info.cartModified);

// Order confirmation text check
cy.getByTID([TIDs.order_confirmation_page_text_wrapper]).should(
    'contain.text',
    translations.order.confirmation.czechPost,
);

// Validation error checking (keys resolved via t())
checkFormLineError('Please enter email');
checkFormLineError('You have to agree with our privacy policy');
```

### Key Files

- `project-base/storefront/cypress/fixtures/translationKeys.ts` — All translation key strings (English)
- `project-base/storefront/cypress/support/translations.ts` — Core `t()` function, `loadAllTranslations()`, .po parser
- `project-base/storefront/public/locales/{en,cs,sk}/common.json` — JSON translation files
- `project-base/app/translations/dataFixtures.{locale}.po` — .PO fallback translations

## Common Test Patterns

### Popup interaction (confirm/dismiss)

```typescript
// Trigger action that opens popup
cy.getByTID([TIDs.comparison_remove_all_button]).click();
// Verify popup is visible
cy.getByTID([TIDs.layout_popup]).should('be.visible');
// Confirm action
cy.getByTID([TIDs.popup_confirm_button]).click();
cy.waitForStableAndInteractiveDOM();
// Or dismiss
cy.realPress('{esc}');
cy.getByTID([TIDs.layout_popup]).should('not.exist');
```

### Toast message verification

```typescript
import { checkAndHideSuccessToast } from 'support';
checkAndHideSuccessToast(); // just check exists
checkAndHideSuccessToast('User profile has been changed successfully'); // check with text
```

### Access control / 403 testing

```typescript
cy.loginB2b(staticData.b2bCatalogUser.email, staticData.b2bCatalogUser.password);
cy.visitB2bAndWaitForStableAndInteractiveDOM(b2bUrl.customer.users, { failOnStatusCode: false });
cy.getByTID([TIDs.error_403_page]).should('exist');
```

### Element absence testing

```typescript
// Verify element does NOT exist (e.g., restricted button)
cy.getByTID([TIDs.blocks_product_addtocart]).should('not.exist');
cy.getByTID([TIDs.complaints_list_create_complaint_manually_button]).should('not.exist');
```

### Navigate to entity by UUID

```typescript
import { visitEntityByUuid } from 'support/navigation';
visitEntityByUuid('category', staticData.categories.electronics.uuid);
visitEntityByUuid('product', staticData.products.a4techMouse.uuid);
```

### Filter interaction

```typescript
// Check checkbox filter
cy.getByTID([TIDs.filter_panel]).find('input[type="checkbox"]').first().check({ force: true });
cy.wait(1500);
cy.waitForStableAndInteractiveDOM();

// Price filter
cy.getByTID([TIDs.filter_price_input_min]).find('input').should('be.visible').clear().type('200').blur();

// Clear all filters (conditionally)
cy.get('body').then(($body) => {
    if ($body.find(`[data-tid="${TIDs.clear_all_filters_button}"]`).length > 0) {
        cy.getByTID([TIDs.clear_all_filters_button]).first().click({ force: true });
    }
});
```

### Sort interaction

```typescript
cy.getByTID([[TIDs.blocks_sortingbar_option_, 0]]).click({ force: true });
cy.wait(1500);
cy.waitForStableAndInteractiveDOM();
```

### Product count assertion

```typescript
cy.getByTID([TIDs.product_list_item_image]).should('have.length.greaterThan', 0);
cy.getByTID([TIDs.product_list_item_image]).then(($items) => {
    expect($items.length).to.be.lte(initialCount);
});
```

### Main and fixed header navigation

```typescript
// Regular flows always use the main header in a stable scroll position.
openHeaderUserMenu();
cy.getByTID([TIDs.header, TIDs.my_account_link, TIDs.user_menu_edit_profile_link])
    .filter(':visible')
    .first()
    .should('be.visible')
    .click({ scrollBehavior: false });

// A dedicated fixed-header test scopes every action explicitly to the fixed header.
cy.scrollTo('bottom');
cy.getByTID([TIDs.fixed_header]).should('be.visible');
cy.getByTID([TIDs.fixed_header, TIDs.my_account_link]).click({ scrollBehavior: false });
```

Do not dynamically choose between the main and fixed headers. Regular feature tests should use the main header after
scrolling to the top. Test fixed-header behavior separately and scope all selectors under `TIDs.fixed_header`.

### Disable retries for flaky-prone tests

```typescript
describe('Feature', { retries: { runMode: 0 } }, () => { ... });
```

## Naming Conventions

### `describe()` block naming

Format: `'Feature Name Tests'` or `'Feature Name Tests (SSP-XXXX)'` with optional Jira ticket reference.

```typescript
// Top-level describe — feature name + "Tests", optionally with ticket
describe('Product Comparison Tests (SSP-1719)', () => { ... });
describe('Stores Tests (SSP-1741)', () => { ... });
describe('Cart Page Tests', () => { ... });
describe('Create Order Tests', () => { ... });

// Nested describe for sub-groups (B2B role-based, access control)
describe('Customer Users (B2B) Tests', () => {
    describe('As B2B Owner', () => { ... });
    describe('Access control', () => { ... });
});

// Multiple describe blocks in one file for logical grouping
describe('Registration Tests (Basic)', () => { ... });
describe('Registration Tests (B2B)', () => { ... });
describe('Registration Tests (Repeated Tries)', () => { ... });
```

### `it()` block naming

Two patterns exist depending on test style:

**Pattern 1: `[Short Label]` prefix + `should` description** — used in most test files. The bracket label is a short identifier (2-4 words, Title Case) that groups related tests and appears in snapshot names.

```typescript
// Good — clear short label + descriptive "should" sentence
it('[Empty Comparison] should show empty comparison page', () => { ... });
it('[Add From Listing] should add a product to comparison from category listing', () => { ... });
it('[Remove All] should add products then remove all from comparison', () => { ... });
it('[Parameter Checkbox Filter] should filter products by parameter checkbox and verify URL persistence', () => { ... });
it('[Anon Home Cash] should create order as unlogged user (transport to home, cash on delivery)', () => { ... });
it('[Prefilled Cart] should log in, add product to cart to an already prefilled cart', () => { ... });
it('[Transport Fee] should change price for transport when cart is large enough', () => { ... });
```

**Pattern 2: Plain `should` description** — used in B2B nested `describe` blocks where the parent already provides context.

```typescript
describe('As B2B Owner', () => {
    it('should display the customer users table', () => { ... });
    it('should open add user popup and add a new user', () => { ... });
});
describe('Access control', () => {
    it('should show 403 for Limited User trying to access customer users page', () => { ... });
});
```

**Rules:**
- Always use arrow `() =>` for all `it()` blocks (both with and without snapshots)
- The bracket label should be unique within the `describe` block
- Keep the `should` description specific — describe what the test does AND verifies

### File naming

```
e2e/
  featureName/
    featureName.cy.ts              # Main test file (camelCase, matches directory)
    featureNameSupport.ts           # Support helpers (camelCase + "Support")
```

Multiple test files in one directory are fine:

```
cart/cartPage.cy.ts, cartLogin.cy.ts, cartInHeader.cy.ts, productAddToCart.cy.ts
order/createOrder.cy.ts, contactInformation.cy.ts, orderRepeat.cy.ts
```

## Workflow for Writing Tests

1. **Search existing complete flows** across `project-base/storefront/cypress/` by component, TID, helper, and interaction type; reuse the closest verified pattern
2. **Inspect the page and component** with `/playwright-cli` on localhost or `https://ssfwcc.dev.shopsys.cloud/` and read the relevant React implementation when lifecycle behavior affects the test
3. **Check existing TIDs** in `project-base/storefront/cypress/tids.ts` — reuse what exists
4. **Add missing TIDs** to `tids.ts` + React components (use `data-tid` for HTML elements, `tid` prop for Button/LinkButton)
5. **Create or extend a support file** for reusable helpers (co-locate as `featureNameSupport.ts`)
6. **Write test** using `cy.getByTID()` consistently, `initializePersistStoreInLocalStorageToDefaultValues()` in beforeEach
7. **Add SNAPSHOT_GROUP** if new category (unique number in support/index.ts enum)
8. **Run test** with `make run-specific-test-base SPEC=e2e/path/to/test.cy.ts`
9. **Fix errors** and re-run until passing

## Real-World Example: Complete Test + Support File

### comparisonSupport.ts

```typescript
import { url } from 'fixtures/demodata';
import { checkAndHideSuccessToast, checkUrl } from 'support';
import { TIDs } from 'tids';

export const visitComparisonPage = () => {
    cy.visitAndWaitForStableAndInteractiveDOM(url.productComparison);
    checkUrl(url.productComparison);
};

export const checkComparisonIsEmpty = () => {
    cy.getByTID([TIDs.comparison_empty_state]).should('be.visible');
};

export const addProductToComparisonFromListing = (catnum: string) => {
    cy.getByTID([[TIDs.blocks_product_list_listeditem_, catnum], TIDs.product_compare_button]).click({ force: true });
};

export const checkComparisonPopupVisible = () => {
    cy.getByTID([TIDs.comparison_popup]).should('be.visible');
};

export const closeComparisonPopup = () => {
    cy.realPress('{esc}');
};

export const goToComparisonFromPopup = () => {
    cy.getByTID([TIDs.comparison_popup_link]).click();
    cy.waitForStableAndInteractiveDOM();
};

export const removeAllFromComparison = () => {
    cy.getByTID([TIDs.comparison_remove_all_button]).click();
    cy.getByTID([TIDs.layout_popup]).should('be.visible');
    cy.getByTID([TIDs.popup_confirm_button]).click();
    cy.waitForStableAndInteractiveDOM();
    checkAndHideSuccessToast();
};

export const removeProductFromComparison = (catnum: string) => {
    cy.getByTID([[TIDs.comparison_product_, catnum], TIDs.comparison_remove_product_button]).click({ force: true });
    cy.waitForStableAndInteractiveDOM();
    checkAndHideSuccessToast();
};

export const checkComparisonProductCount = (count: number) => {
    cy.getByTID([TIDs.page_title]).should('contain', `(${count})`);
};
```

### productComparison.cy.ts

```typescript
import {
    addProductToComparisonFromDetail,
    addProductToComparisonFromListing,
    checkComparisonIsEmpty,
    checkComparisonPopupVisible,
    checkComparisonProductCount,
    closeComparisonPopup,
    goToComparisonFromPopup,
    removeAllFromComparison,
    removeProductFromComparison,
    visitComparisonPage,
} from './comparisonSupport';
import { staticData, url } from 'fixtures/demodata';
import {
    checkUrl,
    getSnapshotIndexingFunction,
    initializePersistStoreInLocalStorageToDefaultValues,
    SNAPSHOT_GROUP,
    takeSnapshotAndCompare,
} from 'support';
import { visitEntityByUuid } from 'support/navigation';
import { TIDs } from 'tids';

const SUBGROUP_INDEX = 0;
const getSnapshotFullIndexAsString = getSnapshotIndexingFunction(SNAPSHOT_GROUP.COMPARISON, SUBGROUP_INDEX);

describe('Product Comparison Tests (SSP-1719)', { retries: { runMode: 0 } }, () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
    });

    it('[Empty Comparison] should show empty comparison page', () => {
        visitComparisonPage();
        checkComparisonIsEmpty();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'empty comparison', {
            blackout: [
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });
    });

    it('[Add From Listing] should add product from listing and navigate to comparison', () => {
        visitEntityByUuid('category', staticData.categories.electronics.uuid);
        addProductToComparisonFromListing(staticData.products.helloKitty.catnum);
        checkComparisonPopupVisible();
        goToComparisonFromPopup();
        checkUrl(url.productComparison);
        checkComparisonProductCount(1);
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'one product from listing', {
            blackout: [
                { tid: TIDs.comparison_product_image },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });
    });

    it('[Remove All] should add products then remove all', () => {
        visitEntityByUuid('category', staticData.categories.electronics.uuid);
        addProductToComparisonFromListing(staticData.products.helloKitty.catnum);
        checkComparisonPopupVisible();
        closeComparisonPopup();

        visitEntityByUuid('product', staticData.products.a4techMouse.uuid);
        addProductToComparisonFromDetail();
        checkComparisonPopupVisible();
        goToComparisonFromPopup();

        removeAllFromComparison();
        checkComparisonIsEmpty();
    });
});
```

## Snapshot Lookup Table

A complete lookup table mapping snapshot IDs to test names and source files is maintained in:
`project-base/storefront/cypress/snapshots-info-table.md`

Use this table to quickly find which test generates a specific snapshot (e.g., snapshot `13-0-0` → stores test).

**Regenerate** the table after adding/removing/renaming snapshots:

```bash
make generate-snapshots-info-table
```

## Cypress Config Highlights

- **Viewport**: 1600x720 (covers the five-column product grid at the 1560px `xxl` breakpoint)
- **Headless Electron window**: Matches the configured viewport in `before:browser:launch` so screenshots are not clipped to the default 1280px window width.
- **Default command timeout**: 20s
- **Video**: enabled
- **Visual regression error threshold**: 0.005 (0.5%)
- **Retries in runMode**: 2 (configurable per test with `{ retries: { runMode: 0 } }`)
- **Test groups**: Controlled by `GROUP` env var for CI (e.g., `GROUP=authentication`, `GROUP=b2b`)
- **Translation loading**: Auto-loads `.po` files from `/app/app-translations/`, falls back to English

## Keeping This Skill Up-to-Date

**IMPORTANT**: When you make changes to the Cypress test infrastructure, you MUST also update this skill file (`.claude/skills/cypress-tests/SKILL.md`) to stay in sync. Specifically:

- **New SNAPSHOT_GROUP value** → Update the `SNAPSHOT_GROUP values` enum listing in this file
- **New custom command** → Add it to the Custom Commands Reference tables
- **New static data** (products, users, URLs) → Add to the Static Test Data section
- **New TID patterns** (new section/category of TIDs) → No action needed (skill references `tids.ts` directly)
- **New test category/directory** → Add to the Existing Test Categories table
- **New translation key category** → Update the `translations` object categories list
- **Changed test workflow or conventions** → Update relevant sections
- **Snapshot changes** → Run `make generate-snapshots-info-table` to regenerate the lookup table
