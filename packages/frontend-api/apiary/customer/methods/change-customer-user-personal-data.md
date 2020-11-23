### Change customer user personal data [/graphql{?change_customer_user_personal_data}]

#### POST [POST]

Returns customer user new personal data

- Request (application/json)

    - Headers

            Authorization: Bearer --ACCESS-TOKEN--

    - Body

            mutation {
                ChangePersonalData(input: {
                    telephone: "123456321"
                    firstName: "John"
                    lastName: "Doe"
                }) {
                    firstName
                    lastName,
                    telephone,
                    email
                }
            }

- Response 200 (application/json; charset=utf-8)

        {
            "data": {
                "ChangePersonalData": {
                    "firstName": "John",
                    "lastName": "Doe",
                    "telephone": "123456321",
                    "email": "no-reply@shopsys.com"
                }
            }
        }
