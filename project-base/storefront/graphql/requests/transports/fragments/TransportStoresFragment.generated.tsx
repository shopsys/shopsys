// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ListedStoreConnectionFragment } from '../../stores/fragments/ListedStoreConnectionFragment.generated';
export type TypeTransportStoresFragment = { __typename: 'Transport', uuid: string, stores: { __typename: 'StoreConnection', edges: Array<{ __typename: 'StoreEdge', node: { __typename: 'Store', slug: string, name: string, description: string | null, latitude: string | null, longitude: string | null, street: string, postcode: string, city: string, distance: number | null, email: string | null, phone: string | null, specialMessage: string | null, identifier: string, openingHours: { __typename?: 'OpeningHours', status: Types.TypeStoreOpeningStatusEnum, dayOfWeek: number, openingHoursOfDays: Array<{ __typename?: 'OpeningHoursOfDay', date: any, dayOfWeek: number, openingHoursRanges: Array<{ __typename?: 'OpeningHoursRange', openingTime: string, closingTime: string }> }> }, country: { __typename: 'Country', name: string, code: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null } | null } | null> | null } | null };

export const TransportStoresFragment = gql`
    fragment TransportStoresFragment on Transport {
  __typename
  uuid
  stores {
    ...ListedStoreConnectionFragment
  }
}
    ${ListedStoreConnectionFragment}`;