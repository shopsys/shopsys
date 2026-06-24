// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
/** Represents the status of the order confirmation page content. */
export type TypeOrderConfirmationPageContentStatusEnum =
  | 'FAILED'
  | 'IN_PROCESS'
  | 'SUCCESSFUL';

/** One of possible types of the order item */
export type TypeOrderItemTypeEnum =
  | 'discount'
  | 'payment'
  | 'product'
  | 'productGift'
  | 'promotion'
  | 'rounding'
  | 'transport';

/** One of the possible methods of the payment type */
export type TypePaymentTypeEnum =
  | 'bankTransfer'
  | 'basic'
  | 'goPay';

export type TypeUpdatePaymentStatusFragment = { __typename: 'Order', isPaid: boolean, number: string, paymentTransactionsCount: number, hasPaymentInProcess: boolean, lastExternalPaymentUrl: string | null, paymentStatus: string | null, urlHash: string, items: Array<{ type: Types.TypeOrderItemTypeEnum, payment: { name: string, type: Types.TypePaymentTypeEnum } | null }>, confirmationPageContent: { __typename: 'OrderConfirmationPageContent', content: string, status: Types.TypeOrderConfirmationPageContentStatusEnum } };

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