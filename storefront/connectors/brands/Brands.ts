import { BrandDetailFragmentApi, ListedBrandFragmentApi, useBrandsQueryApi } from 'graphql/generated';
import { BrandDetailType, ListedBrandType } from './types';
import { ListedProductEdgesType } from 'components/Blocks/Product/types';
import { mapImageApiData } from 'connectors/image/Image';
import { mapListedProductType } from 'connectors/products/Products';
import { mapPageInfoApiData } from 'connectors/pageInfo/PageInfo';
import { useQueryError } from 'hooks/graphQl/UseQueryError';

export const mapListedBrandApiData = (apiData: ListedBrandFragmentApi): ListedBrandType => {
    return { ...apiData, image: mapImageApiData(apiData.images) };
};

export function getBrands(): ListedBrandType[] | undefined {
    const [{ data, error }] = useBrandsQueryApi();
    useQueryError(error);

    if (data?.brands === undefined) {
        return undefined;
    }

    return data.brands.map((apiBrand) => mapListedBrandApiData(apiBrand));
}

export const mapBrandDetailApiData = (apiData: BrandDetailFragmentApi, currencyCode: string): BrandDetailType => {
    const products: ListedProductEdgesType = {
        ...apiData.products,
        totalCount: apiData.products?.totalCount !== undefined ? apiData.products.totalCount : 0,
        pageInfo: mapPageInfoApiData(apiData.products?.pageInfo),
        edges: [],
    };

    if (apiData?.products?.edges !== undefined && apiData.products.edges !== null) {
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
        seoH1: apiData.seoH1 !== undefined ? apiData.seoH1 : null,
        description: apiData.description !== undefined ? apiData.description : null,
        image: mapImageApiData(apiData.brandImages),
        products: products,
    };
};
