import { getFirstImageSize, mapImageSizesTypeApiData } from 'connectors/image/Image';
import {
    ListedProductFragmentApi,
    ListedVariantFragmentApi,
    SliderProductFragmentApi,
    usePromotedProductsQueryApi,
} from 'graphql/generated';
import { ListedProductType, ListedVariantType, SliderProductItemType } from 'types/product';
import { mapProductPriceData } from 'connectors/price/Prices';
import { mapStoreAvailabilities } from 'connectors/availability/Availability';
import { useQueryError } from 'hooks/graphQl/UseQueryError';
import { useShopsysSelector } from 'redux/main';

export const mapListedProductType = (apiData: ListedProductFragmentApi, currencyCode: string): ListedProductType => {
    return {
        ...apiData,
        isMainVariant: apiData.__typename === 'MainVariant',
        slug: apiData.slug,
        availability: apiData.availability.name,
        name: apiData.name,
        price: mapProductPriceData(apiData.price, currencyCode),
        image: getFirstImageSize(apiData.images),
        catalogNumber: apiData.catalogNumber,
    };
};

export const mapListedVariantType = (apiData: ListedVariantFragmentApi, currencyCode: string): ListedVariantType => {
    return {
        ...apiData,
        slug: apiData.slug,
        availability: apiData.availability.name,
        name: apiData.name,
        price: mapProductPriceData(apiData.price, currencyCode),
        images: mapImageSizesTypeApiData(apiData.images),
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
            image: getFirstImageSize(apiProduct.images),
            price: mapProductPriceData(apiProduct.price, currencyCode),
            isMainVariant: apiProduct.__typename === 'MainVariant',
            availability: apiProduct.availability.name,
            stockQuantity: apiProduct.stockQuantity,
        };
    });
};

export const mapListedProductConnectionType = (
    apiData: ListedProductsFragmentApi['products'],
    currencyCode: string,
): ListedProductConnectionType => {
    return {
        ...apiData,
        pageInfo: mapPageInfoApiData(apiData.pageInfo),
        products: mapListedProductTypes(apiData, currencyCode),
        productFilterOptions: mapProductFilterOptions(apiData.productFilterOptions, currencyCode),
    };
};
