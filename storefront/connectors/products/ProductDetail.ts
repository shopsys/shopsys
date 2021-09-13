import { mapSliderProductApiData, sliderProductQuery } from './Products';
import { ProductDetailApiType, ProductDetailType } from 'components/Pages/ProductDetail/types';

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
    accessories {
        ${sliderProductQuery}
    }
`;

export const mapProductDetailApiData = (
    productDetailApiData: ProductDetailApiType,
    currencyCode: string,
): ProductDetailType => {
    return {
        ...productDetailApiData,
        accessories: mapSliderProductApiData(productDetailApiData.accessories, currencyCode),
    };
};
