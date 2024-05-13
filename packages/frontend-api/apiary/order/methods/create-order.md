### Create order [/graphql{?create_order}]

#### POST [POST]

Creates complete order with products and addresses

- Request (application/json)

    - Headers
    
            :[headers-authorization](../../components/headers/authorization.md) (optional)

    - Attributes

        - firstName (required) - The customer's first name
        - lastName (required) - The customer's last name
        - email (required) - The customer's email address
        - telephone (required) - The customer's phone number
        - onCompanyBehalf (boolean, required) - Determines whether the order is made on the company behalf.
        - companyName - The customer’s company name (required when onCompanyBehalf is true)
        - companyNumber - The customer’s company identification number (required when onCompanyBehalf is true)
        - companyTaxNumber - The customer’s company tax number (required when onCompanyBehalf is true)
        - street (required) - Billing address street name (will be on the tax invoice)
        - city (required) - Billing address city name (will be on the tax invoice)
        - postcode (required) - Billing address zip code (will be on the tax invoice)
        - country (required) - Billing address country code in ISO 3166-1 alpha-2 (Country will be on the tax invoice)
        - IsDeliveryAddressDifferentFromBilling (boolean, required) - Determines whether to deliver products to a different address than the billing one
        - deliveryFirstName - First name of the contact person for delivery (required when IsDeliveryAddressDifferentFromBilling is true)
        - deliveryLastName - Last name of the contact person for delivery (required when IsDeliveryAddressDifferentFromBilling is true)
        - deliveryCompanyName - Company name for delivery
        - deliveryTelephone - Contact telephone number for delivery
        - deliveryStreet - Street name for delivery (required when IsDeliveryAddressDifferentFromBilling is true)
        - deliveryCity - City name for delivery (required when IsDeliveryAddressDifferentFromBilling is true)
        - deliveryPostcode - Zip code for delivery (required when IsDeliveryAddressDifferentFromBilling is true)
        - deliveryCountry - Country code in ISO 3166-1 alpha-2 for delivery (required when IsDeliveryAddressDifferentFromBilling is true)
        - note - Other information related to the order
        - payment (InputPayment, required) - Payment method applied to the order
        - transport (InputTransport, required) - Transport method applied to the order
        - products (array, required) - A list of products to order to
            - (InputOrderProduct)

    - Body

            mutation {
                CreateOrder(
                    input: {
                        firstName: "firstName"
                        lastName: "lastName"
                        email: "user@example.com"
                        telephone: "+53 123456789"
                        onCompanyBehalf: true
                        companyName: "Airlocks s.r.o."
                        companyNumber: "1234"
                        companyTaxNumber: "EU4321"
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
                        IsDeliveryAddressDifferentFromBilling: true
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
                    IsDeliveryAddressDifferentFromBilling
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
            "data": {
                "CreateOrder": {
                    "transport": {
                        "name": "Czech post"
                    },
                    "payment": {
                        "name": "Cash on delivery"
                    },
                    "status": "New",
                    "totalPrice": {
                        "priceWithVat": "1406.44",
                        "priceWithoutVat": "1162.69",
                        "vatAmount": "243.75"
                    },
                    "items": [
                        {
                            "name": "22\" Sencor SLE 22F46DM4 HELLO KITTY",
                            "unitPrice": {
                                "priceWithVat": "139.96",
                                "priceWithoutVat": "115.67",
                                "vatAmount": "24.29"
                            },
                            "totalPrice": {
                                "priceWithVat": "1399.60",
                                "priceWithoutVat": "1156.69",
                                "vatAmount": "242.91"
                            },
                            "quantity": 10,
                            "vatRate": "21.0000",
                            "unit": "pcs"
                        },
                        {
                            "name": "Cash on delivery",
                            "unitPrice": {
                                "priceWithVat": "2.00",
                                "priceWithoutVat": "2.00",
                                "vatAmount": "0.00"
                            },
                            "totalPrice": {
                                "priceWithVat": "2.00",
                                "priceWithoutVat": "2.00",
                                "vatAmount": "0.00"
                            },
                            "quantity": 1,
                            "vatRate": "0.0000",
                            "unit": null
                        },
                        {
                            "name": "Czech post",
                            "unitPrice": {
                                "priceWithVat": "4.84",
                                "priceWithoutVat": "4.00",
                                "vatAmount": "0.84"
                            },
                            "totalPrice": {
                                "priceWithVat": "4.84",
                                "priceWithoutVat": "4.00",
                                "vatAmount": "0.84"
                            },
                            "quantity": 1,
                            "vatRate": "21.0000",
                            "unit": null
                        }
                    ],
                    "firstName": "firstName",
                    "lastName": "lastName",
                    "email": "user@example.com",
                    "telephone": "+53 123456789",
                    "companyName": "Airlocks s.r.o.",
                    "companyNumber": "1234",
                    "companyTaxNumber": "EU4321",
                    "street": "123 Fake Street",
                    "city": "Springfield",
                    "postcode": "12345",
                    "country": "CZ",
                    "IsDeliveryAddressDifferentFromBilling": true,
                    "deliveryFirstName": "deliveryFirstName",
                    "deliveryLastName": "deliveryLastName",
                    "deliveryCompanyName": null,
                    "deliveryTelephone": null,
                    "deliveryStreet": "deliveryStreet",
                    "deliveryCity": "deliveryCity",
                    "deliveryPostcode": "13453",
                    "deliveryCountry": "SK",
                    "note": "Thank You"
                }
            }
        }
