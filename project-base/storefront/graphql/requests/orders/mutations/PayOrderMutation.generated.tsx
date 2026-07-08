// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypePayOrderMutationVariables = Types.Exact<{
  orderUuid: Types.Scalars['Uuid']['input'];
}>;


export type TypePayOrderMutation = (
  { __typename?: 'Mutation' }
  & { PayOrder: (
    { __typename?: 'PaymentSetupCreationData' }
    & { goPayCreatePaymentSetup: Types.Maybe<(
      { __typename?: 'GoPayCreatePaymentSetup' }
      & Pick<Types.TypeGoPayCreatePaymentSetup, 'gatewayUrl' | 'goPayId' | 'embedJs'>
    )> }
  ) }
);


export const PayOrderMutationDocument = gql`
    mutation PayOrderMutation($orderUuid: Uuid!) {
  PayOrder(orderUuid: $orderUuid) {
    goPayCreatePaymentSetup {
      gatewayUrl
      goPayId
      embedJs
    }
  }
}
    `;

export function usePayOrderMutation() {
  return Urql.useMutation<TypePayOrderMutation, TypePayOrderMutationVariables>(PayOrderMutationDocument);
};