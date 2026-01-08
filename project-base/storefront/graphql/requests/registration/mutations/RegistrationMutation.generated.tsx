// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { TokenFragments } from '../../auth/fragments/TokensFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeRegistrationMutationVariables = Types.Exact<{
  input: Types.TypeRegistrationDataInput;
}>;


export type TypeRegistrationMutation = { __typename?: 'Mutation', Register: { __typename?: 'LoginResult', showCartMergeInfo: boolean, tokens: { __typename?: 'Token', accessToken: string, refreshToken: string } } };


export const RegistrationMutationDocument = gql`
    mutation RegistrationMutation($input: RegistrationDataInput!) {
  Register(input: $input) {
    tokens {
      ...TokenFragments
    }
    showCartMergeInfo
  }
}
    ${TokenFragments}`;

export function useRegistrationMutation() {
  return Urql.useMutation<TypeRegistrationMutation, TypeRegistrationMutationVariables>(RegistrationMutationDocument);
};