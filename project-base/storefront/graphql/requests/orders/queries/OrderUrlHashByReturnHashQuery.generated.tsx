// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeOrderUrlHashByReturnHashQueryVariables = Types.Exact<{
  returnHash: Types.Scalars['String']['input'];
}>;


export type TypeOrderUrlHashByReturnHashQuery = (
  { __typename?: 'Query' }
  & Pick<Types.TypeQuery, 'orderUrlHashByReturnHash'>
);


export const OrderUrlHashByReturnHashQueryDocument = gql`
    query OrderUrlHashByReturnHashQuery($returnHash: String!) {
  orderUrlHashByReturnHash(returnHash: $returnHash)
}
    `;

export function useOrderUrlHashByReturnHashQuery(options: Omit<Urql.UseQueryArgs<TypeOrderUrlHashByReturnHashQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeOrderUrlHashByReturnHashQuery, TypeOrderUrlHashByReturnHashQueryVariables>({ query: OrderUrlHashByReturnHashQueryDocument, ...options });
};