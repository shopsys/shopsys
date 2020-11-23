### Create order [/graphql{?create_order}]

#### POST [POST]

Creates complete order with products and addresses

- Request (application/json)

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
                    differentDeliveryAddress: true
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
                differentDeliveryAddress
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
                    "differentDeliveryAddress": true,
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
                    differentDeliveryAddress: true
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
                differentDeliveryAddress
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

### List of customer user orders [/graphql{?customer_user_orders}]

#### POST [POST]

Returns list of customer user orders that can be paginated using `first`, `last`, `before` and `after` keywords.
By default this list is limited to first 10 orders.
You can read more about Connection specification in [connections article](https://relay.dev/graphql/connections.htm).

- Request (application/json)

    - Headers

            Authorization: Bearer --ACCESS-TOKEN--

    - Attributes

        - after
        - first (number)
        - before
        - last (number)

    - Body

            {
                orders(first: 2) {
                    edges {
                        node {
                            uuid
                            number
                            urlHash
                            creationDate
                            status
                            totalPrice {
                                priceWithVat
                            }
                            items {
                                name
                            }
                            transport {
                                name
                            }
                            payment {
                                name
                            }
                            companyName
                            companyNumber
                            companyTaxNumber
                            firstName
                            lastName
                            email
                            street
                            city
                            postcode
                            country
                            differentDeliveryAddress
                            note
                        }
                    }
                }
            }


- Response 200 (application/json; charset=utf-8)

        {
            "data": {
                "orders": {
                    "edges": [
                        {
                            "node": {
                                "uuid": "2f310e40-5104-4bd3-1337-0ebd9fe7324e",
                                "number": "1095328621",
                                "urlHash": "ckbikWM3atXE471u0xvg0SqQP4gbwgbNjQ89de8qirbyzDQ74i",
                                "creationDate": "2020-07-20T20:46:26+00:00",
                                "status": "New",
                                "totalPrice": {
                                    "priceWithVat": "76.580000"
                                },
                                "items": [
                                    {
                                        "name": "Pot holder, black"
                                    },
                                    {
                                        "name": "Credit card"
                                    },
                                    {
                                        "name": "Personal collection"
                                    }
                                ],
                                "transport": {
                                    "name": "Personal collection"
                                },
                                "payment": {
                                    "name": "Credit card"
                                },
                                "companyName": null,
                                "companyNumber": null,
                                "companyTaxNumber": null,
                                "firstName": "Iva",
                                "lastName": "Jačková",
                                "email": "no-reply@shopsys.com",
                                "street": "Druhá 2",
                                "city": "Ostrava",
                                "postcode": "71300",
                                "country": "CZ",
                                "differentDeliveryAddress": false,
                                "note": null
                            }
                        },
                        {
                            "node": {
                                "uuid": "f236f5ac-6d0d-4074-a666-7b878e7a6937",
                                "number": "1595328622",
                                "urlHash": "fpb0c5Aasmw71CLkltLwZVm3h2efkfjMr3hsRkvgxupjYj31QO",
                                "creationDate": "2020-07-19T04:02:31+00:00",
                                "status": "New",
                                "totalPrice": {
                                    "priceWithVat": "83.580000"
                                },
                                "items": [
                                    {
                                        "name": "CD-R 210MB"
                                    },
                                    {
                                        "name": "Cash on delivery"
                                    },
                                    {
                                        "name": "Czech post"
                                    }
                                ],
                                "transport": {
                                    "name": "Czech post"
                                },
                                "payment": {
                                    "name": "Cash on delivery"
                                },
                                "companyName": null,
                                "companyNumber": null,
                                "companyTaxNumber": null,
                                "firstName": "Jan",
                                "lastName": "Adamovský",
                                "email": "no-reply@shopsys.com",
                                "street": "Třetí 3",
                                "city": "Ostrava",
                                "postcode": "71200",
                                "country": "CZ",
                                "differentDeliveryAddress": false,
                                "note": null
                            }
                        }
                    ]
                }
            }
        }

### Order detail authorized [/graphql{?order_detail_authorized}]

#### POST [POST]

Returns order filtered using UUID and access token

- Request (application/json)

    - Headers

            Authorization: Bearer --ACCESS-TOKEN--

    - Attributes

        - uuid

    - Body

            {
                order(uuid: "2f310e40-51O4-4bde-8517-0ebd9fe7324e") {
                    uuid
                    number
                    urlHash
                    creationDate
                    status
                    totalPrice {
                        priceWithVat
                    }
                    items {
                        name
                    }
                    transport {
                        name
                    }
                    payment {
                        name
                    }
                    companyName
                    companyNumber
                    companyTaxNumber
                    firstName
                    lastName
                    email
                    street
                    city
                    postcode
                    country
                    differentDeliveryAddress
                    note
                }
            }

- Response 200 (application/json; charset=utf-8)

        {
            "data": {
                "order": {
                    "uuid": "2f310e40-51O4-4bde-8517-0ebd9fe7324e",
                    "number": "1595328621",
                    "urlHash": "cKbikWM3ktXe471u0xvgOSqQP4rbwgbNjQ89de8qirbyzDQ74i",
                    "creationDate": "2020-07-20T20:46:26+00:00",
                    "status": "New",
                    "totalPrice": {
                        "priceWithVat": "76.580000"
                    },
                    "items": [
                        {
                            "name": "Pot holder, black"
                        },
                        {
                            "name": "Credit card"
                        },
                        {
                            "name": "Personal collection"
                        }
                    ],
                    "transport": {
                        "name": "Personal collection"
                    },
                    "payment": {
                        "name": "Credit card"
                    },
                    "companyName": null,
                    "companyNumber": null,
                    "companyTaxNumber": null,
                    "firstName": "Iva",
                    "lastName": "Jačková",
                    "email": "no-reply@shopsys.com",
                    "street": "Druhá 2",
                    "city": "Ostrava",
                    "postcode": "71300",
                    "country": "CZ",
                    "differentDeliveryAddress": false,
                    "note": null
                }
            }
        }

### Order detail unauthorized [/graphql{?order_detail_unauthorized}]

#### POST [POST]

Returns order filtered using url hash

- Request (application/json)

    - Attributes

        - urlHash

    - Body

            {
                order(urlHash:"cKbikWM3ktXe471u0xvgOSqQP4rbwgbNjQ89de8qirbyzDQ74i") {
                    uuid
                    number
                    urlHash
                    creationDate
                    status
                    totalPrice {
                        priceWithVat
                    }
                    items {
                        name
                    }
                    transport {
                        name
                    }
                    payment {
                        name
                    }
                    companyName
                    companyNumber
                    companyTaxNumber
                    firstName
                    lastName
                    email
                    street
                    city
                    postcode
                    country
                    differentDeliveryAddress
                    note
                }
            }


- Response 200 (application/json; charset=utf-8)

        {
            "data": {
                "order": {
                    "uuid": "2f310e40-51O4-4bde-8517-0ebd9fe7324e",
                    "number": "1595328621",
                    "urlHash": "cKbikWM3ktXe471u0xvgOSqQP4rbwgbNjQ89de8qirbyzDQ74i",
                    "creationDate": "2020-07-20T20:46:26+00:00",
                    "status": "New",
                    "totalPrice": {
                        "priceWithVat": "76.580000"
                    },
                    "items": [
                        {
                            "name": "Pot holder, black"
                        },
                        {
                            "name": "Credit card"
                        },
                        {
                            "name": "Personal collection"
                        }
                    ],
                    "transport": {
                        "name": "Personal collection"
                    },
                    "payment": {
                        "name": "Credit card"
                    },
                    "companyName": null,
                    "companyNumber": null,
                    "companyTaxNumber": null,
                    "firstName": "Iva",
                    "lastName": "Jačková",
                    "email": "no-reply@shopsys.com",
                    "street": "Druhá 2",
                    "city": "Ostrava",
                    "postcode": "71300",
                    "country": "CZ",
                    "differentDeliveryAddress": false,
                    "note": null
                }
            }
        }
