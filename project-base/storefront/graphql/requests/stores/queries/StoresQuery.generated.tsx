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


export type TypeStoresQuery = (
  { __typename?: 'Query' }
  & { stores: (
    { __typename: 'StoreConnection' }
    & { edges: Types.Maybe<Array<Types.Maybe<(
      { __typename: 'StoreEdge' }
      & { node: Types.Maybe<(
        { __typename: 'Store' }
        & Pick<Types.TypeStore, 'slug' | 'name' | 'description' | 'latitude' | 'longitude' | 'street' | 'postcode' | 'city' | 'distance' | 'email' | 'phone' | 'specialMessage'>
        & { identifier: Types.TypeStore['uuid'] }
        & { openingHours: (
          { __typename?: 'OpeningHours' }
          & Pick<Types.TypeOpeningHours, 'status' | 'dayOfWeek'>
          & { openingHoursOfDays: Array<(
            { __typename?: 'OpeningHoursOfDay' }
            & Pick<Types.TypeOpeningHoursOfDay, 'date' | 'dayOfWeek'>
            & { openingHoursRanges: Array<(
              { __typename?: 'OpeningHoursRange' }
              & Pick<Types.TypeOpeningHoursRange, 'openingTime' | 'closingTime'>
            )> }
          )> }
        ), country: (
          { __typename: 'Country' }
          & Pick<Types.TypeCountry, 'name' | 'code'>
        ), mainImage: Types.Maybe<(
          { __typename: 'Image' }
          & Pick<Types.TypeImage, 'name' | 'url'>
        )> }
      )> }
    )>>> }
  ) }
);


export const StoresQueryDocument = gql`
    query StoresQuery($searchText: String = null, $coordinates: Coordinates = null) @redisCache(ttl: 3600) {
  stores(searchText: $searchText, coordinates: $coordinates) {
    ...ListedStoreConnectionFragment
  }
}
    ${ListedStoreConnectionFragment}`;

export function useStoresQuery(options?: Omit<Urql.UseQueryArgs<TypeStoresQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeStoresQuery, TypeStoresQueryVariables>({ query: StoresQueryDocument, ...options });
};