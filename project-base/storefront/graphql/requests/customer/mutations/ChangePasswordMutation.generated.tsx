// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeChangePasswordMutationVariables = Exact<{
  email: string;
  oldPassword: string;
  newPassword: string;
}>;


export type TypeChangePasswordMutation = { ChangePassword:
    | { email: string }
    | { email: string }
   };


export const ChangePasswordMutationDocument = gql`
    mutation ChangePasswordMutation($email: String!, $oldPassword: Password!, $newPassword: Password!) {
  ChangePassword(
    input: {email: $email, oldPassword: $oldPassword, newPassword: $newPassword}
  ) {
    email
  }
}
    `;

export function useChangePasswordMutation() {
  return Urql.useMutation<TypeChangePasswordMutation, TypeChangePasswordMutationVariables>(ChangePasswordMutationDocument);
};