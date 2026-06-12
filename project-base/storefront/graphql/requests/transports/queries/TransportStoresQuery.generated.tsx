// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { TransportStoresFragment } from '../fragments/TransportStoresFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeTransportStoresQueryVariables = Types.Exact<{
  uuid: Types.Scalars['Uuid']['input'];
  searchText?: Types.InputMaybe<Types.Scalars['String']['input']>;
  coordinates?: Types.InputMaybe<Types.TypeCoordinates>;
  first?: Types.InputMaybe<Types.Scalars['Int']['input']>;
  after?: Types.InputMaybe<Types.Scalars['String']['input']>;
}>;


export type TypeTransportStoresQuery = { __typename?: 'Query', transport: { __typename: 'Transport', uuid: string, stores: { __typename: 'StoreConnection', searchCoordinates: { __typename?: 'StoreSearchCoordinates', latitude: number, longitude: number } | null, pageInfo: { __typename?: 'PageInfo', hasNextPage: boolean, endCursor: string | null }, edges: Array<{ __typename: 'StoreEdge', node: { __typename: 'Store', slug: string, name: string, description: string | null, latitude: string | null, longitude: string | null, street: string, postcode: string, city: string, distance: number | null, email: string | null, phone: string | null, specialMessage: string | null, identifier: string, openingHours: { __typename?: 'OpeningHours', status: Types.TypeStoreOpeningStatusEnum, dayOfWeek: number, openingHoursOfDays: Array<{ __typename?: 'OpeningHoursOfDay', date: any, dayOfWeek: number, openingHoursRanges: Array<{ __typename?: 'OpeningHoursRange', openingTime: string, closingTime: string }> }> }, country: { __typename: 'Country', name: string, code: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null } | null } | null> | null } | null } | null };


export const TransportStoresQueryDocument = gql`
    query TransportStoresQuery($uuid: Uuid!, $searchText: String = null, $coordinates: Coordinates = null, $first: Int, $after: String) {
  transport(uuid: $uuid) {
    ...TransportStoresFragment
  }
}
    ${TransportStoresFragment}`;

export function useTransportStoresQuery(options: Omit<Urql.UseQueryArgs<TypeTransportStoresQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeTransportStoresQuery, TypeTransportStoresQueryVariables>({ query: TransportStoresQueryDocument, ...options });
};