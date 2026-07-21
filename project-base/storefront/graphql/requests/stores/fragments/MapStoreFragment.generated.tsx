// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
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