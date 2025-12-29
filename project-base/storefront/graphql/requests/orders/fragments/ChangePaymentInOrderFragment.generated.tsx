// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { SimplePaymentFragment } from '../../payments/fragments/SimplePaymentFragment.generated';
export type TypeChangePaymentInOrderFragment = { __typename: 'Order', urlHash: string, number: string, payment: { __typename: 'Payment', uuid: string, name: string, description: string | null, instructions: string | null, type: Types.TypePaymentTypeEnum, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, goPayPaymentMethod: { __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string } | null } };

export const ChangePaymentInOrderFragment = gql`
    fragment ChangePaymentInOrderFragment on Order {
  __typename
  urlHash
  number
  payment {
    ...SimplePaymentFragment
  }
}
    ${SimplePaymentFragment}`;