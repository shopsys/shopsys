// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeMapStoreFragment = { __typename: 'Store', latitude: string | null, longitude: string | null, identifier: string, name: string };

export const MapStoreFragment = gql`
    fragment MapStoreFragment on Store {
  __typename
  identifier: uuid
  name: city
  latitude
  longitude
}
    `;