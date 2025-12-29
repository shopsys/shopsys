// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypePasswordRecoveryMutationVariables = Types.Exact<{
  email: Types.Scalars['String']['input'];
}>;


export type TypePasswordRecoveryMutation = { __typename?: 'Mutation', RequestPasswordRecovery: string };


export const PasswordRecoveryMutationDocument = gql`
    mutation PasswordRecoveryMutation($email: String!) {
  RequestPasswordRecovery(email: $email)
}
    `;

export function usePasswordRecoveryMutation() {
  return Urql.useMutation<TypePasswordRecoveryMutation, TypePasswordRecoveryMutationVariables>(PasswordRecoveryMutationDocument);
};