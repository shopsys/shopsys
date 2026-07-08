// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeOrderWithdrawalRequestFragment = (
  { __typename: 'OrderWithdrawalRequest' }
  & Pick<Types.TypeOrderWithdrawalRequest, 'email' | 'firstName' | 'lastName' | 'telephone' | 'note' | 'requestedAt'>
);

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