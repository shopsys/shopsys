// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { OrderItemFragment } from './OrderItemFragment.generated';
import { PriceFragment } from '../../prices/fragments/PriceFragment.generated';
export type TypeListedOrderFragment = { __typename: 'Order', uuid: string, number: string, creationDate: any, isPaid: boolean, hasExternalPayment: boolean, hasPaymentInProcess: boolean, status: string, note: string | null, productItems: Array<{ __typename: 'OrderItem', quantity: number, product: { __typename: 'MainVariant', name: string, isVisible: boolean, isSellingDenied: boolean, isInquiryType: boolean, isCurrentlyOutOfStock: boolean, link: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null } | { __typename: 'RegularProduct', name: string, isVisible: boolean, isSellingDenied: boolean, isInquiryType: boolean, isCurrentlyOutOfStock: boolean, link: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null } | { __typename: 'Variant', name: string, isVisible: boolean, isSellingDenied: boolean, isInquiryType: boolean, isCurrentlyOutOfStock: boolean, link: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null } | null }>, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, items: Array<{ __typename?: 'OrderItem', type: Types.TypeOrderItemTypeEnum, payment: { __typename?: 'Payment', name: string, mainImage: { __typename?: 'Image', url: string } | null } | null, transport: { __typename?: 'Transport', name: string, mainImage: { __typename?: 'Image', url: string } | null } | null }> };

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