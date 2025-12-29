// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { StoreDetailFragment } from '../../stores/fragments/StoreDetailFragment.generated';
export type TypeStoreAvailabilityFragment = { __typename: 'StoreAvailability', availabilityInformation: string, availabilityStatus: Types.TypeAvailabilityStatusEnum, store: { __typename: 'Store', uuid: string, slug: string, description: string | null, street: string, city: string, postcode: string, email: string | null, phone: string | null, directions: string | null, specialMessage: string | null, latitude: string | null, longitude: string | null, storeName: string, country: { __typename: 'Country', name: string, code: string }, openingHours: { __typename?: 'OpeningHours', status: Types.TypeStoreOpeningStatusEnum, dayOfWeek: number, openingHoursOfDays: Array<{ __typename?: 'OpeningHoursOfDay', date: any, dayOfWeek: number, openingHoursRanges: Array<{ __typename?: 'OpeningHoursRange', openingTime: string, closingTime: string }> }> }, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, storeImages: Array<{ __typename: 'Image', name: string | null, url: string }> } | null };

export const StoreAvailabilityFragment = gql`
    fragment StoreAvailabilityFragment on StoreAvailability {
  __typename
  availabilityInformation
  availabilityStatus
  store {
    ...StoreDetailFragment
  }
}
    ${StoreDetailFragment}`;