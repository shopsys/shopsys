import { mapPageInfoApiData } from 'connectors/pageInfo/PageInfo';
import {
    ListedProductConnectionFragmentApi,
    ListedProductConnectionPreviewFragmentApi,
    ListedProductFragmentApi,
    ListedVariantFragmentApi,
    SliderProductFragmentApi,
    usePromotedProductsQueryApi,
} from 'graphql/generated';
import { mapProductFilterOptions } from 'helpers/filterOptions/mapProductFilterOptions';
import { useQueryError } from 'hooks/graphQl/useQueryError';
import {
    ListedProductConnectionPreviewType,
    ListedProductConnectionType,
    ListedProductType,
    ListedVariantType,
    SliderProductItemType,
} from 'types/product';

export const mapListedProductType = (apiData: ListedProductFragmentApi): ListedProductType => {
    return {
        ...apiData,
        isMainVariant: apiData.__typename === 'MainVariant',
        categoryNames: apiData.categories.map((category) => category.name),
    };
};

export const mapListedVariantType = (apiData: ListedVariantFragmentApi): ListedVariantType => {
    return {
        ...apiData,
        ...mapListedProductType(apiData),
    };
};

export const usePromotedProducts = (): SliderProductItemType[] | undefined => {
    const [{ data, error }] = usePromotedProductsQueryApi();
    useQueryError(error);

    const apiData = data?.promotedProducts;
    if (apiData === undefined) {
        return undefined;
    }

    return mapSliderProductApiData(apiData);
};

export const mapSliderProductApiData = (apiData: SliderProductFragmentApi[]): SliderProductItemType[] => {
    return apiData.map((apiProduct) => {
        return mapSliderItemProductType(apiProduct);
    });
};

const mapSliderItemProductType = (apiData: SliderProductFragmentApi): SliderProductItemType => {
    return {
        ...apiData,
        isMainVariant: apiData.__typename === 'MainVariant',
        categoryNames: apiData.categories.map((category) => category.name),
    };
};

export const mapListedProductConnectionType = (
    apiData: ListedProductConnectionFragmentApi,
    currencyCode: string,
): ListedProductConnectionType => {
    return {
        ...apiData,
        pageInfo: mapPageInfoApiData(apiData.pageInfo),
        products: mapListedProductTypes(apiData),
        productFilterOptions: mapProductFilterOptions(apiData.productFilterOptions, currencyCode),
    };
};

export const mapListedProductConnectionPreviewType = (
    apiData: ListedProductConnectionPreviewFragmentApi,
    currencyCode: string,
): ListedProductConnectionPreviewType => {
    return {
        ...apiData,
        productFilterOptions: mapProductFilterOptions(apiData.productFilterOptions, currencyCode),
    };
};

const mapListedProductTypes = (apiData: ListedProductConnectionFragmentApi): ListedProductType[] => {
    const result = [];

    if (apiData.edges !== null) {
        for (const edge of apiData.edges) {
            if (edge?.node === undefined || edge.node === null) {
                continue;
            }
            result.push(mapListedProductType(edge.node));
        }
    }

    return result;
};
