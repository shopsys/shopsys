// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
/** Product Availability statuses */
export type TypeAvailabilityStatusEnum =
  /** Product availability status in stock */
  | 'InStock'
  /** Product availability status out of stock */
  | 'OutOfStock';

export type TypeSimpleStoreAvailabilityFragment = { availabilityInformation: string, availabilityStatus: Types.TypeAvailabilityStatusEnum, store: { slug: string, storeName: string } | null };

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