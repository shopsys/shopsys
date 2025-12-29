// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeOrderWithdrawalRequestFragment = { __typename: 'OrderWithdrawalRequest', email: string, firstName: string, lastName: string, telephone: string | null, note: string | null, requestedAt: any };

export const OrderWithdrawalRequestFragment = gql`
    fragment OrderWithdrawalRequestFragment on OrderWithdrawalRequest {
  __typename
  email
  firstName
  lastName
  telephone
  note
  requestedAt
}
    `;