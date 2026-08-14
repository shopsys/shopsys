// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeConfirmOrderWithdrawalRequestMutationVariables = Exact<{
  confirmationHash: string;
}>;


export type TypeConfirmOrderWithdrawalRequestMutation = { ConfirmOrderWithdrawalRequest: string };


export const ConfirmOrderWithdrawalRequestMutationDocument = gql`
    mutation ConfirmOrderWithdrawalRequestMutation($confirmationHash: String!) {
  ConfirmOrderWithdrawalRequest(confirmationHash: $confirmationHash)
}
    `;

export function useConfirmOrderWithdrawalRequestMutation() {
  return Urql.useMutation<TypeConfirmOrderWithdrawalRequestMutation, TypeConfirmOrderWithdrawalRequestMutationVariables>(ConfirmOrderWithdrawalRequestMutationDocument);
};