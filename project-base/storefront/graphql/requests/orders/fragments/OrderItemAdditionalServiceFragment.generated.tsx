// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
import { PriceFragment } from '../../prices/fragments/PriceFragment.generated';
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

export type TypeOrderItemAdditionalServiceFragment = { __typename: 'OrderItem', uuid: string, name: string, catnum: string | null, quantity: number, unit: string | null, type: Types.TypeOrderItemTypeEnum, mainImage: { __typename: 'Image', name: string | null, url: string } | null, unitPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } };

export const OrderItemAdditionalServiceFragment = gql`
    fragment OrderItemAdditionalServiceFragment on OrderItem {
  __typename
  uuid
  name
  catnum
  quantity
  unit
  type
  mainImage {
    ...ImageFragment
  }
  unitPrice {
    ...PriceFragment
  }
  totalPrice {
    ...PriceFragment
  }
}
    ${ImageFragment}
${PriceFragment}`;