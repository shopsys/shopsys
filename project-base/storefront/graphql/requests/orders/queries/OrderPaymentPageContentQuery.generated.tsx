// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeOrderPaymentPageContentQueryVariables = Types.Exact<{
  orderUuid: Types.Scalars['Uuid']['input'];
}>;


export type TypeOrderPaymentPageContentQuery = { __typename?: 'Query', orderPaymentPageContent: { __typename: 'OrderPaymentPageContent', content: string, status: Types.TypePaymentContentPageStatusEnum } };


export const OrderPaymentPageContentQueryDocument = gql`
    query OrderPaymentPageContentQuery($orderUuid: Uuid!) {
  orderPaymentPageContent(orderUuid: $orderUuid) {
    __typename
    content
    status
  }
}
    `;

export function useOrderPaymentPageContentQuery(options: Omit<Urql.UseQueryArgs<TypeOrderPaymentPageContentQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeOrderPaymentPageContentQuery, TypeOrderPaymentPageContentQueryVariables>({ query: OrderPaymentPageContentQueryDocument, ...options });
};