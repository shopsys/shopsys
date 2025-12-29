// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { PriceFragment } from '../../prices/fragments/PriceFragment.generated';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
export type TypeSimplePaymentFragment = { __typename: 'Payment', uuid: string, name: string, description: string | null, instructions: string | null, type: Types.TypePaymentTypeEnum, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, goPayPaymentMethod: { __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string } | null };

export const SimplePaymentFragment = gql`
    fragment SimplePaymentFragment on Payment {
  __typename
  uuid
  name
  description
  instructions
  price {
    ...PriceFragment
  }
  mainImage {
    ...ImageFragment
  }
  type
  goPayPaymentMethod {
    __typename
    identifier
    name
    paymentGroup
  }
}
    ${PriceFragment}
${ImageFragment}`;