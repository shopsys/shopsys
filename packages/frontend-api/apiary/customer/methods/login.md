### Login [/graphql{?login}]

#### POST [POST]

Login user and return access and refresh tokens

- Request (application/json)

    - Attributes

        - Include InputLogin

    - Body

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
