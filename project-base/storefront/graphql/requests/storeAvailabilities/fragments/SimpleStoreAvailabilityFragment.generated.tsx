// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeSimpleStoreAvailabilityFragment = { __typename?: 'StoreAvailability', availabilityInformation: string, availabilityStatus: Types.TypeAvailabilityStatusEnum, store: { __typename?: 'Store', slug: string, storeName: string } | null };

export const SimpleStoreAvailabilityFragment = gql`
    fragment SimpleStoreAvailabilityFragment on StoreAvailability {
  availabilityInformation
  availabilityStatus
  store {
    slug
    storeName: name
  }
}
    `;