// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeSetOrderPaymentStatusPageValidityHashMutationVariables = Types.Exact<{
  orderUuid: Types.Scalars['Uuid']['input'];
}>;


export type TypeSetOrderPaymentStatusPageValidityHashMutation = { __typename?: 'Mutation', SetOrderPaymentStatusPageValidityHashMutation: { __typename?: 'PaymentInstructionSetupData', goPayEmbedJs: string, orderPaymentStatusPageValidityHash: string } };


export const SetOrderPaymentStatusPageValidityHashMutationDocument = gql`
    mutation SetOrderPaymentStatusPageValidityHashMutation($orderUuid: Uuid!) {
  SetOrderPaymentStatusPageValidityHashMutation(orderUuid: $orderUuid) {
    goPayEmbedJs
    orderPaymentStatusPageValidityHash
  }
}
    `;

export function useSetOrderPaymentStatusPageValidityHashMutation() {
  return Urql.useMutation<TypeSetOrderPaymentStatusPageValidityHashMutation, TypeSetOrderPaymentStatusPageValidityHashMutationVariables>(SetOrderPaymentStatusPageValidityHashMutationDocument);
};