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

export type TypeUpdatePaymentStatusFragment = { __typename: 'UpdatePaymentStatusResult', isPaid: boolean, orderNumber: string, paymentName: string, paymentTransactionsCount: number, hasPaymentInProcess: boolean, lastExternalPaymentUrl: string | null, lastPaymentStatus: string | null, confirmationPageContent: { __typename: 'OrderConfirmationPageContent', content: string, status: Types.TypeOrderConfirmationPageContentStatusEnum } };

export const UpdatePaymentStatusFragment = gql`
    fragment UpdatePaymentStatusFragment on UpdatePaymentStatusResult {
  __typename
  isPaid
  orderNumber
  paymentName
  paymentTransactionsCount
  hasPaymentInProcess
  lastExternalPaymentUrl
  lastPaymentStatus
  confirmationPageContent {
    __typename
    content
    status
  }
}
    `;