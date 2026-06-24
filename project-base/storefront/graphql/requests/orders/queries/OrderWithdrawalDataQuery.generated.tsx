// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { OrderWithdrawalDataFragment } from '../fragments/OrderWithdrawalDataFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeOrderWithdrawalDataQueryVariables = Exact<{
  urlHash: string;
}>;


export type TypeOrderWithdrawalDataQuery = { order: { __typename: 'Order', uuid: string, number: string, urlHash: string, firstName: string | null, lastName: string | null, email: string, telephone: string, canRequestWithdrawal: boolean, telephoneData: { prefix: string | null, countryCode: string | null, number: string }, customerUser:
      | { billingAddressUuid: string }
      | { billingAddressUuid: string }
      | { billingAddressUuid: string }
      | { billingAddressUuid: string }
     | null } | null };


export const OrderWithdrawalDataQueryDocument = gql`
    query OrderWithdrawalDataQuery($urlHash: String!) {
  order(urlHash: $urlHash) {
    ...OrderWithdrawalDataFragment
  }
}
    ${OrderWithdrawalDataFragment}`;

export function useOrderWithdrawalDataQuery(options: Omit<Urql.UseQueryArgs<TypeOrderWithdrawalDataQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeOrderWithdrawalDataQuery, TypeOrderWithdrawalDataQueryVariables>({ query: OrderWithdrawalDataQueryDocument, ...options });
};