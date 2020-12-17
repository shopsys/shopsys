### Categories search [/graphql{?categories_search}]

#### POST [POST]

Returns searched list of categories that can be paginated using `first`, `last`, `before` and `after` keywords.

- Request (application/json)

    - Attributes
  
        - after
        - first (number) - Default: **10**
        - before
        - last (number)
        - search (required)
      
    - Body

            {
                query {
                    categoriesSearch(search: "tv") {
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
                "categoriesSearch": {
                    "edges": [
                        {
                            "node": {
                                "name": "TV, audio"
                            }
                        }
                    ]
                }
            }
        }
