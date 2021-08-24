export const productDetailBody = `
    uuid
    name
    namePrefix
    nameSuffix
    description
    catalogNumber
    availability {
        name 
        status
    }
    storeAvailabilities {
        storeName
        exposed
        availabilityInformation
        availabilityStatus
    }
    breadcrumb {
        name
        slug
    }
    availableStoresCount
    exposedStoresCount
`;
