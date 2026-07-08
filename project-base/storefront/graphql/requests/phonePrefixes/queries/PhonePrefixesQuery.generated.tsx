// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypePhonePrefixesQueryVariables = Types.Exact<{ [key: string]: never; }>;


export type TypePhonePrefixesQuery = (
  { __typename?: 'Query' }
  & { settings: Types.Maybe<(
    { __typename?: 'Settings' }
    & { phonePrefixes: Array<(
      { __typename?: 'PhonePrefix' }
      & Pick<Types.TypePhonePrefix, 'code' | 'dialCode' | 'countryName' | 'flagEmoji'>
    )> }
  )> }
);


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