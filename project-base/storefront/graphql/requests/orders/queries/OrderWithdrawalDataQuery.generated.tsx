// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { OrderWithdrawalDataFragment } from '../fragments/OrderWithdrawalDataFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeOrderWithdrawalDataQueryVariables = Types.Exact<{
  urlHash: Types.Scalars['String']['input'];
}>;


export type TypeOrderWithdrawalDataQuery = (
  { __typename?: 'Query' }
  & { order: Types.Maybe<(
    { __typename: 'Order' }
    & Pick<Types.TypeOrder, 'uuid' | 'number' | 'urlHash' | 'firstName' | 'lastName' | 'email' | 'telephone' | 'canRequestWithdrawal'>
    & { telephoneData: (
      { __typename?: 'PhoneData' }
      & Pick<Types.TypePhoneData, 'prefix' | 'countryCode' | 'number'>
    ), customerUser: Types.Maybe<(
      { __typename?: 'CompanyCustomerUser' }
      & Pick<Types.TypeCompanyCustomerUser, 'billingAddressUuid'>
    ) | (
      { __typename?: 'CurrentCompanyCustomerUser' }
      & Pick<Types.TypeCurrentCompanyCustomerUser, 'billingAddressUuid'>
    ) | (
      { __typename?: 'CurrentRegularCustomerUser' }
      & Pick<Types.TypeCurrentRegularCustomerUser, 'billingAddressUuid'>
    ) | (
      { __typename?: 'RegularCustomerUser' }
      & Pick<Types.TypeRegularCustomerUser, 'billingAddressUuid'>
    )> }
  )> }
);


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