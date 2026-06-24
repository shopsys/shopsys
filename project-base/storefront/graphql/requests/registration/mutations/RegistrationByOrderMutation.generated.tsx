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
export type TypeRegistrationByOrderInput = {
  /** Order URL hash */
  orderUrlHash: string;
  /** Customer user password */
  password: string;
  /** Uuids of product lists that should be merged to the product lists of the user after registration */
  productListsUuids: Array<string>;
};

export type TypeRegistrationByOrderMutationVariables = Exact<{
  input: Types.TypeRegistrationByOrderInput;
}>;


export type TypeRegistrationByOrderMutation = { RegisterByOrder: { showCartMergeInfo: boolean, tokens: { accessToken: string, refreshToken: string } } };


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