// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
export type TypeOrderItemFragment = { __typename: 'OrderItem', quantity: number, product: { __typename: 'MainVariant', isVisible: boolean, isSellingDenied: boolean, isInquiryType: boolean, isCurrentlyOutOfStock: boolean, mainImage: { __typename: 'Image', name: string | null, url: string } | null } | { __typename: 'RegularProduct', isVisible: boolean, isSellingDenied: boolean, isInquiryType: boolean, isCurrentlyOutOfStock: boolean, mainImage: { __typename: 'Image', name: string | null, url: string } | null } | { __typename: 'Variant', isVisible: boolean, isSellingDenied: boolean, isInquiryType: boolean, isCurrentlyOutOfStock: boolean, mainImage: { __typename: 'Image', name: string | null, url: string } | null } | null };

export const OrderItemFragment = gql`
    fragment OrderItemFragment on OrderItem {
  __typename
  quantity
  product {
    __typename
    isVisible
    isSellingDenied
    isInquiryType
    isCurrentlyOutOfStock
    mainImage {
      ...ImageFragment
    }
  }
}
    ${ImageFragment}`;