import {
    FlagLabelFragmentApi,
    ListedProductFragmentApi,
    ProductPriceFragmentApi,
    SliderProductFragmentApi,
    usePromotedProductsQueryApi,
} from 'graphql/generated';
import { FlagType, ProductPriceType, SliderProductItemType } from 'components/Blocks/Product/types';
import { ListedProductType } from './types';
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
        flags: mapFlagsApiData(apiData.flags),
        isMainVariant: apiData.__typename === 'MainVariant',
        detailSlug: apiData.slug,
        availability: apiData.availability.name,
        name: apiData.name,
        price: mapProductPriceApiData(apiData.price, currencyCode),
        image: mapImageApiData(apiData.images),
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
            detailSlug: apiProduct.slug,
            name: apiProduct.name,
            image: mapImageApiData(apiProduct.images),
            price: mapProductPriceApiData(apiProduct.price, currencyCode),
            isMainVariant: apiProduct.__typename === 'MainVariant',
            availability: apiProduct.availability.name,
            flags: mapFlagsApiData(apiProduct.flags),
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

export const mapFlagsApiData = (flags: FlagLabelFragmentApi[]): FlagType[] => {
    return flags.map((flagApi) => {
        return {
            name: flagApi.name,
            rgbColor: flagApi.rgbColor,
        };
    });
};
