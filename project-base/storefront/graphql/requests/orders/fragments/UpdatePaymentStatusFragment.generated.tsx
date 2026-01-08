// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeUpdatePaymentStatusFragment = { __typename: 'Order', isPaid: boolean, number: string, hasPaymentInProcess: boolean, urlHash: string, payment: { __typename?: 'Payment', name: string, type: Types.TypePaymentTypeEnum } };

export const UpdatePaymentStatusFragment = gql`
    fragment UpdatePaymentStatusFragment on Order {
  __typename
  isPaid
  number
  payment {
    name
    type
  }
  hasPaymentInProcess
  urlHash
}
    `;