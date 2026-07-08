// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { TokenFragments } from '../fragments/TokensFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeLoginMutationVariables = Types.Exact<{
  email: Types.Scalars['String']['input'];
  password: Types.Scalars['Password']['input'];
  previousCartUuid?: Types.InputMaybe<Types.Scalars['Uuid']['input']>;
  productListsUuids: Array<Types.Scalars['Uuid']['input']> | Types.Scalars['Uuid']['input'];
  shouldOverwriteCustomerUserCart?: Types.InputMaybe<Types.Scalars['Boolean']['input']>;
}>;


export type TypeLoginMutation = (
  { __typename?: 'Mutation' }
  & { Login: (
    { __typename?: 'LoginResult' }
    & Pick<Types.TypeLoginResult, 'showCartMergeInfo'>
    & { tokens: (
      { __typename?: 'Token' }
      & Pick<Types.TypeToken, 'accessToken' | 'refreshToken'>
    ) }
  ) }
);


export const LoginMutationDocument = gql`
    mutation LoginMutation($email: String!, $password: Password!, $previousCartUuid: Uuid, $productListsUuids: [Uuid!]!, $shouldOverwriteCustomerUserCart: Boolean = false) {
  Login(
    input: {email: $email, password: $password, cartUuid: $previousCartUuid, productListsUuids: $productListsUuids, shouldOverwriteCustomerUserCart: $shouldOverwriteCustomerUserCart}
  ) {
    tokens {
      ...TokenFragments
    }
    showCartMergeInfo
  }
}
    ${TokenFragments}`;

export function useLoginMutation() {
  return Urql.useMutation<TypeLoginMutation, TypeLoginMutationVariables>(LoginMutationDocument);
};