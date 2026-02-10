// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { OrderItemFragment } from './OrderItemFragment.generated';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
import { PriceFragment } from '../../prices/fragments/PriceFragment.generated';
export type TypeListedOrderFragment = { __typename: 'Order', uuid: string, number: string, creationDate: any, isPaid: boolean, hasExternalPayment: boolean, hasPaymentInProcess: boolean, status: string, note: string | null, productItems: Array<{ __typename: 'OrderItem', quantity: number, product: { __typename: 'MainVariant', isVisible: boolean, isSellingDenied: boolean, isInquiryType: boolean, isCurrentlyOutOfStock: boolean, mainImage: { __typename: 'Image', name: string | null, url: string } | null } | { __typename: 'RegularProduct', isVisible: boolean, isSellingDenied: boolean, isInquiryType: boolean, isCurrentlyOutOfStock: boolean, mainImage: { __typename: 'Image', name: string | null, url: string } | null } | { __typename: 'Variant', isVisible: boolean, isSellingDenied: boolean, isInquiryType: boolean, isCurrentlyOutOfStock: boolean, mainImage: { __typename: 'Image', name: string | null, url: string } | null } | null }>, transport: { __typename: 'Transport', name: string, mainImage: { __typename: 'Image', url: string, name: string | null } | null }, payment: { __typename: 'Payment', name: string, type: Types.TypePaymentTypeEnum, mainImage: { __typename?: 'Image', url: string } | null }, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } };

export const ListedOrderFragment = gql`
    fragment ListedOrderFragment on Order {
  __typename
  uuid
  number
  creationDate
  productItems {
    ...OrderItemFragment
  }
  transport {
    __typename
    name
    mainImage {
      ...ImageFragment
    }
    mainImage {
      url
    }
  }
  payment {
    __typename
    name
    type
    mainImage {
      url
    }
  }
  totalPrice {
    ...PriceFragment
  }
  isPaid
  hasExternalPayment
  hasPaymentInProcess
  status
  note
}
    ${OrderItemFragment}
${ImageFragment}
${PriceFragment}`;