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

/** One of possible types of the product list */
export type TypeProductListTypeEnum =
  | 'COMPARISON'
  | 'WISHLIST';

export type TypeProductListUpdateInput = {
  productListInput: TypeProductListInput;
  /** Product identifier */
  productUuid: string;
};

export type TypeAddProductToListMutationVariables = Exact<{
  input: Types.TypeProductListUpdateInput;
}>;


export type TypeAddProductToListMutation = { AddProductToList: { __typename: 'ProductList', uuid: string, itemsCount: number, productUuids: Array<string> } };


export const AddProductToListMutationDocument = gql`
    mutation AddProductToListMutation($input: ProductListUpdateInput!) {
  AddProductToList(input: $input) {
    ...ProductListSummaryFragment
  }
}
    ${ProductListSummaryFragment}`;

export function useAddProductToListMutation() {
  return Urql.useMutation<TypeAddProductToListMutation, TypeAddProductToListMutationVariables>(AddProductToListMutationDocument);
};