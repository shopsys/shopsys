// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeLogoutMutationVariables = Types.Exact<{ [key: string]: never; }>;


export type TypeLogoutMutation = (
  { __typename?: 'Mutation' }
  & Pick<Types.TypeMutation, 'Logout'>
);


export const LogoutMutationDocument = gql`
    mutation LogoutMutation {
  Logout
}
    `;

export function useLogoutMutation() {
  return Urql.useMutation<TypeLogoutMutation, TypeLogoutMutationVariables>(LogoutMutationDocument);
};