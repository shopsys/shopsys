// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeOrdersCountQueryVariables = Types.Exact<{
  after?: Types.InputMaybe<Types.Scalars['String']['input']>;
  first?: Types.InputMaybe<Types.Scalars['Int']['input']>;
}>;


export type TypeOrdersCountQuery = { __typename?: 'Query', orders: { __typename?: 'OrderConnection', totalCount: number } | null };


export const OrdersCountQueryDocument = gql`
    query OrdersCountQuery($after: String, $first: Int) {
  orders(after: $after, first: $first) {
    totalCount
  }
}
    `;

export function useOrdersCountQuery(options?: Omit<Urql.UseQueryArgs<TypeOrdersCountQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeOrdersCountQuery, TypeOrdersCountQueryVariables>({ query: OrdersCountQueryDocument, ...options });
};