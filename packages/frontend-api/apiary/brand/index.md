### Brand detail [/graphql{?brand_detail}]

#### POST [POST]

Returns brand filtered using UUID

- Request (application/json)

    - Attributes

        - uuid

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

### List of brands [/graphql{?brands}]

#### POST [POST]

Returns complete list of brands

- Request (application/json)

        {
            brands {
                uuid
                name
                description
                link
                seoTitle
                seoMetaDescription
                seoH1
                products (first: 1) {
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
                "brands": [
                    {
                        "uuid": "deb09a21-3d22-4dfa-a7b0-e7f8be466d90",
                        "name": "A4tech",
                        "description": "This is description of brand A4tech.",
                        "link": "http://127.0.0.1:8000/a4tech/",
                        "seoTitle": "A4tech SEO Title",
                        "seoMetaDescription": "This is SEO meta description of brand A4tech.",
                        "seoH1": "A4tech SEO H1",
                        "products": {
                            "edges": [
                                {
                                    "node": {
                                        "name": "A4tech mouse X-710BK, OSCAR Game, 2000DPI, black,"
                                    }
                                }
                            ]
                        },
                        "images": [
                            {
                                "url": "http://127.0.0.1:8000/content/images/brand/default/84.jpg",
                                "type": null,
                                "size": "default",
                                "width": 300,
                                "height": 200,
                                "position": null
                            },
                            {
                                "url": "http://127.0.0.1:8000/content/images/brand/original/84.jpg",
                                "type": null,
                                "size": "original",
                                "width": null,
                                "height": null,
                                "position": null
                            }
                        ]
                    },

                    // ... 22 more brands hidden here to shorten this list in documentation

                    {
                        "uuid": "94b06eb9-bbbd-4adb-8f9c-46495c9330d6",
                        "name": "Verbatim",
                        "description": "This is description of brand Verbatim.",
                        "link": "http://127.0.0.1:8000/verbatim/",
                        "seoTitle": "Verbatim SEO Title",
                        "seoMetaDescription": "This is SEO meta description of brand Verbatim.",
                        "seoH1": "Verbatim SEO H1",
                        "products": {
                            "edges": [
                                {
                                    "node": {
                                        "name": "CD-R VERBATIM 210MB"
                                    }
                                }
                            ]
                        },
                        "images": [
                            {
                                "url": "http://127.0.0.1:8000/content/images/brand/default/86.jpg",
                                "type": null,
                                "size": "default",
                                "width": 300,
                                "height": 200,
                                "position": null
                            },
                            {
                                "url": "http://127.0.0.1:8000/content/images/brand/original/86.jpg",
                                "type": null,
                                "size": "original",
                                "width": null,
                                "height": null,
                                "position": null
                            }
                        ]
                    }
                ]
            }
        }
