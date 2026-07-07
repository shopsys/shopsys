// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { PriceFragment } from '../../prices/fragments/PriceFragment.generated';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
import { SimplePaymentFragment } from '../../payments/fragments/SimplePaymentFragment.generated';
import { ListedStoreConnectionFragment } from '../../stores/fragments/ListedStoreConnectionFragment.generated';
/** One of the possible methods of the payment type */
export type TypePaymentTypeEnum =
  | 'bankTransfer'
  | 'basic'
  | 'goPay';

/** Status of store opening */
export type TypeStoreOpeningStatusEnum =
  /** Store is currently closed */
  | 'CLOSED'
  /** Store will be closed soon */
  | 'CLOSED_SOON'
  /** Store is currently opened */
  | 'OPEN'
  /** Store will be opened soon */
  | 'OPEN_SOON';

/** One of the possible methods of the transport type */
export type TypeTransportTypeEnum =
  | 'common'
  | 'packetery'
  | 'personal_pickup';

export type TypeTransportWithAvailablePaymentsAndStoresFragment = { __typename: 'Transport', uuid: string, name: string, description: string | null, daysUntilDelivery: number, transportTypeCode: Types.TypeTransportTypeEnum, isPersonalPickup: boolean, vatPercent: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, payments: Array<{ __typename: 'Payment', uuid: string, name: string, description: string | null, instructions: string | null, type: Types.TypePaymentTypeEnum, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, goPayPaymentMethod: { __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string } | null }>, stores: { __typename: 'StoreConnection', edges: Array<{ __typename: 'StoreEdge', node: { __typename: 'Store', slug: string, name: string, description: string | null, latitude: string | null, longitude: string | null, street: string, postcode: string, city: string, distance: number | null, email: string | null, phone: string | null, specialMessage: string | null, identifier: string, openingHours: { status: Types.TypeStoreOpeningStatusEnum, dayOfWeek: number, openingHoursOfDays: Array<{ date: string, dayOfWeek: number, openingHoursRanges: Array<{ openingTime: string, closingTime: string }> }> }, country: { __typename: 'Country', name: string, code: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null } | null } | null> | null } | null };

export const TransportWithAvailablePaymentsAndStoresFragment = gql`
    fragment TransportWithAvailablePaymentsAndStoresFragment on Transport {
  __typename
  uuid
  name
  description
  price {
    ...PriceFragment
  }
  mainImage {
    ...ImageFragment
  }
  payments {
    ...SimplePaymentFragment
  }
  daysUntilDelivery
  stores {
    ...ListedStoreConnectionFragment
  }
  transportTypeCode
  isPersonalPickup
  vatPercent
}
    ${PriceFragment}
${ImageFragment}
${SimplePaymentFragment}
${ListedStoreConnectionFragment}`;