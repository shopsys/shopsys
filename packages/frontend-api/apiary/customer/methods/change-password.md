### Change password [/graphql{?change_customer_user_password}]

#### POST [POST]

Change password using email address, old password and new password

- Request (application/json)

    - Headers

            :[headers-authorization](../../components/headers/authorization.md)

    - Body

            mutation {
                ChangePassword(input: {
                    email: "no-reply@shopsys.com"
                    oldPassword: "user123"
                    newPassword: "user124"
                }) {
                    firstName
                    lastName
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
