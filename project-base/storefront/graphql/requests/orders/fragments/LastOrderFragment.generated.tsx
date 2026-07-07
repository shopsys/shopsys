// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { CountryFragment } from '../../countries/fragments/CountryFragment.generated';
/** One of possible types of the order item */
export type TypeOrderItemTypeEnum =
  | 'discount'
  | 'payment'
  | 'product'
  | 'productGift'
  | 'promotion'
  | 'rounding'
  | 'transport';

/** One of the possible methods of the transport type */
export type TypeTransportTypeEnum =
  | 'common'
  | 'packetery'
  | 'personal_pickup';

export type TypeLastOrderFragment = { __typename: 'Order', pickupPlaceIdentifier: string | null, deliveryStreet: string | null, deliveryCity: string | null, deliveryPostcode: string | null, deliveryCountry: { __typename: 'Country', name: string, code: string } | null, items: Array<{ type: Types.TypeOrderItemTypeEnum, payment: { uuid: string } | null, transport: { uuid: string, transportTypeCode: Types.TypeTransportTypeEnum } | null }> };

export const LastOrderFragment = gql`
    fragment LastOrderFragment on Order {
  __typename
  pickupPlaceIdentifier
  deliveryStreet
  deliveryCity
  deliveryPostcode
  deliveryCountry {
    ...CountryFragment
  }
  items {
    type
    payment {
      uuid
    }
    transport {
      uuid
      transportTypeCode
    }
  }
}
    ${CountryFragment}`;