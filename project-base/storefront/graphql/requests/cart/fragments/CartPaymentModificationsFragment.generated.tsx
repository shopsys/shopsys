// @ts-nocheck
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