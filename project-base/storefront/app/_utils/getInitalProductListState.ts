import { getProductListProductsQuery } from 'app/_queries/getProductListProductsQuery';
import { getCookieStoreStateFromServer } from 'app/_utils/getCookieStoreStateFromServer';
import { ProductListState } from 'components/providers/ProductListProvider';
import { TypeProductListTypeEnum } from 'graphql/types';

export async function getInitialProductListState() {
    const { productListUuids } = await getCookieStoreStateFromServer();
    const initialState: ProductListState = {};

    await Promise.all(
        Object.values(TypeProductListTypeEnum).map(async (listType) => {
            const uuid = productListUuids?.[listType] ?? null;

            if (uuid) {
                const products = await getProductListProductsQuery(uuid, listType);
                initialState[listType] = {
                    products: new Set(products.map((product) => product.uuid)),
                    uuid,
                };
            } else {
                initialState[listType] = {
                    products: new Set(),
                    uuid: null,
                };
            }
        }),
    );

    return initialState;
}
