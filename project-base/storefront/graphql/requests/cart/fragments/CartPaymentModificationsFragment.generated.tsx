// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeCartPaymentModificationsFragment = (
  { __typename: 'CartPaymentModificationsResult' }
  & Pick<Types.TypeCartPaymentModificationsResult, 'paymentPriceChanged' | 'paymentUnavailable'>
);

export const CartPaymentModificationsFragment = gql`
    fragment CartPaymentModificationsFragment on CartPaymentModificationsResult {
  __typename
  paymentPriceChanged
  paymentUnavailable
}
    `;