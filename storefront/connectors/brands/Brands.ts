import { BrandDetailFragmentApi, ListedBrandFragmentApi, useBrandsQueryApi } from 'graphql/generated';
import { BrandDetailType, ListedBrandType } from 'types/brand';
import { getFirstImage } from 'connectors/image/Image';
import { mapListedProductConnectionType } from 'connectors/products/Products';
import { useQueryError } from 'hooks/graphQl/UseQueryError';

export function useBrands(): ListedBrandType[] | undefined {
    const [{ data, error }] = useBrandsQueryApi();
    useQueryError(error);

    if (data?.brands === undefined) {
        return undefined;
    }

    return mapListedBrandsApiData(data.brands);
}

export const mapBrandDetail = (apiData: BrandDetailFragmentApi, currencyCode: string): BrandDetailType => {
    return {
        ...apiData,
        __typename: 'Brand',
        productConnection: mapListedProductConnectionType(apiData.products, currencyCode),
        image: getFirstImage(apiData.brandImages),
    };
};

export const mapListedBrand = (apiData: ListedBrandFragmentApi): ListedBrandType => {
    return {
        ...apiData,
        image: getFirstImage(apiData.images),
    };
};

export const mapListedBrandsApiData = (apiData: ListedBrandFragmentApi[]): ListedBrandType[] => {
    return apiData.map((brand) => mapListedBrand(brand));
};
