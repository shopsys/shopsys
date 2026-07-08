// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { OrderItemFragment } from './OrderItemFragment.generated';
import { PriceFragment } from '../../prices/fragments/PriceFragment.generated';
export type TypeListedOrderFragment = (
  { __typename: 'Order' }
  & Pick<Types.TypeOrder, 'currencyCode' | 'uuid' | 'number' | 'creationDate' | 'isPaid' | 'hasExternalPayment' | 'hasPaymentInProcess' | 'status' | 'note'>
  & { productItems: Array<(
    { __typename: 'OrderItem' }
    & Pick<Types.TypeOrderItem, 'quantity'>
    & { product: Types.Maybe<(
      { __typename: 'MainVariant' }
      & Pick<Types.TypeMainVariant, 'name' | 'isVisible' | 'isSellingDenied' | 'isInquiryType' | 'isCurrentlyOutOfStock' | 'link'>
      & { mainImage: Types.Maybe<(
        { __typename: 'Image' }
        & Pick<Types.TypeImage, 'name' | 'url'>
      )> }
    ) | (
      { __typename: 'RegularProduct' }
      & Pick<Types.TypeRegularProduct, 'name' | 'isVisible' | 'isSellingDenied' | 'isInquiryType' | 'isCurrentlyOutOfStock' | 'link'>
      & { mainImage: Types.Maybe<(
        { __typename: 'Image' }
        & Pick<Types.TypeImage, 'name' | 'url'>
      )> }
    ) | (
      { __typename: 'Variant' }
      & Pick<Types.TypeVariant, 'name' | 'isVisible' | 'isSellingDenied' | 'isInquiryType' | 'isCurrentlyOutOfStock' | 'link'>
      & { mainImage: Types.Maybe<(
        { __typename: 'Image' }
        & Pick<Types.TypeImage, 'name' | 'url'>
      )> }
    )> }
  )>, totalPrice: (
    { __typename: 'Price' }
    & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'currencyCode'>
  ), items: Array<(
    { __typename?: 'OrderItem' }
    & Pick<Types.TypeOrderItem, 'type'>
    & { payment: Types.Maybe<(
      { __typename?: 'Payment' }
      & Pick<Types.TypePayment, 'name'>
      & { mainImage: Types.Maybe<(
        { __typename?: 'Image' }
        & Pick<Types.TypeImage, 'url'>
      )> }
    )>, transport: Types.Maybe<(
      { __typename?: 'Transport' }
      & Pick<Types.TypeTransport, 'name'>
      & { mainImage: Types.Maybe<(
        { __typename?: 'Image' }
        & Pick<Types.TypeImage, 'url'>
      )> }
    )> }
  )> }
);

export const ListedOrderFragment = gql`
    fragment ListedOrderFragment on Order {
  currencyCode
  __typename
  uuid
  number
  creationDate
  productItems {
    ...OrderItemFragment
  }
  totalPrice {
    ...PriceFragment
  }
  isPaid
  hasExternalPayment
  hasPaymentInProcess
  status
  note
  items {
    type
    payment {
      name
      mainImage {
        url
      }
    }
    transport {
      name
      mainImage {
        url
      }
    }
  }
}
    ${OrderItemFragment}
${PriceFragment}`;