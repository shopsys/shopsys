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

export type TypeProductListCountQueryVariables = Exact<{
  input: Types.TypeProductListInput;
}>;


export type TypeProductListCountQuery = { productList: { uuid: string, itemsCount: number } | null };


export const ProductListCountQueryDocument = gql`
    query ProductListCountQuery($input: ProductListInput!) {
  productList(input: $input) {
    uuid
    itemsCount
  }
}
    `;

export function useProductListCountQuery(options: Omit<Urql.UseQueryArgs<TypeProductListCountQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeProductListCountQuery, TypeProductListCountQueryVariables>({ query: ProductListCountQueryDocument, ...options });
};