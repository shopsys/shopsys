// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { OrderItemAdditionalServiceFragment } from '../../orders/fragments/OrderItemAdditionalServiceFragment.generated';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
import { FileFragment } from '../../files/fragments/FileFragment.generated';
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

export type TypeComplaintItemFragment = { uuid: string, quantity: number, description: string, catnum: string | null, productName: string, orderItem: { __typename: 'OrderItem', uuid: string, unit: string | null, totalPrice: { priceWithVat: string }, relatedItems: Array<{ __typename: 'OrderItem', deliveryDaysExtension: number | null, uuid: string, name: string, catnum: string | null, quantity: number, unit: string | null, type: Types.TypeOrderItemTypeEnum, mainImage: { __typename: 'Image', name: string | null, url: string } | null, unitPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } }> } | null, files: Array<{ __typename: 'File', anchorText: string, url: string, viewUrl: string | null, filesize: number | null, extension: string | null }> | null, product:
    | { slug: string, isVisible: boolean, mainImage: { __typename: 'Image', name: string | null, url: string } | null }
    | { slug: string, isVisible: boolean, mainImage: { __typename: 'Image', name: string | null, url: string } | null }
    | { slug: string, isVisible: boolean, mainImage: { __typename: 'Image', name: string | null, url: string } | null }
   | null };

export const ComplaintItemFragment = gql`
    fragment ComplaintItemFragment on ComplaintItem {
  uuid
  quantity
  description
  orderItem {
    __typename
    uuid
    unit
    totalPrice {
      priceWithVat
    }
    relatedItems {
      ...OrderItemAdditionalServiceFragment
      deliveryDaysExtension
    }
  }
  files {
    ...FileFragment
  }
  product {
    mainImage {
      ...ImageFragment
    }
    slug
    isVisible
  }
  catnum
  productName
}
    ${OrderItemAdditionalServiceFragment}
${FileFragment}
${ImageFragment}`;