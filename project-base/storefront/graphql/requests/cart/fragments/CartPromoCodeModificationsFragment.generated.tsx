// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeCartPromoCodeModificationsFragment = (
  { __typename: 'CartPromoCodeModificationsResult' }
  & Pick<Types.TypeCartPromoCodeModificationsResult, 'noLongerApplicablePromoCode'>
);

export const CartPromoCodeModificationsFragment = gql`
    fragment CartPromoCodeModificationsFragment on CartPromoCodeModificationsResult {
  __typename
  noLongerApplicablePromoCode
}
    `;