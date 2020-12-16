### Product filter options [/graphql{?product_filter_options}]

#### POST [POST]

Product filter options are part of the product connection ([connections article](https://relay.dev/graphql/connections.htm)).
It includes all filter options for the current query.
Filter options vary depending on the query used for selecting products.
The most filter options is available for products selected via category query.

- Request (application/json)

    - Body

            {
                query {
                    category (uuid: "33269603-5a86-4513-8fca-d5ce39d9a8ed") {
                        products {
                            productFilterOptions {
                                flags {
                                    flag {
                                        name
                                        uuid
                                    }
                                    count
                                    isAbsolute
                                },
                                brands {
                                    brand {
                                        name
                                    }
                                    count
                                    isAbsolute
                                },
                                inStock,
                                minimalPrice,
                                maximalPrice,
                                parameters {
                                    name
                                    uuid
                                    values {
                                        text
                                        uuid
                                        count
                                        isAbsolute
                                    }
                                }
                            }
                        }
                    }
                }
            }

- Response 200 (application/json; charset=utf-8)

        {
            "data": {
                "category": {
                    "products": {
                        "productFilterOptions": {
                            "flags": [
                                {
                                    "flag": {
                                        "name": "Action",
                                        "uuid": "9d194ad9-eb39-46ea-ae26-f8644dbb8e96"
                                    },
                                    "count": 1,
                                    "isAbsolute": true
                                },
                                {
                                    "flag": {
                                        "name": "TOP",
                                        "uuid": "0ad89389-de37-4584-88e4-6ff356955671"
                                    },
                                    "count": 2,
                                    "isAbsolute": true
                                }
                            ],
                            "brands": [
                                {
                                    "brand": {
                                        "name": "A4tech"
                                    },
                                    "count": 1,
                                    "isAbsolute": true
                                },
                                {
                                    "brand": {
                                        "name": "LG"
                                    },
                                    "count": 1,
                                    "isAbsolute": true
                                },
                                {
                                    "brand": {
                                        "name": "Philips"
                                    },
                                    "count": 1,
                                    "isAbsolute": true
                                },
                                {
                                    "brand": {
                                        "name": "Sencor"
                                    },
                                    "count": 1,
                                    "isAbsolute": true
                                }
                            ],
                            "inStock": 4,
                            "minimalPrice": "12.750000",
                            "maximalPrice": "863.600000",
                            "parameters": [
                                {
                                    "name": "Ergonomics",
                                    "uuid": "20a25642-987f-4355-b772-b19ae8540e60",
                                    "values": [
                                        {
                                            "text": "Right-handed",
                                            "uuid": "0377c8ea-c578-43b8-b03d-95b3fbd75270",
                                            "count": 1,
                                            "isAbsolute": true
                                        }
                                    ]
                                },
                                {
                                    "name": "Gaming mouse",
                                    "uuid": "189c3da0-e643-484f-a8e9-8519be0a9969",
                                    "values": [
                                        {
                                            "text": "Yes",
                                            "uuid": "a4a21553-9e73-4891-9876-9ce5e03b3e19",
                                            "count": 1,
                                            "isAbsolute": true
                                        }
                                    ]
                                },
                                {
                                    "name": "HDMI",
                                    "uuid": "63e04736-a6ec-4c88-88b2-ca0238bfb1be",
                                    "values": [
                                        {
                                            "text": "No",
                                            "uuid": "5339a5c6-39e5-4945-93f7-17ca838da617",
                                            "count": 2,
                                            "isAbsolute": true
                                        },
                                        {
                                            "text": "Yes",
                                            "uuid": "a4a21553-9e73-4891-9876-9ce5e03b3e19",
                                            "count": 1,
                                            "isAbsolute": true
                                        }
                                    ]
                                },
                                {
                                    "name": "Number of buttons",
                                    "uuid": "a277f155-b990-440a-9e5f-8d46a6c529ff",
                                    "values": [
                                        {
                                            "text": "5",
                                            "uuid": "1fbe384a-6244-4fbf-a0e6-2d3c96402db3",
                                            "count": 1,
                                            "isAbsolute": true
                                        }
                                    ]
                                },
                                {
                                    "name": "Resolution",
                                    "uuid": "0d8cb47b-c492-4947-8dbb-d909a347d9d0",
                                    "values": [
                                        {
                                            "text": "1920×1080 (Full HD)",
                                            "uuid": "dde8a1d1-5d07-43e5-a889-8fa1b8fa613a",
                                            "count": 3,
                                            "isAbsolute": true
                                        }
                                    ]
                                },
                                {
                                    "name": "Screen size",
                                    "uuid": "4a87eb44-8b20-453c-83fc-4b0004976179",
                                    "values": [
                                        {
                                            "text": "27\"",
                                            "uuid": "f5968971-46a4-4a11-8670-6f005b328b5e",
                                            "count": 1,
                                            "isAbsolute": true
                                        },
                                        {
                                            "text": "30\"",
                                            "uuid": "8b62a410-7dda-41aa-8bba-44d8c614c8c6",
                                            "count": 1,
                                            "isAbsolute": true
                                        },
                                        {
                                            "text": "47\"",
                                            "uuid": "14027d5b-9618-41aa-964f-56f193e2ecd3",
                                            "count": 1,
                                            "isAbsolute": true
                                        }
                                    ]
                                },
                                {
                                    "name": "Supported OS",
                                    "uuid": "80d93212-caca-47ef-bf90-d5d77b9997e6",
                                    "values": [
                                        {
                                            "text": "Windows 2000/XP/Vista/7",
                                            "uuid": "a20bf82d-0924-4906-952e-3a8a31c6d90a",
                                            "count": 1,
                                            "isAbsolute": true
                                        }
                                    ]
                                },
                                {
                                    "name": "Technology",
                                    "uuid": "be9ed73a-1bf5-4528-994c-1475d36bb500",
                                    "values": [
                                        {
                                            "text": "LED",
                                            "uuid": "d7b1bbcd-e8f6-437d-8b69-0a967631fa42",
                                            "count": 3,
                                            "isAbsolute": true
                                        }
                                    ]
                                },
                                {
                                    "name": "USB",
                                    "uuid": "113fde5b-7856-4a1e-bbe9-adbb44a3a1ac",
                                    "values": [
                                        {
                                            "text": "Yes",
                                            "uuid": "a4a21553-9e73-4891-9876-9ce5e03b3e19",
                                            "count": 3,
                                            "isAbsolute": true
                                        }
                                    ]
                                }
                            ]
                        }
                    }
                }
            }
        }
