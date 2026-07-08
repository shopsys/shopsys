// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { CountryFragment } from '../fragments/CountryFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeCountriesQueryVariables = Types.Exact<{ [key: string]: never; }>;


export type TypeCountriesQuery = (
  { __typename?: 'Query' }
  & { countries: Array<(
    { __typename: 'Country' }
    & Pick<Types.TypeCountry, 'name' | 'code'>
  )> }
);


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