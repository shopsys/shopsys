export const categoryDetailBody = `
uuid
name
seoH1
children {
    uuid
    name
    slug
    images (size: "default") {
        url
        height
        width
    } 
    products{
        totalCount
    }
}
linkedCategories {
    uuid
    name
    slug
    images (size: "default") {
        url
        height
        width
    }
    products{
        totalCount
    }
}
products{
    totalCount
    edges {
        node {
            __typename
            uuid
            slug
            name
            flags {
                name
                rgbColor
            }
            stockQuantity
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
readyCategorySeoMixLinks {
    name
    slug
}
`;
