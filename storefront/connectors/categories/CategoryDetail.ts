export const categoryDetailBody = `
    uuid
    name
    seoH1
    children{
        uuid
        name
        slug
        products{
            totalCount
        }
    }
    products {
        totalCount
        edges {
            node {
                __typename
                slug
                name
                flags {
                    name
                    rgbColor
                }
                images (size: "list") {
                    url
                    width
                    height
                }
                availability {
                    name
                }
                price {
                    priceWithVat
                    priceWithoutVat
                    vatAmount
                    isPriceFrom
                }
                availableStoresCount
                exposedStoresCount
            }
        }
    }
`;
