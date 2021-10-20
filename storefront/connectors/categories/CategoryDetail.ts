export function categoryDetailBody(sortingMode: string, endCursorForPagination: string): string {
    return `
        uuid
        name
        seoH1
        children {
            uuid
            name
            slug
            images (sizes: "default") {
                sizes {
                    url
                    height
                    width
                }
            }
            products{
                totalCount
            }
        }
        linkedCategories {
            uuid
            name
            slug
            images (sizes: "default") {
                sizes {
                    url
                    height
                    width
                }
            }
            products{
                totalCount
            }
        }
        products(orderingMode:${sortingMode} after:"${endCursorForPagination}"){
            totalCount
            pageInfo {
                hasNextPage
                hasPreviousPage
                startCursor
                endCursor
            }
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
                    images (sizes: "list") {
                        sizes {
                            url
                            width
                            height
                        }
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
}
