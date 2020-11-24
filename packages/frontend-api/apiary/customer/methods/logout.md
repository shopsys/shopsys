### Logout [/graphql{?logout}]

#### POST [POST]

Logout user

- Request (application/json)

    - Headers

            :[headers-authorization](../../components/headers/authorization.md)

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
