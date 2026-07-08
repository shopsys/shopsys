// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { PriceFragment } from '../../prices/fragments/PriceFragment.generated';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
import { SimplePaymentFragment } from '../../payments/fragments/SimplePaymentFragment.generated';
export type TypeTransportWithAvailablePaymentsFragment = (
  { __typename: 'Transport' }
  & Pick<Types.TypeTransport, 'uuid' | 'name' | 'description' | 'daysUntilDelivery' | 'transportTypeCode' | 'isPersonalPickup' | 'vatPercent'>
  & { price: (
    { __typename: 'Price' }
    & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount'>
  ), mainImage: Types.Maybe<(
    { __typename: 'Image' }
    & Pick<Types.TypeImage, 'name' | 'url'>
  )>, payments: Array<(
    { __typename: 'Payment' }
    & Pick<Types.TypePayment, 'uuid' | 'name' | 'description' | 'instructions' | 'type'>
    & { price: (
      { __typename: 'Price' }
      & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount'>
    ), mainImage: Types.Maybe<(
      { __typename: 'Image' }
      & Pick<Types.TypeImage, 'name' | 'url'>
    )>, goPayPaymentMethod: Types.Maybe<(
      { __typename: 'GoPayPaymentMethod' }
      & Pick<Types.TypeGoPayPaymentMethod, 'identifier' | 'name' | 'paymentGroup'>
    )> }
  )> }
);

export const TransportWithAvailablePaymentsFragment = gql`
    fragment TransportWithAvailablePaymentsFragment on Transport {
  __typename
  uuid
  name
  description
  price {
    ...PriceFragment
  }
  mainImage {
    ...ImageFragment
  }
  payments {
    ...SimplePaymentFragment
  }
  daysUntilDelivery
  transportTypeCode
  isPersonalPickup
  vatPercent
}
    ${PriceFragment}
${ImageFragment}
${SimplePaymentFragment}`;