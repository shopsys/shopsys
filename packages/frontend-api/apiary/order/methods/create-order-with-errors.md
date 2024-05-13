### Create order without supplying required fields [/graphql{?create_order_with_errors}]

#### POST [POST]

Returns validation error

- Request (application/json)

        mutation {
            CreateOrder(
                input: {
                    firstName: "firstName"
                    lastName: "lastName"
                    email: "user@example.com"
                    telephone: "+53 123456789"
                    onCompanyBehalf: true
                    street: "123 Fake Street"
                    city: "Springfield"
                    postcode: "12345"
                    country: "CZ"
                    note:"Thank You"
                    payment: {
                        uuid: "840f9191-c6f2-4ab0-9788-9c038f211ca6"
                        price: {
                            priceWithVat: "2"
                            priceWithoutVat: "2"
                            vatAmount: "0"
                        }
                    }
                    transport: {
                        uuid: "edbafa26-9306-46ed-b1e8-d1570f072832"
                        price: {
                            priceWithVat: "4.84"
                            priceWithoutVat: "4"
                            vatAmount: "0.84"
                        }
                    }
                    isDeliveryAddressDifferentFromBilling: true
                    deliveryFirstName: "deliveryFirstName"
                    deliveryLastName: "deliveryLastName"
                    deliveryStreet: "deliveryStreet"
                    deliveryCity: "deliveryCity"
                    deliveryCountry: "SK"
                    deliveryPostcode: "13453"
                    products: [
                        {
                            uuid: "04a561ae-d08c-47f9-aed0-e52c469038aa"
                            unitPrice: {
                                priceWithVat: "139.96"
                                priceWithoutVat: "115.67"
                                vatAmount: "24.29"
                            }
                            quantity: 10
                        },
                    ]
                }
            ) {
                transport {
                    name
                }
                payment {
                    name
                }
                status
                totalPrice {
                    priceWithVat
                    priceWithoutVat
                    vatAmount
                }
                items {
                    name
                    unitPrice {
                        priceWithVat
                        priceWithoutVat
                        vatAmount
                    }
                    totalPrice {
                        priceWithVat
                        priceWithoutVat
                        vatAmount
                    }
                    quantity
                    vatRate
                    unit
                }
                firstName
                lastName
                email
                telephone
                companyName
                companyNumber
                companyTaxNumber
                street
                city
                postcode
                country
                isDeliveryAddressDifferentFromBilling
                deliveryFirstName
                deliveryLastName
                deliveryCompanyName
                deliveryTelephone
                deliveryStreet
                deliveryCity
                deliveryPostcode
                deliveryCountry
                note
            }
        }

- Response 200 (application/json; charset=utf-8)

        {
            "errors": [
                {
                    "message": "validation",
                    "extensions": {
                        "category": "arguments_validation_error",
                        "validation": {
                            "input.companyName": [
                                {
                                    "message": "Please enter company name",
                                    "code": "c1051bb4-d103-4f74-8988-acbcafc7fdc3"
                                }
                            ],
                            "input.companyNumber": [
                                {
                                    "message": "Please enter identification number",
                                    "code": "c1051bb4-d103-4f74-8988-acbcafc7fdc3"
                                }
                            ]
                        }
                    },
                    "locations": [
                        {
                            "line": 2,
                            "column": 9
                        }
                    ],
                    "path": [
                        "CreateOrder"
                    ]
                }
            ]
        }
