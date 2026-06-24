// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeOrderUrlHashByReturnHashQueryVariables = Exact<{
  returnHash: string;
}>;


export type TypeOrderUrlHashByReturnHashQuery = { orderUrlHashByReturnHash: string | null };


export const OrderUrlHashByReturnHashQueryDocument = gql`
    query OrderUrlHashByReturnHashQuery($returnHash: String!) {
  orderUrlHashByReturnHash(returnHash: $returnHash)
}
    `;

export function useOrderUrlHashByReturnHashQuery(options: Omit<Urql.UseQueryArgs<TypeOrderUrlHashByReturnHashQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeOrderUrlHashByReturnHashQuery, TypeOrderUrlHashByReturnHashQueryVariables>({ query: OrderUrlHashByReturnHashQueryDocument, ...options });
};