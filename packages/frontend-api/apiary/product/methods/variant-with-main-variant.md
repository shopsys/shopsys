### Variant with main variant [/graphql{?product_variant}]

#### POST [POST]

Adds main variant for variants

- Request (application/json)

    - Attributes

        - uuid (required)

    - Body

            {
                query: product(uuid: "81075e9a-29dc-4d37-b7aa-20800e8959cc") {
                    __typename
                    uuid
                    name
                    ...on Variant {
                      mainVariant {
                        name
                      }
                    }
                }
            }

- Response 200 (application/json; charset=utf-8)

        {
            "data": {
                "query": {
                    "__typename": "Variant",
                    "uuid": "81075e9a-29dc-4d37-b7aa-20800e8959cc",
                    "name": "51,5” Hyundai 22HD44D",
                    "mainVariant": {
                        "name": "Hyundai 22HD44D"
                    }
                }
            }
        }
