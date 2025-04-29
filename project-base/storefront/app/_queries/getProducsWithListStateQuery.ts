'use server';

import { createQuery } from 'app/_urql/urql-dto';
import { getCookieStoreStateFromServer } from 'app/_utils/getCookieStoreStateFromServer';
import {
    ProductListQueryDocument,
    TypeProductListQuery,
    TypeProductListQueryVariables,
} from 'graphql/requests/productLists/queries/ProductListQuery.ssr';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.ssr';
import { TypeProductListTypeEnum } from 'graphql/types';

export const getProducsWithListStateQuery = async (products: TypeListedProductFragment[]) => {
    const { productListUuids } = await getCookieStoreStateFromServer();
    const comparisonUuidUuid = productListUuids?.COMPARISON;
    const wishlistUuid = productListUuids?.WISHLIST;

    const comparisonResult = comparisonUuidUuid
        ? await createQuery<TypeProductListQuery, TypeProductListQueryVariables>(ProductListQueryDocument, {
              input: {
                  uuid: comparisonUuidUuid,
                  type: TypeProductListTypeEnum.Comparison,
              },
          })
        : null;

    const wishlistResult = wishlistUuid
        ? await createQuery<TypeProductListQuery, TypeProductListQueryVariables>(ProductListQueryDocument, {
              input: {
                  uuid: wishlistUuid,
                  type: TypeProductListTypeEnum.Wishlist,
              },
          })
        : null;

    const productsWithListState = products.map((product) => ({
        ...product,
        listState: {
            isInComparison: !!comparisonResult?.data?.productList?.products.find(
                (comparisonProduct) => comparisonProduct.uuid === product.uuid,
            ),
            isInWishlist: !!wishlistResult?.data?.productList?.products.find(
                (wishlistProduct) => wishlistProduct.uuid === product.uuid,
            ),
        },
    }));

    return productsWithListState;
};
