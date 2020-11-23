### Logout [/graphql{?logout}]

#### POST [POST]

Logout user

- Request (application/json)

    - Headers

            Authorization: Bearer ABCDEF

    - Body

            mutation {
                Logout
            }

- Response 200 (application/json; charset=utf-8)

        {
            "data": {
                "Logout": true
            }
        }
