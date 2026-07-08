// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { TokenFragments } from '../../auth/fragments/TokensFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeRegistrationByOrderMutationVariables = Types.Exact<{
  input: Types.TypeRegistrationByOrderInput;
}>;


export type TypeRegistrationByOrderMutation = (
  { __typename?: 'Mutation' }
  & { RegisterByOrder: (
    { __typename?: 'LoginResult' }
    & Pick<Types.TypeLoginResult, 'showCartMergeInfo'>
    & { tokens: (
      { __typename?: 'Token' }
      & Pick<Types.TypeToken, 'accessToken' | 'refreshToken'>
    ) }
  ) }
);


export const RegistrationByOrderMutationDocument = gql`
    mutation RegistrationByOrderMutation($input: RegistrationByOrderInput!) {
  RegisterByOrder(input: $input) {
    tokens {
      ...TokenFragments
    }
    showCartMergeInfo
  }
}
    ${TokenFragments}`;

export function useRegistrationByOrderMutation() {
  return Urql.useMutation<TypeRegistrationByOrderMutation, TypeRegistrationByOrderMutationVariables>(RegistrationByOrderMutationDocument);
};