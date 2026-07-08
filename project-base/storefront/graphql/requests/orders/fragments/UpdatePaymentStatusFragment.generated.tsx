// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeUpdatePaymentStatusFragment = (
  { __typename: 'Order' }
  & Pick<Types.TypeOrder, 'isPaid' | 'number' | 'paymentTransactionsCount' | 'hasPaymentInProcess' | 'lastExternalPaymentUrl' | 'paymentStatus' | 'urlHash'>
  & { items: Array<(
    { __typename?: 'OrderItem' }
    & Pick<Types.TypeOrderItem, 'type'>
    & { payment: Types.Maybe<(
      { __typename?: 'Payment' }
      & Pick<Types.TypePayment, 'name' | 'type'>
    )> }
  )>, confirmationPageContent: (
    { __typename: 'OrderConfirmationPageContent' }
    & Pick<Types.TypeOrderConfirmationPageContent, 'content' | 'status'>
  ) }
);

export const UpdatePaymentStatusFragment = gql`
    fragment UpdatePaymentStatusFragment on Order {
  __typename
  isPaid
  number
  paymentTransactionsCount
  items {
    type
    payment {
      name
      type
    }
  }
  hasPaymentInProcess
  lastExternalPaymentUrl
  paymentStatus
  confirmationPageContent {
    __typename
    content
    status
  }
  urlHash
}
    `;