import { CategoryDetailApiType, CategoryDetailType } from '../../components/pages/CategoryDetail/types';
import { ListedProductEdgesType } from '../../components/blocks/product/types';
import { mapListedProductNode } from '../products/Products';

export function mapCategoryData(data: CategoryDetailApiType, currencyCode: string): CategoryDetailType {
    const products: ListedProductEdgesType = {
        ...data.products,
        edges: [],
    };

    for (const edge of data.products.edges) {
        products.edges.push({
            ...edge,
            node: mapListedProductNode(edge.node, currencyCode),
        });
    }

    return {
        ...data,
        products: products,
    };
}
