// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeRemoveCustomerUserMutationVariables = Exact<{
  customerUserUuid: string;
}>;


export type TypeRemoveCustomerUserMutation = { RemoveCustomerUser: boolean };


export const RemoveCustomerUserMutationDocument = gql`
    mutation RemoveCustomerUserMutation($customerUserUuid: Uuid!) {
  RemoveCustomerUser(input: {customerUserUuid: $customerUserUuid})
}
    `;

export function useRemoveCustomerUserMutation() {
  return Urql.useMutation<TypeRemoveCustomerUserMutation, TypeRemoveCustomerUserMutationVariables>(RemoveCustomerUserMutationDocument);
};