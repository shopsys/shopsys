// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { TokenFragments } from '../../auth/fragments/TokensFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeRecoverPasswordMutationVariables = Exact<{
  email: string;
  hash: string;
  newPassword: string;
  cartUuid?: string | null | undefined;
  productListsUuids: Array<string> | string;
}>;


export type TypeRecoverPasswordMutation = { RecoverPassword: { showCartMergeInfo: boolean, tokens: { accessToken: string, refreshToken: string } } };


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