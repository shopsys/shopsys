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

### Change password [/graphql{?change_customer_user_password}]

#### POST [POST]

Change password using email address, old password and new password

- Request (application/json)

    - Headers

            Authorization: Bearer --ACCESS-TOKEN--

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

### Current customer user personal data [/graphql{?current_customer_user_personal_data}]

#### POST [POST]

Get current customer user personal data

- Request (application/json)

    - Headers

            Authorization: Bearer --ACCESS-TOKEN--

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

### Login [/graphql{?login}]

#### POST [POST]

Login user and return access and refresh tokens

- Request (application/json)

        mutation {
            Login(input: {
                email: "no-reply@shopsys.com"
                password: "user123"
            }) {
                accessToken
                refreshToken
            }
        }

- Response 200 (application/json; charset=utf-8)

        {
            "data": {
                "Login": {
                    "accessToken": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8xMjcuMC4wLjE6ODAwMCIsImF1ZCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwIiwiaWF0IjoxNTg2NDM2NTg3LCJleHAiOjE1ODY0NDAxODcsInV1aWQiOiI0ZDAwNTEyZi1lNDkxLTRmMzEtYjBjYi04ZTViOGU0ODQ5ZDEiLCJlbWFpbCI6Im5vLXJlcGx5QHNob3BzeXMuY29tIiwiZnVsbE5hbWUiOiJTaG9wc3lzIiwicm9sZXMiOlsiUk9MRV9MT0dHRURfQ1VTVE9NRVIiXX0.cTOk-HrBeLh6DsmFY49Dg7VXACmivLi-6MJU5C4XW8o",
                    "refreshToken": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8xMjcuMC4wLjE6ODAwMCIsImF1ZCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwIiwiaWF0IjoxNTg2NDM2NTg3LCJleHAiOjE1ODc2NDYxODcsInV1aWQiOiI0ZDAwNTEyZi1lNDkxLTRmMzEtYjBjYi04ZTViOGU0ODQ5ZDEiLCJzZWNyZXRDaGFpbiI6ImIyMGZkMTNkODIyNTVhNzdmYzJjYWM4OTA1YzU1MWQxZjNjYjc3ODkifQ._mBpd4yQZ1bF4aC6YY3C1BDI1mrH2hV_w0Yh9mKu_i0"
                }
            }
        }

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

### Refresh tokens [/graphql{?refresh_tokens}]

#### POST [POST]

Refreshes access and refresh tokens

- Request (application/json)

        mutation {
            RefreshTokens(input: {
                refreshToken: "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8xMjcuMC4wLjE6ODAwMCIsImF1ZCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwIiwiaWF0IjoxNTg2NDM2NTg3LCJleHAiOjE1ODc2NDYxODcsInV1aWQiOiI0ZDAwNTEyZi1lNDkxLTRmMzEtYjBjYi04ZTViOGU0ODQ5ZDEiLCJzZWNyZXRDaGFpbiI6ImIyMGZkMTNkODIyNTVhNzdmYzJjYWM4OTA1YzU1MWQxZjNjYjc3ODkifQ._mBpd4yQZ1bF4aC6YY3C1BDI1mrH2hV_w0Yh9mKu_i0"
            }) {
                accessToken
                refreshToken
            }
        }

- Response 200 (application/json; charset=utf-8)

        {
            "data": {
                "RefreshTokens": {
                    "accessToken": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8xMjcuMC4wLjE6ODAwMCIsImF1ZCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwIiwiaWF0IjoxNTg2ODU1NzcyLCJleHAiOjE1ODY4NTkzNzIsInV1aWQiOiI0ZDAwNTEyZi1lNDkxLTRmMzEtYjBjYi04ZTViOGU0ODQ5ZDEiLCJlbWFpbCI6Im5vLXJlcGx5QHNob3BzeXMuY29tIiwiZnVsbE5hbWUiOiJTaG9wc3lzIiwicm9sZXMiOlsiUk9MRV9MT0dHRURfQ1VTVE9NRVIiXX0.vrCl5MDKGfrigYBDDq2b9gGKyTKPDVVreBmGrWIZnYZ6LrYBgiLlvhilcmeXI1OSD3ZWnu2QC1_SDOR3971FoM1Pveo9q0H_7bMF7ubIBUXAp8BThk5sh0GnDqMJVdg84zcTbwQY4-3OVfNoBhqRdBkm-OxaG5t5etOHRa1W0QA0NAm_I48yCn70qGmIM-aHXVTQJq_UGKsh1pRjBDse5Y1H0yFSb65VjWkV3O3FThEnDgJnU2jL4J5nIDUX6qfd1ejU0HatE-CB1f9P4HDVTbuqS5Xc1MbHCVfHxqmrut8FfZ--CrPL_dC0i14a7moxJb-tyh82npThFy7LBkw1Ww",
                    "refreshToken": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8xMjcuMC4wLjE6ODAwMCIsImF1ZCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwIiwiaWF0IjoxNTg2ODU1NzcyLCJleHAiOjE1ODgwNjUzNzIsInV1aWQiOiI0ZDAwNTEyZi1lNDkxLTRmMzEtYjBjYi04ZTViOGU0ODQ5ZDEiLCJzZWNyZXRDaGFpbiI6ImMxZjRjMzAzZTExZTg2MTM2NGIxMTIwY2I0ODBiZjFjMjVmNDU3ZGEifQ.KSsGeI0LFeeJ94t-wU0sWO9_AFmj3djr9bLRi_9gxJ7zy6UxIzKSqDoGzg9LfxbYAofb9MEYQ8ZmCOURhoTBVyTOIMPlFvhFbD_tEQUBakE4Rz3tZFOfszuGQ_bDp6qIG50O-xCFdu3SyupSIR4nJ6fjPG_5j6SZH0ETw1iPqwHyH5_sz7USBHf74FtcuVGNXYIqvvWbVJ6n0Fi-kEI2RSvdkot_6bsS28Zi5zyoovMHThPCMU4DBBmqiY0cSqtEdPdFoN_ALdBGW3yJu7gM9oPaCmqfqapVuywIn5i17cocmN2NmHSTzR0DmTFMtOrCVipM7tlHVImq8dlovGSffQ"
                }
            }
        }

### Register a new customer user [/graphql{?register}]

#### POST [POST]

Register and login a new customer user and return access and refresh tokens

- Request (application/json)

        mutation {
            Register(input: {
                firstName: "John"
                lastName: "Doe"
                email: "new-no-reply@shopsys.com"
                password: "user123"
            }) {
                accessToken,
                refreshToken
            }
        }

- Response 200 (application/json; charset=utf-8)

        {
            "data": {
                "Register": {
                    "accessToken": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9hcHBsaWNhdGlvbjo4MDAwIiwiYXVkIjoiaHR0cDpcL1wvYXBwbGljYXRpb246ODAwMCIsImlhdCI6MTYwNDU5MTkzNywiZXhwIjoxNjA0NTkyMjM3LCJkZXZpY2VJZCI6IjQwZGNjNmE0LTllZDAtNGM1YS04ZDc4LWMxOGMwMTllYWY3MCIsInV1aWQiOiJlZWZmYTM0OC1mYTg0LTQyZmQtOTAwMy1kYTkzOWZlODllYmIiLCJlbWFpbCI6Im5ldy1uby1yZXBseUBzaG9wc3lzLmNvbSIsImZ1bGxOYW1lIjoiRG9lIEpvaG4iLCJyb2xlcyI6WyJST0xFX0xPR0dFRF9DVVNUT01FUiJdfQ.MWNJIiI2C_DNn4D3-X99TYZZpOK6_QrylOnihMy7IB0l_B3BSSFJ-A34PJjdYtdp1D1PN8IUrFeykOhS3EWB_Zmdp7oMwEk5GK13-K-C0qva1H3R8qeBw-vMUp-LLv4jXpb8yOxQGojY2CJtPslNzFDTEqsyWY1qvnEDzTkzUHpJKAXeUDSvcBOcirZA0fUATeDSSzgiOX_RFYyfkcmgr39kQvVfMTP67oTSs6Re0X0cXURvh67JZwh719YMZ_plx88LB77s3liF8fqc4uIWNwL6LW8lVsN-k3U6Pd1I4QhYA7-9FbRxS47ashFSglhyBer0b5f8AYslZdZyiJ8wzw",
                    "refreshToken": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9hcHBsaWNhdGlvbjo4MDAwIiwiYXVkIjoiaHR0cDpcL1wvYXBwbGljYXRpb246ODAwMCIsImlhdCI6MTYwNDU5MTkzNywiZXhwIjoxNjA1ODAxNTM3LCJ1dWlkIjoiZWVmZmEzNDgtZmE4NC00MmZkLTkwMDMtZGE5MzlmZTg5ZWJiIiwic2VjcmV0Q2hhaW4iOiI3NzQ0ZTQ5NTZhZDI3ZjFiMDhlZjdhNzhhNGFkYWMyYjIyMWM5MjAyIn0.AaDNjYpj2sTP_zak3HdGtbkS7F3Di0gloMVIi3pgNMkL7_MGbPBmMbTYQNcIQQyYoslup2FGd6Y4mn6bx5qXSC_iaSLuTfxyzmXaIOVrSssqPCtyQO9OPO8MkUFI-a7Uvn6Idia1djbGEY0kh0tWHy6JNqN6Tui5Ef-VnkUfzRNtW98Jv6LVd3iGFRqmdv5a2nI-H2-cNYEe5LzuiYypnZnFTOgY4-_wvCCkWPmuWx37XlUP-8WW5GbQemN9c1-bffJpNMwuxQixQTKwMVL5XCrWG95v82-Q_KKVQ_jHJCpiGhyfQUEsXChoEkNVCABD3uZZNzsMQH79L8LAf7bN8g"
                }
            }
        }
