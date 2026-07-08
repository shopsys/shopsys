// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeRemoveProductListMutationVariables = Types.Exact<{
  input: Types.TypeProductListInput;
}>;


export type TypeRemoveProductListMutation = (
  { __typename?: 'Mutation' }
  & { RemoveProductList: Types.Maybe<(
    { __typename?: 'ProductList' }
    & Pick<Types.TypeProductList, 'uuid'>
  )> }
);


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