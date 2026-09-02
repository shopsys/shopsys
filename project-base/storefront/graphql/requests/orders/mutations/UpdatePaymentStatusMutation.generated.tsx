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

export type TypeUpdatePaymentStatusMutationVariables = Exact<{
  orderUuid: string;
  orderUrlHash?: string | null | undefined;
}>;


export type TypeUpdatePaymentStatusMutation = { UpdatePaymentStatus: { __typename: 'UpdatePaymentStatusResult', isPaid: boolean, orderNumber: string, paymentName: string, paymentTransactionsCount: number, hasPaymentInProcess: boolean, isAwaitingPayment: boolean, lastExternalPaymentUrl: string | null, lastPaymentStatus: string | null, confirmationPageContent: { __typename: 'OrderConfirmationPageContent', content: string, status: Types.TypeOrderConfirmationPageContentStatusEnum } } };


export const UpdatePaymentStatusMutationDocument = gql`
    mutation UpdatePaymentStatusMutation($orderUuid: Uuid!, $orderUrlHash: String) {
  UpdatePaymentStatus(orderUuid: $orderUuid, orderUrlHash: $orderUrlHash) {
    ...UpdatePaymentStatusFragment
  }
}
    ${UpdatePaymentStatusFragment}`;

export function useUpdatePaymentStatusMutation() {
  return Urql.useMutation<TypeUpdatePaymentStatusMutation, TypeUpdatePaymentStatusMutationVariables>(UpdatePaymentStatusMutationDocument);
};