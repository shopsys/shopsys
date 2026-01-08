// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { OrderWithdrawalDataFragment } from '../fragments/OrderWithdrawalDataFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeOrderWithdrawalDataQueryVariables = Types.Exact<{
  urlHash: Types.Scalars['String']['input'];
}>;


export type TypeOrderWithdrawalDataQuery = { __typename?: 'Query', order: { __typename: 'Order', uuid: string, number: string, urlHash: string, firstName: string | null, lastName: string | null, email: string, telephone: string, canRequestWithdrawal: boolean, customerUser: { __typename?: 'CompanyCustomerUser', billingAddressUuid: string } | { __typename?: 'CurrentCompanyCustomerUser', billingAddressUuid: string } | { __typename?: 'CurrentRegularCustomerUser', billingAddressUuid: string } | { __typename?: 'RegularCustomerUser', billingAddressUuid: string } | null } | null };


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