import {
    ListedProductConnectionType,
    ListedProductType,
    ListedVariantType,
    SliderProductItemType,
} from 'types/product';
import {
    ListedProductFragmentApi,
    ListedProductsFragmentApi,
    ListedVariantFragmentApi,
    SliderProductFragmentApi,
    usePromotedProductsQueryApi,
} from 'graphql/generated';
import { getFirstImageSize } from 'connectors/image/Image';
import { mapPageInfoApiData } from 'connectors/pageInfo/PageInfo';
import { mapProductFilterOptions } from 'helpers/filterOptions/MapProductFilterOptions';
import { mapProductPriceData } from 'connectors/price/Prices';
import { mapStoreAvailabilities } from 'connectors/availability/Availability';
import { useQueryError } from 'hooks/graphQl/UseQueryError';
import { useShopsysSelector } from 'redux/main';

export const mapListedProductType = (apiData: ListedProductFragmentApi, currencyCode: string): ListedProductType => {
    return {
        ...apiData,
        isMainVariant: apiData.__typename === 'MainVariant',
        availability: apiData.availability.name,
        price: mapProductPriceData(apiData.price, currencyCode),
        image: getFirstImageSize(apiData.images),
    };
};

export const mapListedVariantType = (apiData: ListedVariantFragmentApi, currencyCode: string): ListedVariantType => {
    return {
        ...mapListedProductType(apiData, currencyCode),
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
        return mapSliderItemProductType(apiProduct, currencyCode);
    });
};

const mapSliderItemProductType = (apiData: SliderProductFragmentApi, currencyCode: string): SliderProductItemType => {
    return {
        ...apiData,
        isMainVariant: apiData.__typename === 'MainVariant',
        availability: apiData.availability.name,
        price: mapProductPriceData(apiData.price, currencyCode),
        image: getFirstImageSize(apiData.images),
    };
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

const mapListedProductTypes = (
    apiData: ListedProductsFragmentApi['products'],
    currencyCode: string,
): ListedProductType[] => {
    const result = [];

    if (apiData.edges !== null) {
        for (const edge of apiData.edges) {
            if (edge?.node === undefined || edge.node === null) {
                continue;
            }
            result.push(mapListedProductType(edge.node, currencyCode));
        }
    }

    return result;
};
