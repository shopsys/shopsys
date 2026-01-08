// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ListedStoreConnectionFragment } from '../fragments/ListedStoreConnectionFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeStoresQueryVariables = Types.Exact<{
  searchText?: Types.InputMaybe<Types.Scalars['String']['input']>;
  coordinates?: Types.InputMaybe<Types.TypeCoordinates>;
}>;


export type TypeStoresQuery = { __typename?: 'Query', stores: { __typename: 'StoreConnection', edges: Array<{ __typename: 'StoreEdge', node: { __typename: 'Store', slug: string, name: string, description: string | null, latitude: string | null, longitude: string | null, street: string, postcode: string, city: string, distance: number | null, email: string | null, phone: string | null, specialMessage: string | null, identifier: string, openingHours: { __typename?: 'OpeningHours', status: Types.TypeStoreOpeningStatusEnum, dayOfWeek: number, openingHoursOfDays: Array<{ __typename?: 'OpeningHoursOfDay', date: any, dayOfWeek: number, openingHoursRanges: Array<{ __typename?: 'OpeningHoursRange', openingTime: string, closingTime: string }> }> }, country: { __typename: 'Country', name: string, code: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null } | null } | null> | null } };


export const StoresQueryDocument = gql`
    query StoresQuery($searchText: String = null, $coordinates: Coordinates = null) {
  stores(searchText: $searchText, coordinates: $coordinates) {
    ...ListedStoreConnectionFragment
  }
}
    ${ListedStoreConnectionFragment}`;

export function useStoresQuery(options?: Omit<Urql.UseQueryArgs<TypeStoresQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeStoresQuery, TypeStoresQueryVariables>({ query: StoresQueryDocument, ...options });
};