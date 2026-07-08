// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ListedStoreConnectionFragment } from '../../stores/fragments/ListedStoreConnectionFragment.generated';
export type TypeTransportStoresFragment = (
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
);

export const TransportStoresFragment = gql`
    fragment TransportStoresFragment on Transport {
  __typename
  uuid
  stores {
    ...ListedStoreConnectionFragment
  }
}
    ${ListedStoreConnectionFragment}`;