// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { TokenFragments } from '../fragments/TokensFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeLoginViaExchangeTokenMutationVariables = Types.Exact<{
  exchangeToken: Types.Scalars['String']['input'];
}>;


export type TypeLoginViaExchangeTokenMutation = { __typename?: 'Mutation', LoginViaExchangeToken: { __typename?: 'Token', accessToken: string, refreshToken: string } };


export const LoginViaExchangeTokenMutationDocument = gql`
    mutation LoginViaExchangeTokenMutation($exchangeToken: String!) {
  LoginViaExchangeToken(exchangeToken: $exchangeToken) {
    ...TokenFragments
  }
}
    ${TokenFragments}`;

export function useLoginViaExchangeTokenMutation() {
  return Urql.useMutation<TypeLoginViaExchangeTokenMutation, TypeLoginViaExchangeTokenMutationVariables>(LoginViaExchangeTokenMutationDocument);
};