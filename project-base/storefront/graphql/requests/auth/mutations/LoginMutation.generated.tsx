// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { TokenFragments } from '../fragments/TokensFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeLoginMutationVariables = Exact<{
  email: string;
  password: string;
  previousCartUuid?: string | null | undefined;
  productListsUuids: Array<string> | string;
  shouldOverwriteCustomerUserCart?: boolean | null | undefined;
}>;


export type TypeLoginMutation = { Login: { showCartMergeInfo: boolean, tokens: { accessToken: string, refreshToken: string } } };


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