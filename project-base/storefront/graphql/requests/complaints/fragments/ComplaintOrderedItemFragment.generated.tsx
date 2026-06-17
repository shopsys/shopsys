// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
export type TypeComplaintOrderedItemFragment = { uuid: string, name: string, quantity: number, unit: string | null, totalPrice: { priceWithVat: string }, order: { uuid: string, number: string, creationDate: string }, product:
    | { isVisible: boolean, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null }
    | { isVisible: boolean, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null }
    | { isVisible: boolean, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null }
   | null };

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