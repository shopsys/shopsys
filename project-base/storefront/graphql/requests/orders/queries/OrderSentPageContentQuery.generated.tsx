// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeOrderSentPageContentQueryVariables = Types.Exact<{
  orderUuid: Types.Scalars['Uuid']['input'];
}>;


export type TypeOrderSentPageContentQuery = { __typename?: 'Query', orderSentPageContent: string };


export const OrderSentPageContentQueryDocument = gql`
    query OrderSentPageContentQuery($orderUuid: Uuid!) {
  orderSentPageContent(orderUuid: $orderUuid)
}
    `;

export function useOrderSentPageContentQuery(options: Omit<Urql.UseQueryArgs<TypeOrderSentPageContentQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeOrderSentPageContentQuery, TypeOrderSentPageContentQueryVariables>({ query: OrderSentPageContentQueryDocument, ...options });
};