// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
import { PriceFragment } from '../../prices/fragments/PriceFragment.generated';
/** One of the possible methods of the transport type */
export type TypeTransportTypeEnum =
  | 'common'
  | 'packetery'
  | 'personal_pickup';

export type TypeProductDeliveryOptionFragment = { __typename: 'ProductDeliveryOption', expectedDeliveryDate: string | null, transport: { __typename: 'Transport', uuid: string, name: string, description: string | null, transportTypeCode: Types.TypeTransportTypeEnum, mainImage: { __typename: 'Image', name: string | null, url: string } | null }, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } };

export const ProductDeliveryOptionFragment = gql`
    fragment ProductDeliveryOptionFragment on ProductDeliveryOption {
  __typename
  transport {
    __typename
    uuid
    name
    description
    mainImage {
      ...ImageFragment
    }
    transportTypeCode
  }
  price {
    ...PriceFragment
  }
  expectedDeliveryDate
}
    ${ImageFragment}
${PriceFragment}`;