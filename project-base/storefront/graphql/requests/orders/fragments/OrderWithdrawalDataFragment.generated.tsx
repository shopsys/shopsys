// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeOrderWithdrawalDataFragment = (
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
);

export const OrderWithdrawalDataFragment = gql`
    fragment OrderWithdrawalDataFragment on Order {
  __typename
  uuid
  number
  urlHash
  firstName
  lastName
  email
  telephone
  telephoneData {
    prefix
    countryCode
    number
  }
  canRequestWithdrawal
  customerUser {
    billingAddressUuid
  }
}
    `;