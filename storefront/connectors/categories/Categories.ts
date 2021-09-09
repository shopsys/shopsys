import { CategoryDetailApiType, CategoryDetailType } from '../../components/Pages/CategoryDetail/types';
import { ListedProductEdgesType } from '../../components/Blocks/Product/types';
import { mapListedProductNode } from '../products/Products';

export function mapCategoryDetailData(
    apiCategoryDetailData: CategoryDetailApiType,
    currencyCode: string,
): CategoryDetailType {
    const products: ListedProductEdgesType = {
        ...apiCategoryDetailData.products,
        edges: [],
    };

    for (const edge of apiCategoryDetailData.products.edges) {
        products.edges.push({
            ...edge,
            node: mapListedProductNode(edge.node, currencyCode),
        });
    }

    return {
        ...apiCategoryDetailData,
        products: products,
        children: apiCategoryDetailData.children.map((child) => {
            return {
                ...child,
                image: child.images.length > 0 ? child.images[0] : null,
            };
        }),
        linkedCategories: apiCategoryDetailData.linkedCategories.map((child) => {
            return {
                ...child,
                image: child.images.length > 0 ? child.images[0] : null,
            };
        }),
    };
}
