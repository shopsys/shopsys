// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ProductListSummaryFragment } from '../fragments/ProductListSummaryFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeProductListInput = {
  /** Product list type */
  type: TypeProductListTypeEnum;
  /** Product list identifier */
  uuid?: string | null | undefined;
};

export type TypeProductListMoveProductInput = {
  /** Product after which the moved product is placed, null moves it to the top */
  afterProductUuid?: string | null | undefined;
  productListInput: TypeProductListInput;
  /** Product to move */
  productUuid: string;
};

/** One of possible types of the product list */
export type TypeProductListTypeEnum =
  | 'COMPARISON'
  | 'WISHLIST';

export type TypeMoveProductInListMutationVariables = Exact<{
  input: Types.TypeProductListMoveProductInput;
}>;


export type TypeMoveProductInListMutation = { MoveProductInList: { __typename: 'ProductList', uuid: string, itemsCount: number, productUuids: Array<string> } };


export const MoveProductInListMutationDocument = gql`
    mutation MoveProductInListMutation($input: ProductListMoveProductInput!) {
  MoveProductInList(input: $input) {
    ...ProductListSummaryFragment
  }
}
    ${ProductListSummaryFragment}`;

export function useMoveProductInListMutation() {
  return Urql.useMutation<TypeMoveProductInListMutation, TypeMoveProductInListMutationVariables>(MoveProductInListMutationDocument);
};