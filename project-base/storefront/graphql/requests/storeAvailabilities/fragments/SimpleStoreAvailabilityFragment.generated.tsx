// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeSimpleStoreAvailabilityFragment = (
  { __typename?: 'StoreAvailability' }
  & Pick<Types.TypeStoreAvailability, 'availabilityInformation' | 'availabilityStatus'>
  & { store: Types.Maybe<(
    { __typename?: 'Store' }
    & Pick<Types.TypeStore, 'slug'>
    & { storeName: Types.TypeStore['name'] }
  )> }
);

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