// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
/** One of possible product types */
export type TypeProductTypeEnum =
  /** Basic product */
  | 'BASIC'
  /** Gift voucher delivered by email after the order is paid */
  | 'ELECTRONIC_GIFT_VOUCHER'
  /** Product with inquiry form instead of add to cart button */
  | 'INQUIRY'
  /** Gift voucher delivered printed as a regular product */
  | 'PRINTED_GIFT_VOUCHER';

export type TypeComplaintOrderedItemFragment = { uuid: string, name: string, quantity: number, unit: string | null, totalPrice: { priceWithVat: string }, order: { uuid: string, number: string, creationDate: string }, product:
    | { isVisible: boolean, slug: string, productType: Types.TypeProductTypeEnum, mainImage: { __typename: 'Image', name: string | null, url: string } | null }
    | { isVisible: boolean, slug: string, productType: Types.TypeProductTypeEnum, mainImage: { __typename: 'Image', name: string | null, url: string } | null }
    | { isVisible: boolean, slug: string, productType: Types.TypeProductTypeEnum, mainImage: { __typename: 'Image', name: string | null, url: string } | null }
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
    productType
    mainImage {
      ...ImageFragment
    }
  }
}
    ${ImageFragment}`;