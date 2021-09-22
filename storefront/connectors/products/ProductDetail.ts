import { mapProductPriceData, mapSliderProductApiData, sliderProductQuery } from './Products';
import { ProductDetailApiType, ProductDetailType } from 'components/Pages/ProductDetail/types';

export const productDetailBody = `
    uuid
    name
    namePrefix
    nameSuffix
    description
    catalogNumber
    stockQuantity
    price {
        priceWithVat
        priceWithoutVat
        vatAmount
        isPriceFrom
    }
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
        price: mapProductPriceData(productDetailApiData.price, currencyCode),
        accessories: mapSliderProductApiData(productDetailApiData.accessories, currencyCode),
    };
};
