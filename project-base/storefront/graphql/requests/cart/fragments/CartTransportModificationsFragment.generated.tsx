// @ts-nocheck
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