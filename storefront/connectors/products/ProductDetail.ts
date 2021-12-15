import {
    ListedVariantFragmentApi,
    MainVariantDetailFragmentApi,
    ParameterFragmentApi,
    ProductDetailFragmentApi,
    ProductDetailImagesFragmentApi,
    StoreAvailabilityFragmentApi,
} from 'graphql/generated';
import { mapListedVariantType, mapProductPriceData, mapSliderProductApiData } from './Products';
import { ProductDetailImageType, ProductDetailType, ProductParameterType, StoreAvailability } from 'types/product';
import { MainVariantDetailType } from 'types/product';
import { mapStoreDetailApiData } from 'connectors/stores/StoreDetail';

export const mapProductDetailApiData = (
    productDetailApiData: ProductDetailFragmentApi,
    currencyCode: string,
): ProductDetailType => {
    return {
        ...productDetailApiData,
        __typename: productDetailApiData.__typename !== undefined ? productDetailApiData.__typename : 'RegularProduct',
        availability: {
            name: productDetailApiData.availability.name,
            status: productDetailApiData.availability.status === 'in-stock' ? 'in-stock' : 'out-of-stock',
        },
        storeAvailabilities: mapStoreAvailabilities(productDetailApiData.storeAvailabilities),
        namePrefix:
            productDetailApiData.namePrefix !== undefined && productDetailApiData.namePrefix !== null
                ? productDetailApiData.namePrefix
                : '',
        nameSuffix:
            productDetailApiData.nameSuffix !== undefined && productDetailApiData.nameSuffix !== null
                ? productDetailApiData.nameSuffix
                : '',
        description:
            productDetailApiData.description !== undefined && productDetailApiData.description !== null
                ? productDetailApiData.description
                : '',
        shortDescription:
            productDetailApiData.shortDescription !== undefined && productDetailApiData.shortDescription !== null
                ? productDetailApiData.shortDescription
                : '',
        price: mapProductPriceData(productDetailApiData.price, currencyCode),
        accessories: mapSliderProductApiData(productDetailApiData.accessories, currencyCode),
        parameters: mapParametersApiData(productDetailApiData.parameters),
        images: mapProductDetailImages(productDetailApiData.images),
    };
};

export const mapStoreAvailabilities = (apiData: StoreAvailabilityFragmentApi[]): StoreAvailability[] => {
    const mappedStoreAvailabilities = [];

    for (const storeAvailabilityApiData of apiData) {
        if (storeAvailabilityApiData.store !== null && storeAvailabilityApiData.store !== undefined) {
            mappedStoreAvailabilities.push({
                ...storeAvailabilityApiData,
                availabilityStatus:
                    storeAvailabilityApiData.availabilityStatus === 'in-stock'
                        ? ('in-stock' as const)
                        : ('out-of-stock' as const),
                store: mapStoreDetailApiData(storeAvailabilityApiData.store),
            });
        }
    }

    return mappedStoreAvailabilities;
};

const mapVariantImages = (variants: ListedVariantFragmentApi[]): ProductDetailImagesFragmentApi['images'] => {
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
        ...apiData,
        __typename: 'MainVariant',
        namePrefix: apiData.namePrefix !== undefined && apiData.namePrefix !== null ? apiData.namePrefix : '',
        nameSuffix: apiData.nameSuffix !== undefined && apiData.nameSuffix !== null ? apiData.nameSuffix : '',
        description: apiData.description !== undefined && apiData.description !== null ? apiData.description : '',
        price: mapProductPriceData(apiData.price, currencyCode),
        accessories: mapSliderProductApiData(apiData.accessories, currencyCode),
        parameters: mapParametersApiData(apiData.parameters),
        images: mapProductDetailImages([...apiData.images, ...mapVariantImages(apiData.variants)]),
        variants: apiData.variants.map((variant) => mapListedVariantType(variant, currencyCode)),
    };
};

const mapParametersApiData = (apiData: ParameterFragmentApi[]): ProductParameterType[] => {
    const mappedParameters = [];
    for (const parameterApiData of apiData) {
        mappedParameters.push({
            ...parameterApiData,
        });
    }

    return mappedParameters;
};

export const mapProductDetailImages = (images: ProductDetailImagesFragmentApi['images']): ProductDetailImageType[] => {
    const mappedImages = [];
    for (const image of images) {
        const mappedImage: ProductDetailImageType = {};
        for (const imageSize of image.sizes) {
            mappedImage[imageSize.size] = {
                ...imageSize,
                width: imageSize.width !== undefined && imageSize.width !== null ? imageSize.width : 0,
                height: imageSize.height !== undefined && imageSize.height !== null ? imageSize.height : 0,
            };
        }
        mappedImages.push(mappedImage);
    }
    return mappedImages;
};
