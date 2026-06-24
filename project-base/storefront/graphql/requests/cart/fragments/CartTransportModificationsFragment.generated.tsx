// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeCartTransportModificationsFragment = { __typename: 'CartTransportModificationsResult', transportPriceChanged: boolean, transportUnavailable: boolean, transportWeightLimitExceeded: boolean, personalPickupStoreUnavailable: boolean };

export const CartTransportModificationsFragment = gql`
    fragment CartTransportModificationsFragment on CartTransportModificationsResult {
  __typename
  transportPriceChanged
  transportUnavailable
  transportWeightLimitExceeded
  personalPickupStoreUnavailable
}
    `;