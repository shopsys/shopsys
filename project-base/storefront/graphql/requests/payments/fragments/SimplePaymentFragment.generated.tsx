// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { PriceFragment } from '../../prices/fragments/PriceFragment.generated';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
export type TypeSimplePaymentFragment = (
  { __typename: 'Payment' }
  & Pick<Types.TypePayment, 'uuid' | 'name' | 'description' | 'instructions' | 'type'>
  & { price: (
    { __typename: 'Price' }
    & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'currencyCode'>
  ), mainImage: Types.Maybe<(
    { __typename: 'Image' }
    & Pick<Types.TypeImage, 'name' | 'url'>
  )>, goPayPaymentMethod: Types.Maybe<(
    { __typename: 'GoPayPaymentMethod' }
    & Pick<Types.TypeGoPayPaymentMethod, 'identifier' | 'name' | 'paymentGroup'>
  )> }
);

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