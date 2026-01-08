// @ts-nocheck
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