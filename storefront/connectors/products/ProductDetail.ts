import { mapProductPriceData, mapSliderProductApiData } from './Products';
import { ParameterFragmentApi, ProductDetailFragmentApi, ProductDetailImagesFragmentApi } from 'graphql/generated';
import { ProductDetailImageType, ProductDetailType, ProductParameterType } from 'components/Pages/ProductDetail/types';

export const mapProductDetailApiData = (
    productDetailApiData: ProductDetailFragmentApi,
    currencyCode: string,
): ProductDetailType => {
    return {
        ...productDetailApiData,
        __typename:
            productDetailApiData?.__typename !== undefined && productDetailApiData.__typename !== null
                ? productDetailApiData.__typename
                : 'RegularProduct',
        availability: {
            name: productDetailApiData.availability.name,
            status: productDetailApiData.availability.status === 'in-stock' ? 'in-stock' : 'out-of-stock',
        },
        storeAvailabilities: productDetailApiData.storeAvailabilities.map((storeAvailabilityApiData) => {
            return {
                ...storeAvailabilityApiData,
                availabilityStatus:
                    storeAvailabilityApiData.availabilityStatus === 'in-stock' ? 'in-stock' : 'out-of-stock',
            };
        }),
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
        price: mapProductPriceData(productDetailApiData.price, currencyCode),
        accessories:
            productDetailApiData.accessories !== undefined && productDetailApiData.accessories !== null
                ? mapSliderProductApiData(productDetailApiData.accessories, currencyCode)
                : [],
        parameters: mapParametersApiData(productDetailApiData.parameters),
        images: mapProductDetailImages(productDetailApiData.images),
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

const mapProductDetailImages = (images: ProductDetailImagesFragmentApi['images']) => {
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
