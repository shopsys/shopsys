// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeOrderWithdrawalRequestMutationVariables = Types.Exact<{
  input: Types.TypeOrderWithdrawalRequestInput;
}>;


export type TypeOrderWithdrawalRequestMutation = (
  { __typename?: 'Mutation' }
  & Pick<Types.TypeMutation, 'OrderWithdrawalRequest'>
);


export const OrderWithdrawalRequestMutationDocument = gql`
    mutation OrderWithdrawalRequestMutation($input: OrderWithdrawalRequestInput!) {
  OrderWithdrawalRequest(input: $input)
}
    `;

export function useOrderWithdrawalRequestMutation() {
  return Urql.useMutation<TypeOrderWithdrawalRequestMutation, TypeOrderWithdrawalRequestMutationVariables>(OrderWithdrawalRequestMutationDocument);
};