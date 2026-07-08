// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { TransportStoresFragment } from '../fragments/TransportStoresFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeTransportStoresQueryVariables = Types.Exact<{
  uuid: Types.Scalars['Uuid']['input'];
}>;


export type TypeTransportStoresQuery = (
  { __typename?: 'Query' }
  & { transport: Types.Maybe<(
    { __typename: 'Transport' }
    & Pick<Types.TypeTransport, 'uuid'>
    & { stores: Types.Maybe<(
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
    )> }
  )> }
);


export const TransportStoresQueryDocument = gql`
    query TransportStoresQuery($uuid: Uuid!) {
  transport(uuid: $uuid) {
    ...TransportStoresFragment
  }
}
    ${TransportStoresFragment}`;

export function useTransportStoresQuery(options: Omit<Urql.UseQueryArgs<TypeTransportStoresQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeTransportStoresQuery, TypeTransportStoresQueryVariables>({ query: TransportStoresQueryDocument, ...options });
};