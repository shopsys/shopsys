// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeChangePaymentInOrderFragment = (
  { __typename: 'Order' }
  & Pick<Types.TypeOrder, 'urlHash' | 'number' | 'paymentTransactionsCount'>
);

export const ChangePaymentInOrderFragment = gql`
    fragment ChangePaymentInOrderFragment on Order {
  __typename
  urlHash
  number
  paymentTransactionsCount
}
    `;