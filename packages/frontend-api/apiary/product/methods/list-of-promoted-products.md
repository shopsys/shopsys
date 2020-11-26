### List of promoted products [/graphql{?promotedProducts}]

#### POST [POST]

Returns complete list of promoted products

- Request (application/json)

        {
            promotedProducts {
                uuid
                name
                shortDescription
                link
                unit {
                    name
                }
                isUsingStock
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
            }
        }

- Response 200 (application/json; charset=utf-8)

        {
            "data": {
                "promotedProducts": [
                    {
                        "uuid": "ffc129a3-8c7f-4e9c-8275-175c5386db47",
                        "name": "22\" Sencor SLE 22F46DM4 HELLO KITTY",
                        "shortDescription": "Television LED, 55 cm diagonal, 1920x1080 Full HD, DVB-T MPEG4 tuner with USB recording and playback",
                        "link": "http://127.0.0.1:8000/22-sencor-sle-22f46dm4-hello-kitty/",
                        "unit": {
                            "name": "pcs"
                        },
                        "isUsingStock": true,
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
                        }
                    },
                    {
                        "uuid": "7348a580-3d10-4d8b-b340-93c8566ef57a",
                        "name": "Genius repro SP-M120 black",
                        "shortDescription": "Sleek and compact stereo speakers in combination of black and metallic surface.",
                        "link": "http://127.0.0.1:8000/genius-repro-sp-m120-black/",
                        "unit": {
                            "name": "pcs"
                        },
                        "isUsingStock": true,
                        "availability": {
                            "name": "In stock"
                        },
                        "stockQuantity": 220,
                        "categories": [
                            {
                                "name": "TV, audio"
                            },
                            {
                                "name": "Personal Computers & accessories"
                            }
                        ],
                        "flags": [
                            {
                                "name": "New",
                                "rgbColor": "#efd6ff"
                            }
                        ],
                        "price": {
                            "priceWithVat": "7.96",
                            "priceWithoutVat": "6.58",
                            "vatAmount": "1.38"
                        },
                        "images": [
                            {
                                "type": null,
                                "position": null,
                                "size": "default",
                                "url": "http://127.0.0.1:8000/content/images/product/default/17.jpg",
                                "width": 410,
                                "height": null
                            },
                            {
                                "type": null,
                                "position": null,
                                "size": "galleryThumbnail",
                                "url": "http://127.0.0.1:8000/content/images/product/galleryThumbnail/17.jpg",
                                "width": null,
                                "height": 35
                            },
                            {
                                "type": null,
                                "position": null,
                                "size": "list",
                                "url": "http://127.0.0.1:8000/content/images/product/list/17.jpg",
                                "width": 150,
                                "height": null
                            },
                            {
                                "type": null,
                                "position": null,
                                "size": "thumbnail",
                                "url": "http://127.0.0.1:8000/content/images/product/thumbnail/17.jpg",
                                "width": 50,
                                "height": 40
                            },
                            {
                                "type": null,
                                "position": null,
                                "size": "original",
                                "url": "http://127.0.0.1:8000/content/images/product/original/17.jpg",
                                "width": null,
                                "height": null
                            }
                        ],
                        "brand": {
                            "name": "Genius"
                        }
                    },
                    {
                        "uuid": "926f7569-a2a8-47d0-9647-9937e918c819",
                        "name": "Canon MG3550",
                        "shortDescription": "Features of modern and elegantly prepared MFPs with new wireless capabilities. Function automatic two-sided printing",
                        "link": "http://127.0.0.1:8000/canon-mg3550/",
                        "unit": {
                            "name": "pcs"
                        },
                        "isUsingStock": false,
                        "availability": {
                            "name": "In stock"
                        },
                        "stockQuantity": null,
                        "categories": [
                            {
                                "name": "Printers"
                            }
                        ],
                        "flags": [
                            {
                                "name": "New",
                                "rgbColor": "#efd6ff"
                            },
                            {
                                "name": "TOP",
                                "rgbColor": "#d6fffa"
                            }
                        ],
                        "price": {
                            "priceWithVat": "63.60",
                            "priceWithoutVat": "52.56",
                            "vatAmount": "11.04"
                        },
                        "images": [
                            {
                                "type": null,
                                "position": null,
                                "size": "default",
                                "url": "http://127.0.0.1:8000/content/images/product/default/9.jpg",
                                "width": 410,
                                "height": null
                            },
                            {
                                "type": null,
                                "position": null,
                                "size": "galleryThumbnail",
                                "url": "http://127.0.0.1:8000/content/images/product/galleryThumbnail/9.jpg",
                                "width": null,
                                "height": 35
                            },
                            {
                                "type": null,
                                "position": null,
                                "size": "list",
                                "url": "http://127.0.0.1:8000/content/images/product/list/9.jpg",
                                "width": 150,
                                "height": null
                            },
                            {
                                "type": null,
                                "position": null,
                                "size": "thumbnail",
                                "url": "http://127.0.0.1:8000/content/images/product/thumbnail/9.jpg",
                                "width": 50,
                                "height": 40
                            },
                            {
                                "type": null,
                                "position": null,
                                "size": "original",
                                "url": "http://127.0.0.1:8000/content/images/product/original/9.jpg",
                                "width": null,
                                "height": null
                            }
                        ],
                        "brand": {
                            "name": "Canon"
                        }
                    }
                ]
            }
        }
