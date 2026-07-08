// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { StoreDetailFragment } from '../fragments/StoreDetailFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeStoreDetailQueryVariables = Types.Exact<{
  urlSlug?: Types.InputMaybe<Types.Scalars['String']['input']>;
}>;


export type TypeStoreDetailQuery = (
  { __typename?: 'Query' }
  & { store: Types.Maybe<(
    { __typename: 'Store' }
    & Pick<Types.TypeStore, 'uuid' | 'slug' | 'description' | 'street' | 'city' | 'postcode' | 'email' | 'phone' | 'directions' | 'specialMessage' | 'latitude' | 'longitude'>
    & { storeName: Types.TypeStore['name'] }
    & { country: (
      { __typename: 'Country' }
      & Pick<Types.TypeCountry, 'name' | 'code'>
    ), openingHours: (
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
    ), breadcrumb: Array<(
      { __typename: 'Link' }
      & Pick<Types.TypeLink, 'name' | 'slug'>
    )>, storeImages: Array<(
      { __typename: 'Image' }
      & Pick<Types.TypeImage, 'name' | 'url'>
    )> }
  )> }
);


export const StoreDetailQueryDocument = gql`
    query StoreDetailQuery($urlSlug: String) @friendlyUrl {
  store(urlSlug: $urlSlug) {
    ...StoreDetailFragment
  }
}
    ${StoreDetailFragment}`;

export function useStoreDetailQuery(options?: Omit<Urql.UseQueryArgs<TypeStoreDetailQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeStoreDetailQuery, TypeStoreDetailQueryVariables>({ query: StoreDetailQueryDocument, ...options });
};