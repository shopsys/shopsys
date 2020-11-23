### List of advert positions [/graphql{?advertPositions}]

#### POST [POST]

Returns list of advert positions.

- Request (application/json)

        {
            advertPositions{
                description
                positionName
            }
        }


- Response 200 (application/json; charset=utf-8)

        {
            "data": {
                "advertPositions": [
                    {
                        "description": "under heading",
                        "positionName": "header"
                    },
                    {
                        "description": "above footer",
                        "positionName": "footer"
                    },
                    {
                        "description": "in category (above the category name)",
                        "positionName": "productList"
                    }
                ]
            }
        }

### List of adverts [/graphql{?adverts}]

#### POST [POST]

Returns list of adverts.
Adverts may be filtered by `positionName` argument.

- Request (application/json)

    - Attributes

        - positionName

    - Body

            {
                adverts (positionName:"footer") {
                    name
                    type
                    uuid
                    positionName
                    ... on AdvertImage {
                        image {
                            url
                            type
                            size
                            width
                            height
                            position
                        }
                        link
                    }
                    ... on AdvertCode {
                        code
                    }
                }
            }

- Response 200 (application/json; charset=utf-8)

        {
            "data": {
                "adverts": [
                    {
                        "name": "Demo advert",
                        "type": "code",
                        "uuid": "2d3dd17b-e04a-4d20-b8eb-f032f8f9c997",
                        "positionName": "footer",
                        "code": "<a href=\"http:\/\/www.shopsys.cz\/\"><img src=\"cool-image-path.jpg\" alt=\"banner\" \/><\/a>"
                    },
                    {
                        "name": "Another demo advert",
                        "type": "image",
                        "uuid": "07471897-513c-4815-aead-36d8bb18849a",
                        "positionName": "footer",
                        "image": [
                            {
                                "url": "http:\/\/127.0.0.1:8000\/content\/images\/noticer\/footer\/103.png",
                                "type": null,
                                "size": "footer",
                                "width": 1160,
                                "height": null,
                                "position": null
                            },
                            {
                                "url": "http:\/\/127.0.0.1:8000\/content\/images\/noticer\/original\/103.png",
                                "type": null,
                                "size": "original",
                                "width": null,
                                "height": null,
                                "position": null
                            }
                        ],
                        "link": "https://shopsys.com",
                    }
                ]
            }
        }
