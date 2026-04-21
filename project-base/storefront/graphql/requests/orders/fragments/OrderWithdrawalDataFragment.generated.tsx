// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeOrderWithdrawalDataFragment = { __typename: 'Order', uuid: string, number: string, urlHash: string, firstName: string | null, lastName: string | null, email: string, telephone: string, canRequestWithdrawal: boolean, telephoneData: { __typename?: 'PhoneData', prefix: string | null, countryCode: string | null, number: string }, customerUser: { __typename?: 'CompanyCustomerUser', billingAddressUuid: string } | { __typename?: 'CurrentCompanyCustomerUser', billingAddressUuid: string } | { __typename?: 'CurrentRegularCustomerUser', billingAddressUuid: string } | { __typename?: 'RegularCustomerUser', billingAddressUuid: string } | null };

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