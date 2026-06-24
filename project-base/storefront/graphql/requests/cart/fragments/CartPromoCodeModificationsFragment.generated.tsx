// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeCartPromoCodeModificationsFragment = { __typename: 'CartPromoCodeModificationsResult', noLongerApplicablePromoCode: Array<string> };

export const CartPromoCodeModificationsFragment = gql`
    fragment CartPromoCodeModificationsFragment on CartPromoCodeModificationsResult {
  __typename
  noLongerApplicablePromoCode
}
    `;