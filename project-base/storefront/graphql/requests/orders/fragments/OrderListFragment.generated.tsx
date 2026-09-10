// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { PageInfoFragment } from '../../pageInfo/fragments/PageInfoFragment.generated';
import { ListedOrderFragment } from './ListedOrderFragment.generated';
/** One of possible types of the order item */
export type TypeOrderItemTypeEnum =
  | 'additionalService'
  | 'discount'
  | 'payment'
  | 'product'
  | 'productGift'
  | 'promotion'
  | 'rounding'
  | 'transport';

export type TypeOrderListFragment = { __typename: 'OrderConnection', totalCount: number, pageInfo: { __typename: 'PageInfo', hasNextPage: boolean, hasPreviousPage: boolean, endCursor: string | null }, edges: Array<{ __typename: 'OrderEdge', cursor: string, node: { __typename: 'Order', uuid: string, number: string, creationDate: string, isPaid: boolean, hasExternalPayment: boolean, hasPaymentInProcess: boolean, status: string, note: string | null, productItems: Array<{ __typename: 'OrderItem', quantity: number, product:
          | { __typename: 'MainVariant', name: string, isVisible: boolean, isSellingDenied: boolean, isInquiryType: boolean, isCurrentlyOutOfStock: boolean, link: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null }
          | { __typename: 'RegularProduct', name: string, isVisible: boolean, isSellingDenied: boolean, isInquiryType: boolean, isCurrentlyOutOfStock: boolean, link: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null }
          | { __typename: 'Variant', name: string, isVisible: boolean, isSellingDenied: boolean, isInquiryType: boolean, isCurrentlyOutOfStock: boolean, link: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null }
         | null }>, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, items: Array<{ type: Types.TypeOrderItemTypeEnum, payment: { name: string, mainImage: { url: string } | null } | null, transport: { name: string, mainImage: { url: string } | null } | null }> } | null } | null> | null };

export const OrderListFragment = gql`
    fragment OrderListFragment on OrderConnection {
  __typename
  totalCount
  pageInfo {
    ...PageInfoFragment
  }
  edges {
    __typename
    node {
      ...ListedOrderFragment
    }
    cursor
  }
}
    ${PageInfoFragment}
${ListedOrderFragment}`;