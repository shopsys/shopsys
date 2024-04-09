import { ListedProductFragmentApi, SearchProductsQueryApi } from 'graphql/generated';

type NodeType = SearchProductsQueryApi['productsSearch']['edges'];

export const getMappedProducts = (unmappedEdges: NodeType | null | undefined): ListedProductFragmentApi[] | undefined =>
    unmappedEdges?.reduce<ListedProductFragmentApi[]>((mappedEdges, edge) => {
        if (edge?.node) {
            return [...mappedEdges, edge.node];
        }
        return mappedEdges;
    }, []);
