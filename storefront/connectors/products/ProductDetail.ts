import { mapListedVariantType, mapSliderProductApiData } from './Products';
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
): ProductDetailInterfaceType => {
    return {
        ...productDetailApiData,
        namePrefix: productDetailApiData.namePrefix !== null ? productDetailApiData.namePrefix : '',
        nameSuffix: productDetailApiData.nameSuffix !== null ? productDetailApiData.nameSuffix : '',
        description: productDetailApiData.description !== null ? productDetailApiData.description : '',
        accessories: mapSliderProductApiData(productDetailApiData.accessories),
        categoryNames: productDetailApiData.categories.map((category) => category.name),
    };
};

export const mapProductDetailApiData = (productDetailApiData: ProductDetailFragmentApi): ProductDetailType => {
    return {
        ...productDetailApiData,
        ...mapProductDetailInterface(productDetailApiData),
        __typename: productDetailApiData.__typename,
        shortDescription: productDetailApiData.shortDescription !== null ? productDetailApiData.shortDescription : '',
    };
};

const mapVariantImages = (variants: ListedVariantFragmentApi[]): ImageSizesFragmentApi[] => {
    const mappedImages = [];
    for (const variant of variants) {
        if (variant.image !== null) {
            mappedImages.push(variant.image);
        }
    }
    return mappedImages;
};

export const mapMainVariantDetailApiData = (apiData: MainVariantDetailFragmentApi): MainVariantDetailType => {
    return {
        ...mapProductDetailInterface(apiData),
        __typename: 'MainVariant',
        images: [...apiData.images, ...mapVariantImages(apiData.variants)],
        variants: apiData.variants.map((variant) => mapListedVariantType(variant)),
    };
};
