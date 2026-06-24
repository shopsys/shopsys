// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
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