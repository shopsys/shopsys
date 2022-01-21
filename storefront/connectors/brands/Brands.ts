import { BrandDetailFragmentApi, ListedBrandFragmentApi, useBrandsQueryApi } from 'graphql/generated';
import { BrandDetailType, ListedBrandType } from 'types/brand';
import { getFirstImageSize } from 'connectors/image/Image';
import { ListedProductEdgesType } from 'types/product';
import { mapListedProductType } from 'connectors/products/Products';
import { mapPageInfoApiData } from 'connectors/pageInfo/PageInfo';
import { useQueryError } from 'hooks/graphQl/UseQueryError';

export function getBrands(): ListedBrandType[] | undefined {
    const [{ data, error }] = useBrandsQueryApi();
    useQueryError(error);

    if (data?.brands === undefined) {
        return undefined;
    }

    return data.brands.map((apiBrand) => mapListedBrand(apiBrand));
}

export const mapBrandDetail = (apiData: BrandDetailFragmentApi, currencyCode: string): BrandDetailType => {
    const products: ListedProductEdgesType = {
        ...apiData.products,
        totalCount: apiData.products?.totalCount !== undefined ? apiData.products.totalCount : 0,
        pageInfo: mapPageInfoApiData(apiData.products?.pageInfo),
        edges: [],
        productFilterOptions: null,
    };

    if (apiData.products?.edges !== undefined && apiData.products.edges !== null) {
        for (const edge of apiData.products.edges) {
            if (edge?.node === undefined || edge.node === null) {
                continue;
            }
            products.edges.push({
                ...edge,
                node: mapListedProductType(edge.node, currencyCode),
            });
        }
    }

    return {
        ...apiData,
        __typename: 'Brand',
        image: getFirstImageSize(apiData.brandImages),
        products: products,
    };
};

export const mapListedBrand = (apiData: ListedBrandFragmentApi): ListedBrandType => {
    return {
        ...apiData,
        image: getFirstImageSize(apiData.images),
    };
};
