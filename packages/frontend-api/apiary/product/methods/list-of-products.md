### List of products [/graphql{?products}]

#### POST [POST]

Returns list of product that can be paginated using `first`, `last`, `before` and `after` keywords.
You can read more about Connection specification in [connections article](https://relay.dev/graphql/connections.htm).
If you use the authorization header, api will return prices according to the user's price group.
Authorization header is not required.

- Request (application/json)

    - Headers

            :[headers-authorization](../../components/headers/authorization.md)

    - Attributes

        - after
        - first (number) - Default: **10**
        - before
        - last (number)
        - orderingMode (ProductOrderingModeEnum)
        - filter (InputProductFilter)
        - search

    - Body

            {
                query: products (first: 2) {
                    edges {
                        node {
                            uuid
                            name
                            shortDescription
                            seoH1
                            seoTitle
                            seoMetaDescription
                            link
                            unit {
                                name
                            }
                            availability {
                                name
                            }
                            stockQuantity
                            categories {
                                name
                            }
                            flags {
                                name
                                rgbColor
                            }
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
                            brand {
                                name
                            }
                            accessories {
                                name
                            }
                            isSellingDenied
                            description
                            orderingPriority
                            parameters {
                                uuid
                                name
                                values {
                                    uuid
                                    text
                                }
                            }
                        }
                    }
                }
            }

- Response 200 (application/json; charset=utf-8)

        {
            "data": {
                "query": {
                    "edges": [
                        {
                            "node": {
                                "uuid": "5a095ccf-1ea4-47c6-b773-16b94234c150",
                                "name": "100 Czech crowns ticket",
                                "shortDescription": "Coupon valued to 100 Czech crowns. You can cash it at any exchange office",
                                "seoH1": "Ticket for 100 Czech crowns",
                                "seoTitle": "Ticket for 100 CZK",
                                "seoMetaDescription": "Coupon valued to 100 Czech crowns.",
                                "link": "http://127.0.0.1:8000/100-czech-crowns-ticket/",
                                "unit": {
                                    "name": "pcs"
                                },
                                "availability": {
                                    "name": "In stock"
                                },
                                "stockQuantity": 1000000,
                                "categories": [
                                    {
                                        "name": "Books"
                                    }
                                ],
                                "flags": [
                                    {
                                        "name": "TOP",
                                        "rgbColor": "#d6fffa"
                                    }
                                ],
                                "price": {
                                    "priceWithVat": "4.84",
                                    "priceWithoutVat": "4.00",
                                    "vatAmount": "0.84"
                                },
                                "images": [],
                                "brand": null,
                                "accessories": [],
                                "isSellingDenied": false,
                                "description": "Coupon valued to 100 Czech crowns. You can cash it at any exchange office",
                                "orderingPriority": null,
                                "parameters": []
                            }
                        },
                        {
                            "node": {
                                "uuid": "04a561ae-d08c-47f9-aed0-e52c469038aa",
                                "name": "22\" Sencor SLE 22F46DM4 HELLO KITTY",
                                "shortDescription": "Television LED, 55 cm diagonal, 1920x1080 Full HD, DVB-T MPEG4 tuner with USB recording and playback",
                                "seoH1": "Hello Kitty Television",
                                "seoTitle": "Hello Kitty TV",
                                "seoMetaDescription": "Hello Kitty TV, LED, 55 cm diagonal, 1920x1080 Full HD.",
                                "link": "http://127.0.0.1:8000/22-sencor-sle-22f46dm4-hello-kitty/",
                                "unit": {
                                    "name": "pcs"
                                },
                                "availability": {
                                    "name": "In stock"
                                },
                                "stockQuantity": 300,
                                "categories": [
                                    {
                                        "name": "Electronics"
                                    },
                                    {
                                        "name": "TV, audio"
                                    }
                                ],
                                "flags": [
                                    {
                                        "name": "TOP",
                                        "rgbColor": "#d6fffa"
                                    },
                                    {
                                        "name": "Action",
                                        "rgbColor": "#f9ffd6"
                                    }
                                ],
                                "price": {
                                    "priceWithVat": "139.96",
                                    "priceWithoutVat": "115.67",
                                    "vatAmount": "24.29"
                                },
                                "images": [
                                    {
                                        "type": null,
                                        "position": null,
                                        "size": "default",
                                        "url": "http://127.0.0.1:8000/content/images/product/default/1.jpg",
                                        "width": 410,
                                        "height": null
                                    },
                                    {
                                        "type": null,
                                        "position": null,
                                        "size": "galleryThumbnail",
                                        "url": "http://127.0.0.1:8000/content/images/product/galleryThumbnail/1.jpg",
                                        "width": null,
                                        "height": 35
                                    },
                                    {
                                        "type": null,
                                        "position": null,
                                        "size": "list",
                                        "url": "http://127.0.0.1:8000/content/images/product/list/1.jpg",
                                        "width": 150,
                                        "height": null
                                    },
                                    {
                                        "type": null,
                                        "position": null,
                                        "size": "thumbnail",
                                        "url": "http://127.0.0.1:8000/content/images/product/thumbnail/1.jpg",
                                        "width": 50,
                                        "height": 40
                                    },
                                    {
                                        "type": null,
                                        "position": null,
                                        "size": "original",
                                        "url": "http://127.0.0.1:8000/content/images/product/original/1.jpg",
                                        "width": null,
                                        "height": null
                                    },
                                    {
                                        "type": null,
                                        "position": null,
                                        "size": "default",
                                        "url": "http://127.0.0.1:8000/content/images/product/default/64.jpg",
                                        "width": 410,
                                        "height": null
                                    },
                                    {
                                        "type": null,
                                        "position": null,
                                        "size": "galleryThumbnail",
                                        "url": "http://127.0.0.1:8000/content/images/product/galleryThumbnail/64.jpg",
                                        "width": null,
                                        "height": 35
                                    },
                                    {
                                        "type": null,
                                        "position": null,
                                        "size": "list",
                                        "url": "http://127.0.0.1:8000/content/images/product/list/64.jpg",
                                        "width": 150,
                                        "height": null
                                    },
                                    {
                                        "type": null,
                                        "position": null,
                                        "size": "thumbnail",
                                        "url": "http://127.0.0.1:8000/content/images/product/thumbnail/64.jpg",
                                        "width": 50,
                                        "height": 40
                                    },
                                    {
                                        "type": null,
                                        "position": null,
                                        "size": "original",
                                        "url": "http://127.0.0.1:8000/content/images/product/original/64.jpg",
                                        "width": null,
                                        "height": null
                                    }
                                ],
                                "brand": {
                                    "name": "Sencor"
                                },
                                "accessories": [
                                    {
                                        "name": "Kabel HDMI A - HDMI A M/M 2m gold-plated connectors High Speed HD"
                                    },
                                    {
                                        "name": "Defender 2.0 SPK-480"
                                    }
                                ],
                                "isSellingDenied": false,
                                "description": "Television LED, 55 cm diagonal, 1920x1080 Full HD, DVB-T MPEG4 tuner with USB recording and playback (DivX, XviD, MP3, WMA, JPEG), HDMI, SCART, VGA, pink execution, energ. Class B",
                                "orderingPriority": null,
                                "parameters": [
                                    {
                                        "uuid": "728f47d2-eef7-48c4-a8a4-c6af3af7ea39",
                                        "name": "HDMI",
                                        "values": [
                                            {
                                                "uuid": "87768319-f601-491d-b34a-8109215bb75b",
                                                "text": "Yes"
                                            }
                                        ]
                                    },
                                    {
                                        "uuid": "238305c0-ea42-427d-a291-b5c9e77a7420",
                                        "name": "Resolution",
                                        "values": [
                                            {
                                                "uuid": "953cfc88-6f5f-4d27-8993-d2a4430778a1",
                                                "text": "1920×1080 (Full HD)"
                                            }
                                        ]
                                    },
                                    {
                                        "uuid": "4c99f0ca-af64-4d1a-85d3-0b3148033301",
                                        "name": "Screen size",
                                        "values": [
                                            {
                                                "uuid": "47c91459-6d05-4758-8154-e9f711602bb0",
                                                "text": "27\""
                                            }
                                        ]
                                    },
                                    {
                                        "uuid": "ac302aa6-64f5-4c5a-9a5e-2dbe87cdb25a",
                                        "name": "Technology",
                                        "values": [
                                            {
                                                "uuid": "b7b4375c-3403-48e2-8c32-5ba48843ad98",
                                                "text": "LED"
                                            }
                                        ]
                                    },
                                    {
                                        "uuid": "924ce936-2632-4fb4-becd-d20837aff51e",
                                        "name": "USB",
                                        "values": [
                                            {
                                                "uuid": "87768319-f601-491d-b34a-8109215bb75b",
                                                "text": "Yes"
                                            }
                                        ]
                                    }
                                ]
                            }
                        }
                    ]
                }
            }
        }
