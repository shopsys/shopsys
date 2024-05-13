### Order detail authorized [/graphql{?order_detail_authorized}]

#### POST [POST]

Returns order filtered using UUID and access token

- Request (application/json)

    - Headers

            :[headers-authorization](../../components/headers/authorization.md)

    - Attributes

        - uuid (required)

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
