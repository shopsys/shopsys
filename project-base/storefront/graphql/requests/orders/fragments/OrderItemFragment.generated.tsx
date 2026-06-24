// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
export type TypeOrderItemFragment = { __typename: 'OrderItem', quantity: number, product:
    | { __typename: 'MainVariant', name: string, isVisible: boolean, isSellingDenied: boolean, isInquiryType: boolean, isCurrentlyOutOfStock: boolean, link: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null }
    | { __typename: 'RegularProduct', name: string, isVisible: boolean, isSellingDenied: boolean, isInquiryType: boolean, isCurrentlyOutOfStock: boolean, link: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null }
    | { __typename: 'Variant', name: string, isVisible: boolean, isSellingDenied: boolean, isInquiryType: boolean, isCurrentlyOutOfStock: boolean, link: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null }
   | null };

export const OrderItemFragment = gql`
    fragment OrderItemFragment on OrderItem {
  __typename
  quantity
  product {
    __typename
    name
    isVisible
    isSellingDenied
    isInquiryType
    isCurrentlyOutOfStock
    link
    mainImage {
      ...ImageFragment
    }
  }
}
    ${ImageFragment}`;