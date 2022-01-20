import {
    ListedProductFragmentApi,
    ListedVariantFragmentApi,
    ProductPriceFragmentApi,
    SliderProductFragmentApi,
    usePromotedProductsQueryApi,
} from 'graphql/generated';
import { ListedProductType, ListedVariantType } from 'types/product';
import { mapProductDetailImages, mapStoreAvailabilities } from './ProductDetail';
import { ProductPriceType, SliderProductItemType } from 'types/product';
import { mapImageApiData } from 'connectors/image/Image';
import { useQueryError } from 'hooks/graphQl/UseQueryError';
import { useShopsysSelector } from 'redux/main';

export const mapProductPriceData = (
    price: ProductPriceFragmentApi['price'],
    currencyCode: string,
): ProductPriceType => {
    return {
        ...price,
        priceWithVat: Number.parseFloat(price.priceWithVat),
        priceWithoutVat: Number.parseFloat(price.priceWithoutVat),
        vatAmount: Number.parseFloat(price.vatAmount),
        currencyCode,
    };
};

export const mapListedProductType = (apiData: ListedProductFragmentApi, currencyCode: string): ListedProductType => {
    return {
        ...apiData,
        isMainVariant: apiData.__typename === 'MainVariant',
        slug: apiData.slug,
        availability: apiData.availability.name,
        name: apiData.name,
        price: mapProductPriceApiData(apiData.price, currencyCode),
        image: mapImageApiData(apiData.images),
        catalogNumber: apiData.catalogNumber,
    };
};

export const mapListedVariantType = (apiData: ListedVariantFragmentApi, currencyCode: string): ListedVariantType => {
    return {
        ...apiData,
        slug: apiData.slug,
        availability: apiData.availability.name,
        name: apiData.name,
        price: mapProductPriceApiData(apiData.price, currencyCode),
        images: mapProductDetailImages(apiData.images),
        catalogNumber: apiData.catalogNumber,
        storeAvailabilities: mapStoreAvailabilities(apiData.storeAvailabilities),
    };
};
export const getPromotedProducts = (): SliderProductItemType[] | undefined => {
    const { currencyCode } = useShopsysSelector((state) => state.domain);
    const [{ data, error }] = usePromotedProductsQueryApi();
    useQueryError(error);

    const apiData = data?.promotedProducts;
    if (apiData === undefined) {
        return undefined;
    }

    return mapSliderProductApiData(apiData, currencyCode);
};

export const mapSliderProductApiData = (
    apiData: SliderProductFragmentApi[],
    currencyCode: string,
): SliderProductItemType[] => {
    return apiData.map((apiProduct) => {
        return {
            ...apiProduct,
            name: apiProduct.name,
            image: mapImageApiData(apiProduct.images),
            price: mapProductPriceApiData(apiProduct.price, currencyCode),
            isMainVariant: apiProduct.__typename === 'MainVariant',
            availability: apiProduct.availability.name,
            stockQuantity: apiProduct.stockQuantity,
        };
    });
};

export const mapProductPriceApiData = (
    price: ProductPriceFragmentApi['price'],
    currencyCode: string,
): ProductPriceType => {
    return {
        priceWithVat: Number.parseFloat(price.priceWithVat),
        priceWithoutVat: Number.parseFloat(price.priceWithoutVat),
        vatAmount: Number.parseFloat(price.vatAmount),
        isPriceFrom: price.isPriceFrom,
        currencyCode,
    };
};
