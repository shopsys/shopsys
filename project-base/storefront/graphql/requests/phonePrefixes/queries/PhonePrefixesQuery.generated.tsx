// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypePhonePrefixesQueryVariables = Exact<{ [key: string]: never; }>;


export type TypePhonePrefixesQuery = { settings: { phonePrefixes: Array<{ code: string, dialCode: string, countryName: string, flagEmoji: string }> } | null };


export const PhonePrefixesQueryDocument = gql`
    query PhonePrefixesQuery {
  settings {
    phonePrefixes {
      code
      dialCode
      countryName
      flagEmoji
    }
  }
}
    `;

export function usePhonePrefixesQuery(options?: Omit<Urql.UseQueryArgs<TypePhonePrefixesQueryVariables>, 'query'>) {
  return Urql.useQuery<TypePhonePrefixesQuery, TypePhonePrefixesQueryVariables>({ query: PhonePrefixesQueryDocument, ...options });
};