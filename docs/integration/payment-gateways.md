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

### GoPay Components

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

Operations that require proof of access to an order accept it in two forms: the `orderUrlHash` argument, or the logged-in customer owning the order.
When `orderUrlHash` is provided, it is the only proof evaluated — a hash that does not match the order and the current domain is rejected with `order-not-found` even for a logged-in customer owning the order.

### Queries

#### payments

- Returns a complete list of payment methods with details like name, price, and associated transport methods
- Example: `query { payments { uuid, name, price { priceWithVat } } }`

#### payment

- Returns detailed information about a specific payment method
- Example: `query { payment(uuid: "...") { uuid, name, description } }`

#### orderPayments

- Returns payments available for a given order
- Requires proof of access to the order: the customer must be logged in and own the order, or the `orderUrlHash` argument must be provided
- Example: `query { orderPayments(orderUuid: "...", orderUrlHash: "...") { ... } }`

#### GoPaySwifts

- Returns a list of available banks for GoPay bank transfer payment
- Example: `query { GoPaySwifts(currencyCode: "CZK") { ... } }`

### Mutations

#### PayOrder

- Creates a payment transaction in a payment gateway and returns payment setup data
- Requires proof of access to the order: the customer must be logged in and own the order, or the `orderUrlHash` argument must be provided
- Example: `mutation { PayOrder(orderUuid: "...", orderUrlHash: "...") { ... } }`

#### UpdatePaymentStatus

- Checks the payment status of an order after a callback from the payment service and returns the limited `UpdatePaymentStatusResult` type without any personal data
- Requires proof of access to the order: the customer must be logged in and own the order, or the `orderUrlHash` argument must be provided
- Example: `mutation { UpdatePaymentStatus(orderUuid: "...", orderUrlHash: "...") { isPaid } }`

#### ChangePaymentInOrder

- Changes payment in an order after order creation (available for unpaid GoPay orders only)
- Requires proof of access to the order: the customer must be logged in and own the order, or the `orderUrlHash` input field must be provided
- Example: `mutation { ChangePaymentInOrder(input: { ... }) { ... } }`

#### ChangePaymentInCart

- Adds a payment to the cart or removes a payment from the cart
- Example: `mutation { ChangePaymentInCart(input: { ... }) { ... } }`

These GraphQL operations allow frontend applications to display payment options to customers and process payments through the selected payment gateway.

## Conclusion

The payment gateway system in Shopsys provides a flexible and extensible framework for integrating various payment processors. The GoPay integration serves as a comprehensive example of how to implement a payment gateway in Shopsys.

For more detailed information about specific payment gateways or implementation details, refer to the code in the [`Shopsys\FrameworkBundle\Model\Payment`]({{github.link}}/packages/framework/src/Model/Payment/) and [`Shopsys\FrameworkBundle\Model\GoPay`]({{github.link}}/packages/framework/src/Model/GoPay/) namespaces.
