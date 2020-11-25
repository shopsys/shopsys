FORMAT: 1A
HOST: https://polls.apiblueprint.org/

# Frontend API

:[advert](advert/index.md)

:[article](article/index.md)

:[brand](brand/index.md)

:[category](category/index.md)

:[customer](customer/index.md)

:[order](order/index.md)

:[payment](payment/index.md)

:[product](product/index.md)

:[transport](transport/index.md)

## Data Structures
### ProductOrderingModeEnum (enum)
+ `PRIORITY`
+ `PRICE_ASC`
+ `PRICE_DESC`
+ `NAME_ASC`
+ `NAME_DESC`
+ `RELEVANCE`

### InputPrice (object)
- priceWithVat (required) - Price with VAT
- priceWithoutVat (required) - Price without VAT
- vatAmount (required) - Total value of VAT

### InputPayment (object)
- uuid (required) - UUID
- price (InputPrice, required) - Price for payment

### InputTransport (object)
- uuid (required) - UUID
- price (InputPrice, required) - Price for transport

### InputOrderProduct
- uuid (required) - UUID
- quantity (number, required) - Quantity of products
- unitPrice (InputPrice, required) - Product price per unit

### InputChangeCustomerUser

- firstName (required) - Customer user first name
- lastName (required) - Customer user last name
- telephone (required) - Customer telephone number

### InputLogin
- email (required) - Customer user email
- password (required) - Customer user password

### InputRegisterNewCustomerUser

- firstName (required) - Customer user first name
- lastName (required) - Customer user last name
- Include InputLogin

### InputRefreshToken

- refreshToken (required) - Refresh token

### InputChangePassword

- email (required) - Customer user email
- oldPassword (required) - Current customer user password
- newPassword (required) - New customer user password
