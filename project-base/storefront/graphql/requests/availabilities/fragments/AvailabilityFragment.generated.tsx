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

export type TypeAvailabilityFragment = { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum };

export const AvailabilityFragment = gql`
    fragment AvailabilityFragment on Availability {
  __typename
  name
  status
}
    `;