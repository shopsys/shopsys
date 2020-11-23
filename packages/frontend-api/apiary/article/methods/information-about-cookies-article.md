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
