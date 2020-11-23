### Transport method detail [/graphql{?transport_detail}]

#### POST [POST]

Returns transport filtered using UUID

- Request (application/json)

    - Attributes

        - uuid

    - Body

            {
                query: transport(uuid: "edbafa26-9306-46ed-b1e8-d1570f072832") {
                    uuid
                    name
                    description
                    instruction
                    position
                    price {
                        priceWithVat
                        priceWithoutVat
                        vatAmount
                    }
                    images {
                        type
                        position
                        size
                        url
                        width
                        height
                    }
                    payments {
                        uuid
                        name
                    }
                }
            }

- Response 200 (application/json; charset=utf-8)

        {
            "data": {
                "query": {
                    "uuid": "edbafa26-9306-46ed-b1e8-d1570f072832",
                    "name": "Czech post",
                    "description": null,
                    "instruction": null,
                    "position": 0,
                    "price": {
                        "priceWithVat": "4.84",
                        "priceWithoutVat": "4.00",
                        "vatAmount": "0.84"
                    },
                    "images": [
                        {
                            "type": null,
                            "position": null,
                            "size": "default",
                            "url": "http://127.0.0.1:8000/content/images/transport/default/56.jpg",
                            "width": null,
                            "height": 20
                        },
                        {
                            "type": null,
                            "position": null,
                            "size": "original",
                            "url": "http://127.0.0.1:8000/content/images/transport/original/56.jpg",
                            "width": null,
                            "height": null
                        }
                    ],
                    "payments": [
                        {
                            "uuid": "840f9191-c6f2-4ab0-9788-9c038f211ca6",
                            "name": "Cash on delivery"
                        }
                    ]
                }
            }
        }
