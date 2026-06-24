// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypePasswordRecoveryMutationVariables = Exact<{
  email: string;
}>;


export type TypePasswordRecoveryMutation = { RequestPasswordRecovery: string };


export const PasswordRecoveryMutationDocument = gql`
    mutation PasswordRecoveryMutation($email: String!) {
  RequestPasswordRecovery(email: $email)
}
    `;

export function usePasswordRecoveryMutation() {
  return Urql.useMutation<TypePasswordRecoveryMutation, TypePasswordRecoveryMutationVariables>(PasswordRecoveryMutationDocument);
};