// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ListedStoreFragment } from '../fragments/ListedStoreFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeStoreQueryVariables = Types.Exact<{
  uuid?: Types.InputMaybe<Types.Scalars['Uuid']['input']>;
}>;


export type TypeStoreQuery = (
  { __typename?: 'Query' }
  & { store: Types.Maybe<(
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
);


export const StoreQueryDocument = gql`
    query StoreQuery($uuid: Uuid) {
  store(uuid: $uuid) {
    ...ListedStoreFragment
  }
}
    ${ListedStoreFragment}`;

export function useStoreQuery(options?: Omit<Urql.UseQueryArgs<TypeStoreQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeStoreQuery, TypeStoreQueryVariables>({ query: StoreQueryDocument, ...options });
};