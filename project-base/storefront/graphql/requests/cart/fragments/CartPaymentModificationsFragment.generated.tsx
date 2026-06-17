// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeCartPaymentModificationsFragment = { __typename: 'CartPaymentModificationsResult', paymentPriceChanged: boolean, paymentUnavailable: boolean };

export const CartPaymentModificationsFragment = gql`
    fragment CartPaymentModificationsFragment on CartPaymentModificationsResult {
  __typename
  paymentPriceChanged
  paymentUnavailable
}
    `;