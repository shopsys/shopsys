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
