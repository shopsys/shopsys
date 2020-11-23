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
