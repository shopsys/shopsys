// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeRemoveCustomerUserMutationVariables = Types.Exact<{
  customerUserUuid: Types.Scalars['Uuid']['input'];
}>;


export type TypeRemoveCustomerUserMutation = { __typename?: 'Mutation', RemoveCustomerUser: boolean };


export const RemoveCustomerUserMutationDocument = gql`
    mutation RemoveCustomerUserMutation($customerUserUuid: Uuid!) {
  RemoveCustomerUser(input: {customerUserUuid: $customerUserUuid})
}
    `;

export function useRemoveCustomerUserMutation() {
  return Urql.useMutation<TypeRemoveCustomerUserMutation, TypeRemoveCustomerUserMutationVariables>(RemoveCustomerUserMutationDocument);
};