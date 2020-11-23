## Group Category

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

### List of categories [/graphql{?categories}]

#### POST [POST]

Returns complete list of categories

- Request (application/json)

        {
            query: categories {
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
                "query": [
                    {
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
                    },
                    {
                        "uuid": "093460cd-7e31-4847-8ae7-a5d8a3241f47",
                        "name": "Books",
                        "seoH1": null,
                        "seoTitle": null,
                        "seoMetaDescription": null,
                        "children": [],
                        "parent": {
                            "name": null
                        },
                        "images": [
                            {
                                "type": null,
                                "position": null,
                                "size": "default",
                                "url": "http://127.0.0.1:8000/content/images/category/default/75.jpg",
                                "width": 30,
                                "height": 30
                            },
                            {
                                "type": null,
                                "position": null,
                                "size": "original",
                                "url": "http://127.0.0.1:8000/content/images/category/original/75.jpg",
                                "width": null,
                                "height": null
                            }
                        ],
                        "products": {
                            "edges": [
                                {
                                    "node": {
                                        "name": "100 Czech crowns ticket"
                                    }
                                }
                            ]
                        }
                    },
                    {
                        "uuid": "c011e65f-201e-459c-a1b5-47da5942a13d",
                        "name": "Toys",
                        "seoH1": null,
                        "seoTitle": null,
                        "seoMetaDescription": null,
                        "children": [],
                        "parent": {
                            "name": null
                        },
                        "images": [
                            {
                                "type": null,
                                "position": null,
                                "size": "default",
                                "url": "http://127.0.0.1:8000/content/images/category/default/76.jpg",
                                "width": 30,
                                "height": 30
                            },
                            {
                                "type": null,
                                "position": null,
                                "size": "original",
                                "url": "http://127.0.0.1:8000/content/images/category/original/76.jpg",
                                "width": null,
                                "height": null
                            }
                        ],
                        "products": {
                            "edges": []
                        }
                    },
                    {
                        "uuid": "3c8d9534-6432-4726-b068-bab0a47036cd",
                        "name": "Garden tools",
                        "seoH1": null,
                        "seoTitle": null,
                        "seoMetaDescription": null,
                        "children": [],
                        "parent": {
                            "name": null
                        },
                        "images": [
                            {
                                "type": null,
                                "position": null,
                                "size": "default",
                                "url": "http://127.0.0.1:8000/content/images/category/default/77.jpg",
                                "width": 30,
                                "height": 30
                            },
                            {
                                "type": null,
                                "position": null,
                                "size": "original",
                                "url": "http://127.0.0.1:8000/content/images/category/original/77.jpg",
                                "width": null,
                                "height": null
                            }
                        ],
                        "products": {
                            "edges": [
                                {
                                    "node": {
                                        "name": "Cleaner 3in1 stainless steel appliances (4039286078461)"
                                    }
                                }
                            ]
                        }
                    },
                    {
                        "uuid": "2c7c9a49-8626-4e4c-b081-e48d663417a1",
                        "name": "Food",
                        "seoH1": null,
                        "seoTitle": null,
                        "seoMetaDescription": null,
                        "children": [],
                        "parent": {
                            "name": null
                        },
                        "images": [
                            {
                                "type": null,
                                "position": null,
                                "size": "default",
                                "url": "http://127.0.0.1:8000/content/images/category/default/78.jpg",
                                "width": 30,
                                "height": 30
                            },
                            {
                                "type": null,
                                "position": null,
                                "size": "original",
                                "url": "http://127.0.0.1:8000/content/images/category/original/78.jpg",
                                "width": null,
                                "height": null
                            }
                        ],
                        "products": {
                            "edges": [
                                {
                                    "node": {
                                        "name": "Aquila Aquagym non-carbonated spring water"
                                    }
                                }
                            ]
                        }
                    }
                ]
            }
        }
