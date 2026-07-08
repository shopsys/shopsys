// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
export type TypeOrderItemFragment = (
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
);

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