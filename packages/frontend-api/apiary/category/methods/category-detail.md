### Category detail [/graphql{?category_detail}]

#### POST [POST]

Returns category filtered using UUID

- Request (application/json)

    - Attributes

        - uuid

    - Body

            {
                query: category (uuid: "f757f375-2828-489a-9b60-46c608f05b4f") {
                    uuid
                    name
                    seoH1
                    seoTitle
                    seoMetaDescription
                    children {
                        name
                    }
                    parent {
                        name
                    }
                    images {
                        type
                        position
                        size
                        url
                        width
                        height
                    }
                    products (first: 1) {
                        edges {
                            node {
                                name
                            }
                        }
                    }
                }
            }

- Response 200 (application/json; charset=utf-8)

        {
            "data": {
                "query": {
                    "uuid": "f757f375-2828-489a-9b60-46c608f05b4f",
                    "name": "Electronics",
                    "seoH1": "Electronic devices",
                    "seoTitle": "Electronic stuff",
                    "seoMetaDescription": "All kind of electronic devices.",
                    "children": [
                        {
                            "name": "TV, audio"
                        },
                        {
                            "name": "Cameras & Photo"
                        },
                        {
                            "name": "Printers"
                        },
                        {
                            "name": "Personal Computers & accessories"
                        },
                        {
                            "name": "Mobile Phones"
                        },
                        {
                            "name": "Coffee Machines"
                        }
                    ],
                    "parent": {
                        "name": null
                    },
                    "images": [
                        {
                            "type": null,
                            "position": null,
                            "size": "default",
                            "url": "http://127.0.0.1:8000/content/images/category/default/68.jpg",
                            "width": 30,
                            "height": 30
                        },
                        {
                            "type": null,
                            "position": null,
                            "size": "original",
                            "url": "http://127.0.0.1:8000/content/images/category/original/68.jpg",
                            "width": null,
                            "height": null
                        }
                    ],
                    "products": {
                        "edges": [
                            {
                                "node": {
                                    "name": "22\" Sencor SLE 22F46DM4 HELLO KITTY"
                                }
                            }
                        ]
                    }
                }
            }
        }
