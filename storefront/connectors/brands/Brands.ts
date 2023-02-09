import { mapListedProductConnectionPreviewType } from 'connectors/products/Products';
import { BrandDetailFragmentApi, useBrandsQueryApi } from 'graphql/generated';
import { useQueryError } from 'hooks/graphQl/useQueryError';
import { BrandDetailType, ListedBrandType } from 'types/brand';

export function useBrands(): ListedBrandType[] | undefined {
    const [{ data, error }] = useBrandsQueryApi();
    useQueryError(error);

    if (data?.brands === undefined) {
        return undefined;
    }

    return data.brands;
}

export const mapBrandDetail = (apiData: BrandDetailFragmentApi, currencyCode: string): BrandDetailType => {
    return {
        ...apiData,
        __typename: 'Brand',
        productConnection: mapListedProductConnectionPreviewType(apiData.products, currencyCode),
    };
};
