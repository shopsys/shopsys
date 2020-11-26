### Refresh tokens [/graphql{?refresh_tokens}]

#### POST [POST]

Refreshes access and refresh tokens

- Request (application/json)

    - Headers

            :[headers-authorization](../../components/headers/authorization.md)

    - Attributes

        - Include InputRefreshToken

    - Body

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
