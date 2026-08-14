// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeOrderWithdrawalRequestFragment = { __typename: 'OrderWithdrawalRequest', email: string, firstName: string, lastName: string, telephone: string | null, note: string | null, requestedAt: string, confirmed: boolean };

export const OrderWithdrawalRequestFragment = gql`
    fragment OrderWithdrawalRequestFragment on OrderWithdrawalRequest {
  __typename
  email
  firstName
  lastName
  telephone
  note
  requestedAt
  confirmed
}
    `;