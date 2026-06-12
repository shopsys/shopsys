// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { MapStoreFragment } from '../fragments/MapStoreFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeMapStoresQueryVariables = Types.Exact<{ [key: string]: never; }>;


export type TypeMapStoresQuery = { __typename?: 'Query', stores: { __typename?: 'StoreConnection', edges: Array<{ __typename?: 'StoreEdge', node: { __typename: 'Store', latitude: string | null, longitude: string | null, identifier: string, name: string } | null } | null> | null } };


export const MapStoresQueryDocument = gql`
    query MapStoresQuery @redisCache(ttl: 3600) {
  stores {
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