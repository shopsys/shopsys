// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeUpdatePaymentStatusFragment = { __typename: 'Order', isPaid: boolean, number: string, paymentTransactionsCount: number, hasPaymentInProcess: boolean, lastExternalPaymentUrl: string | null, paymentStatus: string | null, urlHash: string, items: Array<{ __typename?: 'OrderItem', type: Types.TypeOrderItemTypeEnum, payment: { __typename?: 'Payment', name: string, type: Types.TypePaymentTypeEnum } | null }>, confirmationPageContent: { __typename: 'OrderConfirmationPageContent', content: string, status: Types.TypeOrderConfirmationPageContentStatusEnum } };

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