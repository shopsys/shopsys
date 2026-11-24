// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeProductListSummaryFragment = { __typename: 'ProductList', uuid: string, productUuids: Array<string>, mainVariantUuids: Array<string>, products: { totalCount: number } };

export const ProductListSummaryFragment = gql`
    fragment ProductListSummaryFragment on ProductList {
  __typename
  uuid
  products(first: 0) {
    totalCount
  }
  productUuids
  mainVariantUuids
}
    `;