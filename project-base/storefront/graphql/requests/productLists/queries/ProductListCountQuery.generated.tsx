// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeProductListCountQueryVariables = Types.Exact<{
  input: Types.TypeProductListInput;
}>;


export type TypeProductListCountQuery = (
  { __typename?: 'Query' }
  & { productList: Types.Maybe<(
    { __typename?: 'ProductList' }
    & Pick<Types.TypeProductList, 'uuid' | 'itemsCount'>
  )> }
);


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