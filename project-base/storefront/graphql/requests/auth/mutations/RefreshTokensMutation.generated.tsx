// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { TokenFragments } from '../fragments/TokensFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeRefreshTokensVariables = Types.Exact<{
  refreshToken: Types.Scalars['String']['input'];
}>;


export type TypeRefreshTokens = { __typename?: 'Mutation', RefreshTokens: { __typename?: 'Token', accessToken: string, refreshToken: string } };


export const RefreshTokensDocument = gql`
    mutation RefreshTokens($refreshToken: String!) {
  RefreshTokens(input: {refreshToken: $refreshToken}) {
    ...TokenFragments
  }
}
    ${TokenFragments}`;

export function useRefreshTokens() {
  return Urql.useMutation<TypeRefreshTokens, TypeRefreshTokensVariables>(RefreshTokensDocument);
};