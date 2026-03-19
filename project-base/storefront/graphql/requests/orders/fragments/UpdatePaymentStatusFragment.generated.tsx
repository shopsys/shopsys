// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeUpdatePaymentStatusFragment = { __typename: 'Order', isPaid: boolean, number: string, hasPaymentInProcess: boolean, urlHash: string, items: Array<{ __typename?: 'OrderItem', type: Types.TypeOrderItemTypeEnum, payment: { __typename?: 'Payment', name: string, type: Types.TypePaymentTypeEnum } | null }> };

export const UpdatePaymentStatusFragment = gql`
    fragment UpdatePaymentStatusFragment on Order {
  __typename
  isPaid
  number
  items {
    type
    payment {
      name
      type
    }
  }
  hasPaymentInProcess
  urlHash
}
    `;