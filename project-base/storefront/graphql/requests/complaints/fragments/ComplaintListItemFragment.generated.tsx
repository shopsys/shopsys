// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeComplaintListItemFragment = (
  { __typename?: 'Complaint' }
  & Pick<Types.TypeComplaint, 'uuid' | 'number' | 'createdAt' | 'status'>
  & { resolution: (
    { __typename?: 'ComplaintResolution' }
    & Pick<Types.TypeComplaintResolution, 'name'>
  ), items: Array<(
    { __typename?: 'ComplaintItem' }
    & Pick<Types.TypeComplaintItem, 'uuid' | 'quantity' | 'productName'>
    & { product: Types.Maybe<(
      { __typename?: 'MainVariant' }
      & Pick<Types.TypeMainVariant, 'slug' | 'isVisible'>
      & { mainImage: Types.Maybe<(
        { __typename?: 'Image' }
        & Pick<Types.TypeImage, 'name' | 'url'>
      )> }
    ) | (
      { __typename?: 'RegularProduct' }
      & Pick<Types.TypeRegularProduct, 'slug' | 'isVisible'>
      & { mainImage: Types.Maybe<(
        { __typename?: 'Image' }
        & Pick<Types.TypeImage, 'name' | 'url'>
      )> }
    ) | (
      { __typename?: 'Variant' }
      & Pick<Types.TypeVariant, 'slug' | 'isVisible'>
      & { mainImage: Types.Maybe<(
        { __typename?: 'Image' }
        & Pick<Types.TypeImage, 'name' | 'url'>
      )> }
    )> }
  )> }
);

export const ComplaintListItemFragment = gql`
    fragment ComplaintListItemFragment on Complaint {
  uuid
  number
  createdAt
  status
  resolution {
    name
  }
  items {
    uuid
    quantity
    productName
    product {
      slug
      isVisible
      mainImage {
        name
        url
      }
    }
  }
}
    `;