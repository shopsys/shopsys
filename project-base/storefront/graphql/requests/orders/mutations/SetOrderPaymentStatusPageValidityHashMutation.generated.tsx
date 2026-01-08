// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeSetOrderPaymentStatusPageValidityHashMutationVariables = Types.Exact<{
  orderUuid: Types.Scalars['Uuid']['input'];
  orderPaymentStatusPageValidityHash: Types.Scalars['String']['input'];
}>;


export type TypeSetOrderPaymentStatusPageValidityHashMutation = { __typename?: 'Mutation', SetOrderPaymentStatusPageValidityHashMutation: string };


export const SetOrderPaymentStatusPageValidityHashMutationDocument = gql`
    mutation SetOrderPaymentStatusPageValidityHashMutation($orderUuid: Uuid!, $orderPaymentStatusPageValidityHash: String!) {
  SetOrderPaymentStatusPageValidityHashMutation(
    orderUuid: $orderUuid
    orderPaymentStatusPageValidityHash: $orderPaymentStatusPageValidityHash
  )
}
    `;

export function useSetOrderPaymentStatusPageValidityHashMutation() {
  return Urql.useMutation<TypeSetOrderPaymentStatusPageValidityHashMutation, TypeSetOrderPaymentStatusPageValidityHashMutationVariables>(SetOrderPaymentStatusPageValidityHashMutationDocument);
};