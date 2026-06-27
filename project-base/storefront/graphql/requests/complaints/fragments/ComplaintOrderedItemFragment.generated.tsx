// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
export type TypeComplaintOrderedItemFragment = { __typename?: 'OrderItem', uuid: string, name: string, quantity: number, unit: string | null, totalPrice: { __typename?: 'Price', priceWithVat: string }, order: { __typename?: 'Order', uuid: string, number: string, creationDate: any }, product: { __typename?: 'MainVariant', isVisible: boolean, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null } | { __typename?: 'RegularProduct', isVisible: boolean, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null } | { __typename?: 'Variant', isVisible: boolean, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null } | null };

export const ComplaintOrderedItemFragment = gql`
    fragment ComplaintOrderedItemFragment on OrderItem {
  uuid
  name
  quantity
  unit
  totalPrice {
    priceWithVat
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