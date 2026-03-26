# Payment Gateways in Shopsys

This documentation provides an overview of the payment gateway system in Shopsys, including how to implement and configure payment gateways, with a specific focus on the GoPay integration.

[TOC]

## Payment Gateway Architecture

The Shopsys payment system is designed with a flexible architecture that supports multiple payment gateways. The core of this architecture is the [`PaymentServiceInterface`]({{github.link}}/packages/framework/src/Model/Payment/Service/PaymentServiceInterface.php), which defines the contract that all payment gateway implementations must follow.

### Core Components

#### Payment Model

- Located in `Shopsys\FrameworkBundle\Model\Payment` namespace
- Contains all core payment-related classes and interfaces

#### Payment Service Interface

- Defines the contract for payment gateways with three key methods:
    - `createTransaction()`: Creates a new payment transaction
    - `updateTransaction()`: Updates the status of an existing transaction
    - `refundTransaction()`: Processes refunds for a transaction
- see [`PaymentServiceInterface`]({{github.link}}/packages/framework/src/Model/Payment/Service/PaymentServiceInterface.php)

#### Payment Service Facade

- Coordinates operations across different payment types
- Routes payment operations to the appropriate payment service implementation
- See [`PaymentServiceFacade`]({{github.link}}/packages/framework/src/Model/Payment/Service/PaymentServiceFacade.php)

#### Transaction Handling

- The system includes robust transaction handling capabilities
- Supports creating, updating, and refunding transactions
- See [`PaymentTransaction`]({{github.link}}/packages/framework/src/Model/Payment/Transaction/PaymentTransaction.php) and related classes in the `Shopsys\FrameworkBundle\Model\Payment\Transaction` namespace

!!! tip

    When your application is using HTTP authentication, you need to set the payment servers' IPs to the `WHITELIST_IPS` variable on your CI or extend `DEFAULT_WHITELIST_IPS` in your [`deploy-project.sh`]({{github.link}}/project-base/app/deploy/deploy-project.sh) to enable gateway to send requests back to your application.
    For more information [see deployment bundle readme](https://github.com/shopsys/deployment#whitelist-ip-addresses).

## Supported Payment Types

Shopsys currently supports the following payment types (defined by the [`PaymentTypeEnum`]({{github.link}}/packages/framework/src/Model/Payment/PaymentTypeEnum.php)):

#### Basic Payment Type

- Simple payment methods include cash on delivery, bank transfer, and others
- Does not require integration with external payment processors

#### GoPay Payment Type

- Online payments processed through the GoPay service
- Supports various payment methods, including credit cards and bank transfers

## GoPay Integration

Shopsys includes a complete implementation of the [GoPay](https://doc.gopay.com/) payment gateway, which can serve as a reference for implementing other payment gateways.

### GoPay Storefront Flow

The GoPay payment flow uses a two-page confirmation architecture with inline iframe support:

#### Page responsibilities

- **`/order-confirmation`**: Primary checkout completion page. For GoPay payments, it serves as a transitional page that auto-triggers the GoPay inline iframe. After payment completion (or on revisit with an active session), the user is redirected to `/order-payment-confirmation`. For non-GoPay payments, it shows standard order confirmation content.
- **`/order-payment-confirmation`**: Post-payment status page for GoPay. Calls `UpdatePaymentStatus` to resolve the current payment state and displays the appropriate UI (success, in-process, or failed with retry options). This is also the GoPay `return_url` landing page after 3DS/bank redirects.

#### Inline iframe behavior

GoPay payments open in an inline iframe (`_gopay.checkout({ inline: true })`). The callback behavior depends on the payment method:

| Scenario                          | `checkoutResult.url`      | Effect                      |
| --------------------------------- | ------------------------- | --------------------------- |
| Card without 3DS (success/cancel) | `undefined`               | Iframe closes automatically |
| Card with 3DS                     | External bank URL         | Full page redirect to 3DS   |
| Bank transfer (online PSD2)       | External bank URL         | Full page redirect to bank  |
| "Back to shop" in iframe          | Internal URL (our domain) | Iframe closes               |

For 3DS and bank transfer scenarios, the user returns via `return_url` and the page loads fresh from `/order-payment-confirmation`.

#### Session recovery

A localStorage-based session (`goPayPaymentSession`) stores `orderUuid`, `orderUrlHash`, validity hash, and domain URL during payment. This enables recovery when:

- The user returns from 3DS/bank redirect and Next.js query params are lost
- The user navigates back to `/order-confirmation` after payment (auto-redirects to `/order-payment-confirmation`)
- The user hits browser back to stale checkout steps with empty cart (recovers to `/order-payment-confirmation`)

Session has a 30-minute TTL and is cleared after terminal payment status handling.

#### Payment status page security

Access to payment status page content is protected by a server-generated `orderPaymentStatusPageValidityHash` (UUID-based). The hash is:

- Reset before each payment attempt (via `PayOrder`)
- Returned in the `PayOrder` response for frontend use
- Included in the GoPay `return_url` query parameters
- Validated by `UpdatePaymentStatus` to open a time-limited (5-minute) access window

#### Payment retry and method change

When a payment fails, the user sees retry options on `/order-payment-confirmation`:

- **Same GoPay method**: Triggers a new `PayOrder` mutation and opens a new iframe
- **Different GoPay sub-type** (e.g., bank transfer with SWIFT): Uses `ChangePaymentInOrder` mutation with `paymentGoPayBankSwift` parameter
- **Non-GoPay method** (e.g., cash on delivery): Uses `ChangePaymentInOrder` and redirects to order detail

GoPay enforces a maximum transaction count (default 2). When reached, the backend returns a `max-transaction-count-reached` error, GoPay options are removed from the payment list, and the user must choose a different payment method.

#### GTM analytics (`ec.payment`)

A single unified `ec.payment` event is emitted for every payment state reached on a confirmation page (successful, in-process, or failed):

- Primary emission is on `/order-payment-confirmation` on the first resolved payment state
- Fallback emission on `/order-confirmation` only when no GoPay session redirect is expected
- Deduplication via `sessionStorage` ensures at most one event per payment attempt (keyed by `orderUuid` + `paymentRetryCount` + `paymentName`)
- `isPaymentSuccessful` is `true` for `isPaid` **and** for `InProcess` states; it is `false` only when the payment has definitively failed. For pending bank transfers we treat the payment as optimistically successful — GoPay does not confirm manual transfers asynchronously, so we cannot distinguish an abandoned transfer from a completed one. The first-seen state wins: if a later recheck flips InProcess to failed, the event is not re-emitted

#### Browser back protection

During an active inline GoPay iframe session, `popstate` and `pageshow` event listeners prevent the user from navigating away. If the user presses browser back, the handler closes the iframe and redirects to `/order-payment-confirmation` for status verification.

### GoPay Backend Components

#### GoPayFacade

- Implements the [`PaymentServiceInterface`]({{github.link}}/packages/framework/src/Model/Payment/Service/PaymentServiceInterface.php)
- Handles communication with the GoPay API
- Manages payment transactions and refunds
- See [`GoPayFacade`]({{github.link}}/packages/framework/src/Model/GoPay/GoPayFacade.php)

#### GoPayClient

- Handles low-level communication with the GoPay API
- Manages authentication and API requests
- Processes responses from the GoPay service
- See [`GoPayClient`]({{github.link}}/packages/framework/src/Model/GoPay/GoPayClient.php)

#### GoPayClientFactory

- Creates instances of the [`GoPayClient`]({{github.link}}/packages/framework/src/Model/GoPay/GoPayClient.php)
- Manages configuration for different domains
- Supports both production and test environments
- See [`GoPayClientFactory`]({{github.link}}/packages/framework/src/Model/GoPay/GoPayClientFactory.php)

#### Order Entity Extensions

The `Order` entity includes GoPay-specific fields and methods:

- `goPayBankSwift`: Stores the SWIFT code for GoPay bank transfer payments
- `orderPaymentStatusPageValidityHash`: UUID-based hash for authorizing payment status page access
- `orderPaymentStatusPageValidFrom`: Timestamp for the 5-minute access window
- `hasPaymentInProcess()`: Checks if any payment transaction is still processing
- `resetOrderPaymentStatusPageValidityHash()`: Generates a fresh hash, invalidating previous links

#### Cron Modules

- [`GoPayAvailablePaymentsCronModule`]({{github.link}}/packages/framework/src/Model/GoPay/GoPayAvailablePaymentsCronModule.php): Downloads and updates available GoPay payment methods for all domains
- [`OrderGoPayStatusUpdateCronModule`]({{github.link}}/packages/framework/src/Model/GoPay/OrderGoPayStatusUpdateCronModule.php): Updates the status of unpaid GoPay orders from the last 21 days and sends email notifications when an order's payment status changes from unpaid to paid

### Configuring GoPay

To configure GoPay in your Shopsys project, you need to set up the `GOPAY_CONFIG` environment variable.
The variable defines your GoPay merchant ID (goid), your client ID, and client secret.
Moreover, it specifies whether to use the production or test environment, and which domains should have GoPay enabled.
For inspiration, see the configuration in [`.env.test`]({{github.link}}/project-base/app/.env.test) file.

#### Local development and testing

For local development and testing, you can use the GoPay test environment. Set the `GOPAY_CONFIG` environment variable in your `.env.local` file to use the test credentials provided by GoPay. This allows you to simulate payment transactions without affecting real accounts.

Moreover, GoPay does not allow the usage of `localhost` or `127.0.0.1` as a return URL for payment callbacks. Instead, you can use the following local setup:

- modify your `/etc/hosts` file to include a custom domain, such as `app.test`. You will need to use `sudo` to edit this file.
- set the same domain URL in your `app/config/domains_urls.yaml` config file
- edit your `storefront/.env.local`:

```
DOMAIN_HOSTNAME_1=http://app.test:8000/
PUBLIC_GRAPHQL_ENDPOINT_HOSTNAME_1=http://app.test:8000/graphql/
```

- recreate the storefront container with `docker compose up -d --force-recreate storefront`
- enjoy the GoPay integration in your local environment on `http://app.test:8000/`

For automated tests, there is a test GoPay client that simulates the GoPay API responses. This allows you to run tests without needing access to the actual GoPay service.
See the [`GoPayClient`]({{github.link}}/project-base/app/tests/FrontendApiBundle/Functional/Payment/GoPay/GoPayClient.php) class for more details.

## Implementing a Payment Gateway

To implement a new payment gateway in Shopsys:

#### Add a new payment type

- Extend the [`PaymentTypeEnum`]({{github.link}}/packages/framework/src/Model/Payment/PaymentTypeEnum.php) class to include your new payment type

#### Implement the PaymentServiceInterface

- Create a new class that implements [`PaymentServiceInterface`]({{github.link}}/packages/framework/src/Model/Payment/Service/PaymentServiceInterface.php) and implement the required methods

#### Register your implementation

- Extend the [`PaymentServiceFacade`]({{github.link}}/packages/framework/src/Model/Payment/Service/PaymentServiceFacade.php) and configure it to use your implementation for your payment type

#### Configure frontend components

- Implement the necessary frontend components for your payment gateway
- Update the order process to support your payment gateway

## Frontend API Integration

The Shopsys Frontend API provides GraphQL queries and mutations for working with payment methods:

### Queries

#### payments

- Returns a complete list of payment methods with details like name, price, and associated transport methods
- Example: `query { payments { uuid, name, price { priceWithVat } } }`

#### payment

- Returns detailed information about a specific payment method
- Example: `query { payment(uuid: "...") { uuid, name, description } }`

#### orderPayments

- Returns payments available for a given order
- Example: `query { orderPayments(orderUuid: "...") { ... } }`

#### GoPaySwifts

- Returns a list of available banks for GoPay bank transfer payment
- Example: `query { GoPaySwifts(currencyCode: "CZK") { ... } }`

#### Order.paymentPageContent

- Returns localized payment page content for the order's current payment state (successful, in-process, or failed)
- Available as a field on the `Order` type, resolved via `orderPaymentPageContentByOrderUuidQuery`
- Respects the 5-minute validity window; returns `null` if the window has expired
- Uses in-memory cache to avoid redundant DB lookups within the same GraphQL request

### Mutations

#### PayOrder

- Creates a payment transaction in a payment gateway and returns payment setup data
- The response includes `orderPaymentStatusPageValidityHash` for authorizing access to the payment status page
- Backend resets the validity hash before creating the payment setup, ensuring each attempt gets a fresh hash
- Example: `mutation { PayOrder(orderUuid: "...") { gatewayUrl, goPayCreatePaymentSetup, orderPaymentStatusPageValidityHash } }`

#### UpdatePaymentStatus

- Checks the payment status of an order after a callback from the payment service
- Accepts `orderPaymentStatusPageValidityHash` parameter; when valid, opens a 5-minute time-limited window for payment status page content access
- Returns the full `Order` object including `paymentPageContent` for the resolved payment state
- Example: `mutation { UpdatePaymentStatus(orderUuid: "...", orderPaymentStatusPageValidityHash: "...") { isPaid, payment { type }, paymentPageContent { ... } } }`

#### ChangePaymentInOrder

- Changes payment in an order after order creation (available for unpaid GoPay orders only)
- Accepts `paymentGoPayBankSwift` parameter to properly persist the SWIFT code when switching to GoPay bank transfer
- Example: `mutation { ChangePaymentInOrder(input: { orderUuid: "...", paymentUuid: "...", paymentGoPayBankSwift: "FIOBCZPP" }) { ... } }`

#### SetOrderPaymentStatusPageValidityHash

- Resets the payment status page validity hash server-side and returns both the hash and GoPay embed JS URL in a single response
- Used by the "Show payment instruction" button for in-process payments
- Returns `PaymentInstructionSetupData` type with `goPayEmbedJs` and `orderPaymentStatusPageValidityHash`
- Example: `mutation { SetOrderPaymentStatusPageValidityHashMutation(orderUuid: "...") { goPayEmbedJs, orderPaymentStatusPageValidityHash } }`

#### ChangePaymentInCart

- Adds a payment to the cart or removes a payment from the cart
- Example: `mutation { ChangePaymentInCart(input: { ... }) { ... } }`

These GraphQL operations allow frontend applications to display payment options to customers and process payments through the selected payment gateway.

## Storefront Components

The GoPay integration on the storefront is built from several specialized components and hooks:

### Key Components

- **`GoPayGateway`**: Manages the GoPay inline iframe checkout. Auto-triggers payment on first visit, handles callback routing (internal return vs. external 3DS redirect), and manages loading/error states. See [`GoPayGateway.tsx`]({{github.link}}/project-base/storefront/components/Pages/Order/PaymentConfirmation/Gateways/GoPayGateway.tsx)
- **`PaymentStatus`**: Simple switch component that renders the appropriate UI based on payment state (`Successful`, `InProcess`, `Failed`). See [`PaymentStatus.tsx`]({{github.link}}/project-base/storefront/components/Pages/Order/PaymentConfirmation/PaymentStatus.tsx)
- **`ShowPaymentInstructionButton`**: For orders in `InProcess` state, allows re-displaying GoPay payment instructions via a fresh iframe. See [`ShowPaymentInstructionButton.tsx`]({{github.link}}/project-base/storefront/components/Pages/Order/PaymentConfirmation/ShowPaymentInstructionButton.tsx)
- **`PaymentsInOrderSelect`**: Payment retry selector shown on failed payments. Handles same-method retry (re-triggers `GoPayGateway`), GoPay sub-type switching with SWIFT code, and non-GoPay method change via `ChangePaymentInOrder`. See [`PaymentsInOrderSelect.tsx`]({{github.link}}/project-base/storefront/components/PaymentsInOrderSelect/PaymentsInOrderSelect.tsx)
- **`PaymentVerificationLoader`**: Full-screen loading overlay with lock icon shown during payment verification. See [`PaymentVerificationLoader.tsx`]({{github.link}}/project-base/storefront/components/Pages/Order/PaymentConfirmation/PaymentVerificationLoader.tsx)

### Key Hooks

- **`useGoPayInlineCheckout`**: Manages GoPay script loading, `_gopay.checkout()` initialization, and callback handling with retry logic (up to 10 attempts). See [`useGoPayInlineCheckout.ts`]({{github.link}}/project-base/storefront/components/Pages/Order/PaymentConfirmation/Gateways/useGoPayInlineCheckout.ts)
- **`useInlinePaymentBackGuard`**: Listens to `popstate`/`pageshow` events during active payment to prevent browser-back navigation away from the payment iframe. See [`useInlinePaymentBackGuard.ts`]({{github.link}}/project-base/storefront/components/Pages/Order/PaymentConfirmation/useInlinePaymentBackGuard.ts)
- **`useGoPaySessionRecovery`**: Checks localStorage for saved GoPay session on mount and auto-redirects to `/order-payment-confirmation` when recovery is needed. See [`useGoPaySessionRecovery.ts`]({{github.link}}/project-base/storefront/components/Pages/Order/PaymentConfirmation/useGoPaySessionRecovery.ts)
- **`useRefreshOrderPaymentStatus`**: Background refresh for order detail pages — calls `UpdatePaymentStatus` once for orders in `InProcess` state. See [`useRefreshOrderPaymentStatus.ts`]({{github.link}}/project-base/storefront/components/Pages/Order/PaymentConfirmation/useRefreshOrderPaymentStatus.ts)
- **`useSanitizeOrderPaymentQuery`**: Removes PII (`orderEmail`) and empty `orderUrlHash` from URL query parameters. See [`useSanitizeOrderPaymentQuery.ts`]({{github.link}}/project-base/storefront/components/Pages/Order/PaymentConfirmation/useSanitizeOrderPaymentQuery.ts)

### GTM Utilities

- **`gtmPaymentEventDedup`**: Deduplication via `sessionStorage` keyed by `[orderUuid, retryCount, paymentName]`. See [`gtmPaymentEventDedup.ts`]({{github.link}}/project-base/storefront/gtm/utils/gtmPaymentEventDedup.ts)
- **`gtmPaymentEventLocalStorage`**: Persists pending payment data (`orderUuid`, `orderNumber`, `paymentName`, retry count) across page navigations. See [`gtmPaymentEventLocalStorage.ts`]({{github.link}}/project-base/storefront/gtm/utils/gtmPaymentEventLocalStorage.ts)
- **`useEmitPendingPaymentEvent`**: Centralized hook for `ec.payment` emission across all pages. See [`useEmitPendingPaymentEvent`]({{github.link}}/project-base/storefront/gtm/hooks/)
- **`goPayPaymentSessionStorage`**: localStorage utility for GoPay session persistence with domain matching and 30-minute TTL. See [`goPayPaymentSessionStorage.ts`]({{github.link}}/project-base/storefront/utils/goPayPaymentSessionStorage.ts)

## Conclusion

The payment gateway system in Shopsys provides a flexible and extensible framework for integrating various payment processors. The GoPay integration serves as a comprehensive example of how to implement a payment gateway, including inline iframe checkout, session recovery, payment retry, and analytics deduplication.

For more detailed information about specific payment gateways or implementation details, refer to the code in the [`Shopsys\FrameworkBundle\Model\Payment`]({{github.link}}/packages/framework/src/Model/Payment/) and [`Shopsys\FrameworkBundle\Model\GoPay`]({{github.link}}/packages/framework/src/Model/GoPay/) namespaces. For the detailed behavioral specification used by coding agents, see [`docs/research/agent-ready-specification/gopay-gateway-and-ec-payment-flow.md`]({{github.link}}/docs/research/agent-ready-specification/gopay-gateway-and-ec-payment-flow.md).
