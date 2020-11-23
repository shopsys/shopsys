### List of transport methods [/graphql{?transports}]

#### POST [POST]

Returns complete list of transport methods

- Request (application/json)

        {
            query: transports {
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
                "query": [
                    {
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
                    },
                    {
                        "uuid": "2cc1d47a-6832-43d2-b9cc-738824b3613b",
                        "name": "PPL",
                        "description": null,
                        "instruction": null,
                        "position": 1,
                        "price": {
                            "priceWithVat": "9.68",
                            "priceWithoutVat": "8.00",
                            "vatAmount": "1.68"
                        },
                        "images": [
                            {
                                "type": null,
                                "position": null,
                                "size": "default",
                                "url": "http://127.0.0.1:8000/content/images/transport/default/57.jpg",
                                "width": null,
                                "height": 20
                            },
                            {
                                "type": null,
                                "position": null,
                                "size": "original",
                                "url": "http://127.0.0.1:8000/content/images/transport/original/57.jpg",
                                "width": null,
                                "height": null
                            }
                        ],
                        "payments": [
                            {
                                "uuid": "81c834a6-f993-46f3-a3cd-a8ee868884e3",
                                "name": "Credit card"
                            }
                        ]
                    },
                    {
                        "uuid": "adecf603-f491-4ece-b049-8d0d86a1e48c",
                        "name": "Personal collection",
                        "description": "You will be welcomed by friendly staff!",
                        "instruction": null,
                        "position": 2,
                        "price": {
                            "priceWithVat": "0.00",
                            "priceWithoutVat": "0.00",
                            "vatAmount": "0.00"
                        },
                        "images": [
                            {
                                "type": null,
                                "position": null,
                                "size": "default",
                                "url": "http://127.0.0.1:8000/content/images/transport/default/58.jpg",
                                "width": null,
                                "height": 20
                            },
                            {
                                "type": null,
                                "position": null,
                                "size": "original",
                                "url": "http://127.0.0.1:8000/content/images/transport/original/58.jpg",
                                "width": null,
                                "height": null
                            }
                        ],
                        "payments": [
                            {
                                "uuid": "81c834a6-f993-46f3-a3cd-a8ee868884e3",
                                "name": "Credit card"
                            },
                            {
                                "uuid": "76145869-900e-4051-a7be-2e0d1796cd6b",
                                "name": "Cash"
                            }
                        ]
                    }
                ]
            }
        }

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
