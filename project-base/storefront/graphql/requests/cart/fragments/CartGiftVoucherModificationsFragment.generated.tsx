// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeCartGiftVoucherModificationsFragment = { __typename: 'CartGiftVoucherModificationsResult', noLongerApplicableGiftVouchers: Array<string> };

export const CartGiftVoucherModificationsFragment = gql`
    fragment CartGiftVoucherModificationsFragment on CartGiftVoucherModificationsResult {
  __typename
  noLongerApplicableGiftVouchers
}
    `;