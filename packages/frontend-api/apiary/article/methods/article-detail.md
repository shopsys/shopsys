### Article detail [/graphql{?article}]

#### POST [POST]

Returns article filtered using UUID

- Request (application/json)

    - Attributes

        - uuid
        - urlSlug

    - Body

            {
                article (uuid:"37e37351-f607-4e60-b66c-bc8ec9a87491") {
                    uuid
                    placement
                    name
                    text
                    seoH1
                    seoTitle
                    seoMetaDescription
                }
            }


- Response 200 (application/json; charset=utf-8)

        {
            "data": {
                "article": {
                    "uuid": "37e37351-f607-4e60-b66c-bc8ec9a87491",
                    "placement": "none",
                    "name": "Shopping guide",
                    "text": "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus felis nisi, tincidunt sollicitudin augue eu, laoreet blandit sem. Donec rutrum augue a elit imperdiet, eu vehicula tortor porta. Vivamus pulvinar sem non auctor dictum. Morbi eleifend semper enim, eu faucibus tortor posuere vitae. Donec tincidunt ipsum ullamcorper nisi accumsan tincidunt. Aenean sed velit massa. Nullam interdum eget est ut convallis. Vestibulum et mauris condimentum, rutrum sem congue, suscipit arcu.\\nSed tristique vehicula ipsum, ut vulputate tortor feugiat eu. Vivamus convallis quam vulputate faucibus facilisis. Curabitur tincidunt pulvinar leo, eu dapibus augue lacinia a. Fusce sed tincidunt nunc. Morbi a nisi a odio pharetra laoreet nec eget quam. In in nisl tortor. Ut fringilla vitae lectus eu venenatis. Nullam interdum sed odio a posuere. Fusce pellentesque dui vel tortor blandit, a dictum nunc congue.",
                    "seoH1": null,
                    "seoTitle": null,
                    "seoMetaDescription": null
                }
            }
        }
