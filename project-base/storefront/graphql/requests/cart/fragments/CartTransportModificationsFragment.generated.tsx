// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeCartTransportModificationsFragment = (
  { __typename: 'CartTransportModificationsResult' }
  & Pick<Types.TypeCartTransportModificationsResult, 'transportPriceChanged' | 'transportUnavailable' | 'transportWeightLimitExceeded' | 'personalPickupStoreUnavailable'>
);

export const CartTransportModificationsFragment = gql`
    fragment CartTransportModificationsFragment on CartTransportModificationsResult {
  __typename
  transportPriceChanged
  transportUnavailable
  transportWeightLimitExceeded
  personalPickupStoreUnavailable
}
    `;