// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeCountryFragment = (
  { __typename: 'Country' }
  & Pick<Types.TypeCountry, 'name' | 'code'>
);

export const CountryFragment = gql`
    fragment CountryFragment on Country {
  __typename
  name
  code
}
    `;