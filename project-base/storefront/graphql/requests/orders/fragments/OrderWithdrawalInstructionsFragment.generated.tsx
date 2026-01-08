// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeOrderWithdrawalInstructionsFragment = { __typename: 'Order', withdrawalInstructions: string };

export const OrderWithdrawalInstructionsFragment = gql`
    fragment OrderWithdrawalInstructionsFragment on Order {
  __typename
  withdrawalInstructions
}
    `;