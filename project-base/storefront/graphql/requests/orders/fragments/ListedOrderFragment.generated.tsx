// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { OrderItemFragment } from './OrderItemFragment.generated';
import { PriceFragment } from '../../prices/fragments/PriceFragment.generated';
/** One of possible types of the order item */
export type TypeOrderItemTypeEnum =
  | 'discount'
  | 'payment'
  | 'product'
  | 'productGift'
  | 'promotion'
  | 'rounding'
  | 'transport';

export type TypeListedOrderFragment = { __typename: 'Order', uuid: string, number: string, creationDate: string, isPaid: boolean, hasExternalPayment: boolean, hasPaymentInProcess: boolean, isAwaitingPayment: boolean, status: string, note: string | null, productItems: Array<{ __typename: 'OrderItem', quantity: number, product:
      | { __typename: 'MainVariant', name: string, isVisible: boolean, isSellingDenied: boolean, isInquiryType: boolean, isCurrentlyOutOfStock: boolean, link: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null }
      | { __typename: 'RegularProduct', name: string, isVisible: boolean, isSellingDenied: boolean, isInquiryType: boolean, isCurrentlyOutOfStock: boolean, link: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null }
      | { __typename: 'Variant', name: string, isVisible: boolean, isSellingDenied: boolean, isInquiryType: boolean, isCurrentlyOutOfStock: boolean, link: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null }
     | null }>, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, items: Array<{ type: Types.TypeOrderItemTypeEnum, payment: { name: string, mainImage: { url: string } | null } | null, transport: { name: string, mainImage: { url: string } | null } | null }> };

export const ListedOrderFragment = gql`
    fragment ListedOrderFragment on Order {
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
  isAwaitingPayment
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