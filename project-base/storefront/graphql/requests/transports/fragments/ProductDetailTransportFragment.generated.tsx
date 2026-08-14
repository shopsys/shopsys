// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { PriceFragment } from '../../prices/fragments/PriceFragment.generated';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
/** One of the possible methods of the transport type */
export type TypeTransportTypeEnum =
  | 'common'
  | 'packetery'
  | 'personal_pickup';

export type TypeProductDetailTransportFragment = { __typename: 'Transport', uuid: string, name: string, description: string | null, expectedDeliveryDate: string | null, transportTypeCode: Types.TypeTransportTypeEnum, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null };

export const ProductDetailTransportFragment = gql`
    fragment ProductDetailTransportFragment on Transport {
  __typename
  uuid
  name
  description
  price(productUuid: $productUuid) {
    ...PriceFragment
  }
  mainImage {
    ...ImageFragment
  }
  expectedDeliveryDate(productUuid: $productUuid)
  transportTypeCode
}
    ${PriceFragment}
${ImageFragment}`;