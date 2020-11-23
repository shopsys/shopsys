### Detail of payment method [/graphql{?payment_detail}]

#### POST [POST]

Returns payment filtered using UUID

- Request (application/json)

    - Attributes

        - uuid

    - Body

            {
                query: payment(uuid: "81c834a6-f993-46f3-a3cd-a8ee868884e3") {
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
                    transports {
                        uuid
                        name
                    }
                }
            }

- Response 200 (application/json; charset=utf-8)

        {
            "data": {
                "query": {
                    "uuid": "81c834a6-f993-46f3-a3cd-a8ee868884e3",
                    "name": "Credit card",
                    "description": "Quick, cheap and reliable!",
                    "instruction": null,
                    "position": 0,
                    "price": {
                        "priceWithVat": "4.00",
                        "priceWithoutVat": "4.00",
                        "vatAmount": "0.00"
                    },
                    "images": [
                        {
                            "type": null,
                            "position": null,
                            "size": "default",
                            "url": "http://127.0.0.1:8000/content/images/payment/default/53.jpg",
                            "width": null,
                            "height": 20
                        },
                        {
                            "type": null,
                            "position": null,
                            "size": "original",
                            "url": "http://127.0.0.1:8000/content/images/payment/original/53.jpg",
                            "width": null,
                            "height": null
                        }
                    ],
                    "transports": [
                        {
                            "uuid": "2cc1d47a-6832-43d2-b9cc-738824b3613b",
                            "name": "PPL"
                        },
                        {
                            "uuid": "adecf603-f491-4ece-b049-8d0d86a1e48c",
                            "name": "Personal collection"
                        }
                    ]
                }
            }
        }

### List of payment methods [/graphql{?payments}]

#### POST [POST]

Returns complete list of payment methods

- Request (application/json)

        {
            query: payments {
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
                transports {
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
                         "uuid": "81c834a6-f993-46f3-a3cd-a8ee868884e3",
                         "name": "Credit card",
                         "description": "Quick, cheap and reliable!",
                         "instruction": null,
                         "position": 0,
                         "price": {
                             "priceWithVat": "4.00",
                             "priceWithoutVat": "4.00",
                             "vatAmount": "0.00"
                         },
                         "images": [
                             {
                                 "type": null,
                                 "position": null,
                                 "size": "default",
                                 "url": "http://127.0.0.1:8000/content/images/payment/default/53.jpg",
                                 "width": null,
                                 "height": 20
                             },
                             {
                                 "type": null,
                                 "position": null,
                                 "size": "original",
                                 "url": "http://127.0.0.1:8000/content/images/payment/original/53.jpg",
                                 "width": null,
                                 "height": null
                             }
                         ],
                         "transports": [
                             {
                                 "uuid": "2cc1d47a-6832-43d2-b9cc-738824b3613b",
                                 "name": "PPL"
                             },
                             {
                                 "uuid": "adecf603-f491-4ece-b049-8d0d86a1e48c",
                                 "name": "Personal collection"
                             }
                         ]
                     },
                     {
                         "uuid": "840f9191-c6f2-4ab0-9788-9c038f211ca6",
                         "name": "Cash on delivery",
                         "description": null,
                         "instruction": null,
                         "position": 1,
                         "price": {
                             "priceWithVat": "2.00",
                             "priceWithoutVat": "2.00",
                             "vatAmount": "0.00"
                         },
                         "images": [
                             {
                                 "type": null,
                                 "position": null,
                                 "size": "default",
                                 "url": "http://127.0.0.1:8000/content/images/payment/default/55.jpg",
                                 "width": null,
                                 "height": 20
                             },
                             {
                                 "type": null,
                                 "position": null,
                                 "size": "original",
                                 "url": "http://127.0.0.1:8000/content/images/payment/original/55.jpg",
                                 "width": null,
                                 "height": null
                             }
                         ],
                         "transports": [
                             {
                                 "uuid": "edbafa26-9306-46ed-b1e8-d1570f072832",
                                 "name": "Czech post"
                             }
                         ]
                     },
                     {
                         "uuid": "76145869-900e-4051-a7be-2e0d1796cd6b",
                         "name": "Cash",
                         "description": null,
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
                                 "url": "http://127.0.0.1:8000/content/images/payment/default/54.jpg",
                                 "width": null,
                                 "height": 20
                             },
                             {
                                 "type": null,
                                 "position": null,
                                 "size": "original",
                                 "url": "http://127.0.0.1:8000/content/images/payment/original/54.jpg",
                                 "width": null,
                                 "height": null
                             }
                         ],
                         "transports": [
                             {
                                 "uuid": "adecf603-f491-4ece-b049-8d0d86a1e48c",
                                 "name": "Personal collection"
                             }
                         ]
                     }
                 ]
             }
         }
