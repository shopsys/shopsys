// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { CountryFragment } from '../fragments/CountryFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeCountriesQueryVariables = Exact<{ [key: string]: never; }>;


export type TypeCountriesQuery = { countries: Array<{ __typename: 'Country', name: string, code: string }> };


export const CountriesQueryDocument = gql`
    query CountriesQuery {
  countries {
    ...CountryFragment
  }
}
    ${CountryFragment}`;

export function useCountriesQuery(options?: Omit<Urql.UseQueryArgs<TypeCountriesQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeCountriesQuery, TypeCountriesQueryVariables>({ query: CountriesQueryDocument, ...options });
};