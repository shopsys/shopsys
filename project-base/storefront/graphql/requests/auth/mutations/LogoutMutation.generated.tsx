// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeLogoutMutationVariables = Exact<{ [key: string]: never; }>;


export type TypeLogoutMutation = { Logout: boolean };


export const LogoutMutationDocument = gql`
    mutation LogoutMutation {
  Logout
}
    `;

export function useLogoutMutation() {
  return Urql.useMutation<TypeLogoutMutation, TypeLogoutMutationVariables>(LogoutMutationDocument);
};