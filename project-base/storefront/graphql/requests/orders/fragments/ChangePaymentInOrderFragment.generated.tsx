// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeChangePaymentInOrderFragment = { __typename: 'Order', urlHash: string, number: string, paymentTransactionsCount: number };

export const ChangePaymentInOrderFragment = gql`
    fragment ChangePaymentInOrderFragment on Order {
  __typename
  urlHash
  number
  paymentTransactionsCount
}
    `;