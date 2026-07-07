// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeCountryFragment = { __typename: 'Country', name: string, code: string };

export const CountryFragment = gql`
    fragment CountryFragment on Country {
  __typename
  name
  code
}
    `;