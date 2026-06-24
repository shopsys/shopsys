// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
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

export type TypeRemoveProductListMutationVariables = Exact<{
  input: Types.TypeProductListInput;
}>;


export type TypeRemoveProductListMutation = { RemoveProductList: { uuid: string } | null };


export const RemoveProductListMutationDocument = gql`
    mutation RemoveProductListMutation($input: ProductListInput!) {
  RemoveProductList(input: $input) {
    uuid
  }
}
    `;

export function useRemoveProductListMutation() {
  return Urql.useMutation<TypeRemoveProductListMutation, TypeRemoveProductListMutationVariables>(RemoveProductListMutationDocument);
};