import { FlagDetailFragmentApi } from 'graphql/generated';
import { FlagDetailType } from 'types/flag';
import { ListedProductEdgesType } from 'types/product';
import { mapListedProductType } from 'connectors/products/Products';
import { mapPageInfoApiData } from 'connectors/pageInfo/PageInfo';

export const mapFlagDetailApiData = (apiData: FlagDetailFragmentApi, currencyCode: string): FlagDetailType => {
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
        __typename: 'Flag',
        products: products,
    };
};
