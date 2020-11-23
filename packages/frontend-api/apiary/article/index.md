## Group Article

### Article detail [/graphql{?article}]

#### POST [POST]

Returns article filtered using UUID

- Request (application/json)

    - Attributes

        - uuid

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
                    "placement": "topMenu",
                    "name": "Shopping guide",
                    "text": "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus felis nisi, tincidunt sollicitudin augue eu, laoreet blandit sem. Donec rutrum augue a elit imperdiet, eu vehicula tortor porta. Vivamus pulvinar sem non auctor dictum. Morbi eleifend semper enim, eu faucibus tortor posuere vitae. Donec tincidunt ipsum ullamcorper nisi accumsan tincidunt. Aenean sed velit massa. Nullam interdum eget est ut convallis. Vestibulum et mauris condimentum, rutrum sem congue, suscipit arcu.\\nSed tristique vehicula ipsum, ut vulputate tortor feugiat eu. Vivamus convallis quam vulputate faucibus facilisis. Curabitur tincidunt pulvinar leo, eu dapibus augue lacinia a. Fusce sed tincidunt nunc. Morbi a nisi a odio pharetra laoreet nec eget quam. In in nisl tortor. Ut fringilla vitae lectus eu venenatis. Nullam interdum sed odio a posuere. Fusce pellentesque dui vel tortor blandit, a dictum nunc congue.",
                    "seoH1": null,
                    "seoTitle": null,
                    "seoMetaDescription": null
                }
            }
        }

### Information about cookies article [/graphql{?cookiesArticle}]

#### POST [POST]

Returns article detail

- Request (application/json)

        {
            cookiesArticle {
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
                "cookiesArticle": {
                    "uuid": "7d1db4e6-2ac0-4cdf-9b1f-0808cc8895b1",
                    "placement": "none",
                    "name": "Information about cookies",
                    "text": "Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.",
                    "seoH1": null,
                    "seoTitle": null,
                    "seoMetaDescription": null
                }
            }
        }

### List of articles [/graphql{?articles}]

#### POST [POST]

Returns list of articles that can be paginated using `first`, `last`, `before` and `after` keywords.
Articles may be filtered by `placement` argument.
By default this list is limited to first 10 articles.
You can read more about Connection specification in [connections article](https://relay.dev/graphql/connections.htm).

- Request (application/json)

    - Attributes

        - after
        - first (number)
        - before
        - last (number)
        - placement

    - Body

            {
                articles (first:2, placement:"topMenu") {
                    edges{
                        node{
                            uuid
                            placement
                            name
                            text
                            seoH1
                            seoTitle
                            seoMetaDescription
                        }
                    }
                }
            }

- Response 200 (application/json; charset=utf-8)

        {
            "data": {
                "articles": {
                    "edges": [
                        {
                            "node": {
                                "uuid": "49998b14-d579-4fd6-b49e-5842f1753aeb",
                                "placement": "topMenu",
                                "name": "News",
                                "text": "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus felis nisi, tincidunt sollicitudin augue eu, laoreet blandit sem. Donec rutrum augue a elit imperdiet, eu vehicula tortor porta. Vivamus pulvinar sem non auctor dictum. Morbi eleifend semper enim, eu faucibus tortor posuere vitae. Donec tincidunt ipsum ullamcorper nisi accumsan tincidunt. Aenean sed velit massa. Nullam interdum eget est ut convallis. Vestibulum et mauris condimentum, rutrum sem congue, suscipit arcu.\\nSed tristique vehicula ipsum, ut vulputate tortor feugiat eu. Vivamus convallis quam vulputate faucibus facilisis. Curabitur tincidunt pulvinar leo, eu dapibus augue lacinia a. Fusce sed tincidunt nunc. Morbi a nisi a odio pharetra laoreet nec eget quam. In in nisl tortor. Ut fringilla vitae lectus eu venenatis. Nullam interdum sed odio a posuere. Fusce pellentesque dui vel tortor blandit, a dictum nunc congue.",
                                "seoH1": null,
                                "seoTitle": null,
                                "seoMetaDescription": null
                            }
                        },
                        {
                            "node": {
                                "uuid": "37e37351-f607-4e60-b66c-bc8ec9a87491",
                                "placement": "topMenu",
                                "name": "Shopping guide",
                                "text": "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus felis nisi, tincidunt sollicitudin augue eu, laoreet blandit sem. Donec rutrum augue a elit imperdiet, eu vehicula tortor porta. Vivamus pulvinar sem non auctor dictum. Morbi eleifend semper enim, eu faucibus tortor posuere vitae. Donec tincidunt ipsum ullamcorper nisi accumsan tincidunt. Aenean sed velit massa. Nullam interdum eget est ut convallis. Vestibulum et mauris condimentum, rutrum sem congue, suscipit arcu.\\nSed tristique vehicula ipsum, ut vulputate tortor feugiat eu. Vivamus convallis quam vulputate faucibus facilisis. Curabitur tincidunt pulvinar leo, eu dapibus augue lacinia a. Fusce sed tincidunt nunc. Morbi a nisi a odio pharetra laoreet nec eget quam. In in nisl tortor. Ut fringilla vitae lectus eu venenatis. Nullam interdum sed odio a posuere. Fusce pellentesque dui vel tortor blandit, a dictum nunc congue.",
                                "seoH1": null,
                                "seoTitle": null,
                                "seoMetaDescription": null
                            }
                        }
                    ]
                }
            }
        }

### Privacy policy article [/graphql{?privacyPolicyArticle}]

#### POST [POST]

Returns article detail

- Request (application/json)

        {
            privacyPolicyArticle {
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
                "privacyPolicyArticle": {
                    "uuid": "6ab6f711-20a5-40da-8b15-adea8494886e",
                    "placement": "none",
                    "name": "Privacy policy",
                    "text": "Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.",
                    "seoH1": null,
                    "seoTitle": null,
                    "seoMetaDescription": null
                }
            }
        }

### Terms and conditions article [/graphql{?termsAndConditionsArticle}]

#### POST [POST]

Returns article detail

- Request (application/json)

        {
            termsAndConditionsArticle {
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
                "termsAndConditionsArticle": {
                    "uuid": "bd1b23ca-9812-437c-8fd2-14440e94b8a0",
                    "placement": "footer",
                    "name": "Terms and conditions",
                    "text": "Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.",
                    "seoH1": null,
                    "seoTitle": null,
                    "seoMetaDescription": null
                }
            }
        }
