// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
export type TypeComplaintOrderedItemFragment = (
  { __typename?: 'OrderItem' }
  & Pick<Types.TypeOrderItem, 'uuid' | 'name' | 'quantity' | 'unit'>
  & { totalPrice: (
    { __typename?: 'Price' }
    & Pick<Types.TypePrice, 'priceWithVat' | 'currencyCode'>
  ), order: (
    { __typename?: 'Order' }
    & Pick<Types.TypeOrder, 'uuid' | 'number' | 'creationDate'>
  ), product: Types.Maybe<(
    { __typename?: 'MainVariant' }
    & Pick<Types.TypeMainVariant, 'isVisible' | 'slug'>
    & { mainImage: Types.Maybe<(
      { __typename: 'Image' }
      & Pick<Types.TypeImage, 'name' | 'url'>
    )> }
  ) | (
    { __typename?: 'RegularProduct' }
    & Pick<Types.TypeRegularProduct, 'isVisible' | 'slug'>
    & { mainImage: Types.Maybe<(
      { __typename: 'Image' }
      & Pick<Types.TypeImage, 'name' | 'url'>
    )> }
  ) | (
    { __typename?: 'Variant' }
    & Pick<Types.TypeVariant, 'isVisible' | 'slug'>
    & { mainImage: Types.Maybe<(
      { __typename: 'Image' }
      & Pick<Types.TypeImage, 'name' | 'url'>
    )> }
  )> }
);

export const ComplaintOrderedItemFragment = gql`
    fragment ComplaintOrderedItemFragment on OrderItem {
  uuid
  name
  quantity
  unit
  totalPrice {
    priceWithVat
    currencyCode
  }
  order {
    uuid
    number
    creationDate
  }
  product {
    isVisible
    slug
    mainImage {
      ...ImageFragment
    }
  }
}
    ${ImageFragment}`;