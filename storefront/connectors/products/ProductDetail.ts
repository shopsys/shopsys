import { mapListedVariantType, mapSliderProductApiData } from './Products';
import { mapAvailabilityData, mapStoreAvailabilities } from 'connectors/availability/Availability';
import { mapImageSizesTypeApiData } from 'connectors/image/Image';
import { mapProductPriceData } from 'connectors/price/Prices';
import {
    ImageSizesFragmentApi,
    ListedVariantFragmentApi,
    MainVariantDetailFragmentApi,
    ProductDetailFragmentApi,
    ProductDetailInterfaceFragmentApi,
} from 'graphql/generated';
import { MainVariantDetailType, ProductDetailInterfaceType, ProductDetailType } from 'types/product';

const mapProductDetailInterface = (
    productDetailApiData: ProductDetailInterfaceFragmentApi,
    currencyCode: string,
): ProductDetailInterfaceType => {
    return {
        ...productDetailApiData,
        namePrefix: productDetailApiData.namePrefix !== null ? productDetailApiData.namePrefix : '',
        nameSuffix: productDetailApiData.nameSuffix !== null ? productDetailApiData.nameSuffix : '',
        description: productDetailApiData.description !== null ? productDetailApiData.description : '',
        price: mapProductPriceData(productDetailApiData.price, currencyCode),
        accessories: mapSliderProductApiData(productDetailApiData.accessories, currencyCode),
        images: mapImageSizesTypeApiData(productDetailApiData.images),
        categoryNames: productDetailApiData.categories.map((category) => category.name),
        availability: mapAvailabilityData(productDetailApiData.availability),
    };
};

export const mapProductDetailApiData = (
    productDetailApiData: ProductDetailFragmentApi,
    currencyCode: string,
): ProductDetailType => {
    return {
        ...productDetailApiData,
        ...mapProductDetailInterface(productDetailApiData, currencyCode),
        __typename: productDetailApiData.__typename,
        storeAvailabilities: mapStoreAvailabilities(productDetailApiData.storeAvailabilities),
        shortDescription: productDetailApiData.shortDescription !== null ? productDetailApiData.shortDescription : '',
    };
};

const mapVariantImages = (variants: ListedVariantFragmentApi[]): ImageSizesFragmentApi[] => {
    const mappedImages = [];
    for (const variant of variants) {
        mappedImages.push(...variant.images);
    }
    return mappedImages;
};

export const mapMainVariantDetailApiData = (
    apiData: MainVariantDetailFragmentApi,
    currencyCode: string,
): MainVariantDetailType => {
    return {
        ...mapProductDetailInterface(apiData, currencyCode),
        __typename: 'MainVariant',
        images: mapImageSizesTypeApiData([...apiData.images, ...mapVariantImages(apiData.variants)]),
        variants: apiData.variants.map((variant) => mapListedVariantType(variant, currencyCode)),
    };
};
