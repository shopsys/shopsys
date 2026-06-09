// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { MapStoreFragment } from '../fragments/MapStoreFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeMapStoresQueryVariables = Types.Exact<{
  searchText?: Types.InputMaybe<Types.Scalars['String']['input']>;
  coordinates?: Types.InputMaybe<Types.TypeCoordinates>;
}>;


export type TypeMapStoresQuery = { __typename?: 'Query', stores: { __typename?: 'StoreConnection', edges: Array<{ __typename?: 'StoreEdge', node: { __typename: 'Store', latitude: string | null, longitude: string | null, identifier: string } | null } | null> | null } };


export const MapStoresQueryDocument = gql`
    query MapStoresQuery($searchText: String = null, $coordinates: Coordinates = null) @redisCache(ttl: 3600) {
  stores(searchText: $searchText, coordinates: $coordinates) {
    edges {
      node {
        ...MapStoreFragment
      }
    }
  }
}
    ${MapStoreFragment}`;

export function useMapStoresQuery(options?: Omit<Urql.UseQueryArgs<TypeMapStoresQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeMapStoresQuery, TypeMapStoresQueryVariables>({ query: MapStoresQueryDocument, ...options });
};