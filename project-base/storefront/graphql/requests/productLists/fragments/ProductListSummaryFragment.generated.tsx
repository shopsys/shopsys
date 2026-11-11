// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeProductListSummaryFragment = { __typename: 'ProductList', uuid: string, itemsCount: number, productUuids: Array<string> };

export const ProductListSummaryFragment = gql`
    fragment ProductListSummaryFragment on ProductList {
  __typename
  uuid
  itemsCount
  productUuids
}
    `;