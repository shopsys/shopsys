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
