// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { TokenFragments } from '../../auth/fragments/TokensFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeRecoverPasswordMutationVariables = Types.Exact<{
  email: Types.Scalars['String']['input'];
  hash: Types.Scalars['String']['input'];
  newPassword: Types.Scalars['Password']['input'];
  cartUuid?: Types.InputMaybe<Types.Scalars['Uuid']['input']>;
  productListsUuids: Array<Types.Scalars['Uuid']['input']> | Types.Scalars['Uuid']['input'];
}>;


export type TypeRecoverPasswordMutation = { __typename?: 'Mutation', RecoverPassword: { __typename?: 'LoginResult', showCartMergeInfo: boolean, tokens: { __typename?: 'Token', accessToken: string, refreshToken: string } } };


export const RecoverPasswordMutationDocument = gql`
    mutation RecoverPasswordMutation($email: String!, $hash: String!, $newPassword: Password!, $cartUuid: Uuid, $productListsUuids: [Uuid!]!) {
  RecoverPassword(
    input: {email: $email, hash: $hash, newPassword: $newPassword, cartUuid: $cartUuid, productListsUuids: $productListsUuids}
  ) {
    tokens {
      ...TokenFragments
    }
    showCartMergeInfo
  }
}
    ${TokenFragments}`;

export function useRecoverPasswordMutation() {
  return Urql.useMutation<TypeRecoverPasswordMutation, TypeRecoverPasswordMutationVariables>(RecoverPasswordMutationDocument);
};