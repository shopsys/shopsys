### Main variant with variants [/graphql{?product_main_variant}]

#### POST [POST]

Lists variants for main variant

- Request (application/json)

    - Attributes

        - uuid (required)

    - Body

            {
                query: product(uuid: "49bc5aa8-c7e1-477d-9e0b-0208129ef56b") {
                    __typename
                    uuid
                    name
                    ...on MainVariant {
                      variants {
                        name
                      }
                    }
                }
            }

- Response 200 (application/json; charset=utf-8)

        {
            "data": {
                "query": {
                    "__typename": "MainVariant",
                    "uuid": "49bc5aa8-c7e1-477d-9e0b-0208129ef56b",
                    "name": "Hyundai 22HD44D",
                    "variants": [
                        {
                            "name": "51,5\" Hyundai 22HD44D"
                        },
                        {
                            "name": "60\" Hyundai 22HD44D"
                        },
                        {
                            "name": "Hyundai 22HD44D"
                        }
                    ]
                }
            }
        }
