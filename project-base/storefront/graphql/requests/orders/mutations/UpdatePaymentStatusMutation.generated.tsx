// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { UpdatePaymentStatusFragment } from '../fragments/UpdatePaymentStatusFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
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

export type TypeUpdatePaymentStatusMutationVariables = Exact<{
  orderUuid: string;
}>;


export type TypeUpdatePaymentStatusMutation = { UpdatePaymentStatus: { __typename: 'Order', isPaid: boolean, number: string, paymentTransactionsCount: number, hasPaymentInProcess: boolean, lastExternalPaymentUrl: string | null, paymentStatus: string | null, urlHash: string, items: Array<{ type: Types.TypeOrderItemTypeEnum, payment: { name: string, type: Types.TypePaymentTypeEnum } | null }>, confirmationPageContent: { __typename: 'OrderConfirmationPageContent', content: string, status: Types.TypeOrderConfirmationPageContentStatusEnum } } };


export const UpdatePaymentStatusMutationDocument = gql`
    mutation UpdatePaymentStatusMutation($orderUuid: Uuid!) {
  UpdatePaymentStatus(orderUuid: $orderUuid) {
    ...UpdatePaymentStatusFragment
  }
}
    ${UpdatePaymentStatusFragment}`;

export function useUpdatePaymentStatusMutation() {
  return Urql.useMutation<TypeUpdatePaymentStatusMutation, TypeUpdatePaymentStatusMutationVariables>(UpdatePaymentStatusMutationDocument);
};