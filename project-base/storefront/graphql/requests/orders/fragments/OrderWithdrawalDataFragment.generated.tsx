// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeOrderWithdrawalDataFragment = { __typename: 'Order', uuid: string, number: string, urlHash: string, firstName: string | null, lastName: string | null, email: string, telephone: string, canRequestWithdrawal: boolean, telephoneData: { prefix: string | null, countryCode: string | null, number: string }, customerUser:
    | { billingAddressUuid: string }
    | { billingAddressUuid: string }
    | { billingAddressUuid: string }
    | { billingAddressUuid: string }
   | null };

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