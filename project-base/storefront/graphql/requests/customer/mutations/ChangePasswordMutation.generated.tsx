// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeChangePasswordMutationVariables = Types.Exact<{
  email: Types.Scalars['String']['input'];
  oldPassword: Types.Scalars['Password']['input'];
  newPassword: Types.Scalars['Password']['input'];
}>;


export type TypeChangePasswordMutation = { __typename?: 'Mutation', ChangePassword: { __typename?: 'CurrentCompanyCustomerUser', email: string } | { __typename?: 'CurrentRegularCustomerUser', email: string } };


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