### Brand detail [/graphql{?brand_detail}]

#### POST [POST]

Returns brand filtered using UUID

- Request (application/json)

    - Attributes

        - uuid
        - urlSlug

    - Body

            {
                brand(uuid: "7eef3b9e-4e57-4166-b587-d1fff68987c0") {
                    uuid
                    name
                    description
                    link
                    seoTitle
                    seoMetaDescription
                    seoH1
                    products (first: 5) {
                        edges {
                            node {
                                name
                            }
                        }
                    }
                    images{
                        url,
                        type,
                        size,
                        width,
                        height,
                        position
                    }
                }
            }

- Response 200 (application/json; charset=utf-8)

        {
            "data": {
                "brand": {
                    "uuid": "7eef3b9e-4e57-4166-b587-d1fff68987c0",
                    "name": "Canon",
                    "description": "This is description of brand Canon.",
                    "link": "http://127.0.0.1:8000/canon/",
                    "seoTitle": "Canon SEO Title",
                    "seoMetaDescription": "This is SEO meta description of brand Canon.",
                    "seoH1": "Canon SEO H1",
                    "products": {
                        "edges": [
                            {
                                "node": {
                                    "name": "Canon EH-22L"
                                }
                            },
                            {
                                "node": {
                                    "name": "Canon EH-22M"
                                }
                            },
                            {
                                "node": {
                                    "name": "Canon EOS 700D"
                                }
                            },
                            {
                                "node": {
                                    "name": "Canon EOS 700E"
                                }
                            },
                            {
                                "node": {
                                    "name": "Canon MG3550"
                                }
                            }
                        ]
                    },
                    "images": [
                        {
                            "url": "http://127.0.0.1:8000/content/images/brand/default/80.jpg",
                            "type": null,
                            "size": "default",
                            "width": 300,
                            "height": 200,
                            "position": null
                        },
                        {
                            "url": "http://127.0.0.1:8000/content/images/brand/original/80.jpg",
                            "type": null,
                            "size": "original",
                            "width": null,
                            "height": null,
                            "position": null
                        }
                    ]
                }
            }
        }
