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
