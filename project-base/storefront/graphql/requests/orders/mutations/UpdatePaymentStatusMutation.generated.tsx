// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { UpdatePaymentStatusFragment } from '../fragments/UpdatePaymentStatusFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeUpdatePaymentStatusMutationVariables = Types.Exact<{
  orderUuid: Types.Scalars['Uuid']['input'];
}>;


export type TypeUpdatePaymentStatusMutation = (
  { __typename?: 'Mutation' }
  & { UpdatePaymentStatus: (
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
  ) }
);


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