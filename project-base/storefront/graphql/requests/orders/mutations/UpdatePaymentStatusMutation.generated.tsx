// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { UpdatePaymentStatusFragment } from '../fragments/UpdatePaymentStatusFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeUpdatePaymentStatusMutationVariables = Types.Exact<{
  orderUuid: Types.Scalars['Uuid']['input'];
}>;


export type TypeUpdatePaymentStatusMutation = { __typename?: 'Mutation', UpdatePaymentStatus: { __typename: 'Order', isPaid: boolean, number: string, paymentTransactionsCount: number, hasPaymentInProcess: boolean, lastExternalPaymentUrl: string | null, paymentStatus: string | null, urlHash: string, items: Array<{ __typename?: 'OrderItem', type: Types.TypeOrderItemTypeEnum, payment: { __typename?: 'Payment', name: string, type: Types.TypePaymentTypeEnum } | null }>, confirmationPageContent: { __typename: 'OrderConfirmationPageContent', content: string, status: Types.TypeOrderConfirmationPageContentStatusEnum } } };


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