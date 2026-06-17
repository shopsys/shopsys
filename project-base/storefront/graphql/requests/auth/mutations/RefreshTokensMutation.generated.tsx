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
export type TypeRefreshTokensVariables = Exact<{
  refreshToken: string;
}>;


export type TypeRefreshTokens = { RefreshTokens: { accessToken: string, refreshToken: string } };


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