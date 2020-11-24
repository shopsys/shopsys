### Current customer user personal data [/graphql{?current_customer_user_personal_data}]

#### POST [POST]

Get current customer user personal data

- Request (application/json)

    - Headers

            :[headers-authorization](../../components/headers/authorization.md)

    - Body

            {
                query: currentCustomerUser {
                    firstName,
                    lastName,
                    email
                    telephone
                }
            }

- Response 200 (application/json; charset=utf-8)

        {
            "data": {
                "query": {
                    "firstName": "Jaromír",
                    "lastName": "Jágr",
                    "email": "no-reply@shopsys.com",
                    "telephone": "605000123"
                }
            }
        }
