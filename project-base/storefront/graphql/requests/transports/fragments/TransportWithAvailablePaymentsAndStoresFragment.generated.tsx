// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { PriceFragment } from '../../prices/fragments/PriceFragment.generated';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
import { SimplePaymentFragment } from '../../payments/fragments/SimplePaymentFragment.generated';
import { ListedStoreConnectionFragment } from '../../stores/fragments/ListedStoreConnectionFragment.generated';
export type TypeTransportWithAvailablePaymentsAndStoresFragment = (
  { __typename: 'Transport' }
  & Pick<Types.TypeTransport, 'uuid' | 'name' | 'description' | 'daysUntilDelivery' | 'transportTypeCode' | 'isPersonalPickup' | 'vatPercent'>
  & { price: (
    { __typename: 'Price' }
    & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount'>
  ), mainImage: Types.Maybe<(
    { __typename: 'Image' }
    & Pick<Types.TypeImage, 'name' | 'url'>
  )>, payments: Array<(
    { __typename: 'Payment' }
    & Pick<Types.TypePayment, 'uuid' | 'name' | 'description' | 'instructions' | 'type'>
    & { price: (
      { __typename: 'Price' }
      & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount'>
    ), mainImage: Types.Maybe<(
      { __typename: 'Image' }
      & Pick<Types.TypeImage, 'name' | 'url'>
    )>, goPayPaymentMethod: Types.Maybe<(
      { __typename: 'GoPayPaymentMethod' }
      & Pick<Types.TypeGoPayPaymentMethod, 'identifier' | 'name' | 'paymentGroup'>
    )> }
  )>, stores: Types.Maybe<(
    { __typename: 'StoreConnection' }
    & { edges: Types.Maybe<Array<Types.Maybe<(
      { __typename: 'StoreEdge' }
      & { node: Types.Maybe<(
        { __typename: 'Store' }
        & Pick<Types.TypeStore, 'slug' | 'name' | 'description' | 'latitude' | 'longitude' | 'street' | 'postcode' | 'city' | 'distance' | 'email' | 'phone' | 'specialMessage'>
        & { identifier: Types.TypeStore['uuid'] }
        & { openingHours: (
          { __typename?: 'OpeningHours' }
          & Pick<Types.TypeOpeningHours, 'status' | 'dayOfWeek'>
          & { openingHoursOfDays: Array<(
            { __typename?: 'OpeningHoursOfDay' }
            & Pick<Types.TypeOpeningHoursOfDay, 'date' | 'dayOfWeek'>
            & { openingHoursRanges: Array<(
              { __typename?: 'OpeningHoursRange' }
              & Pick<Types.TypeOpeningHoursRange, 'openingTime' | 'closingTime'>
            )> }
          )> }
        ), country: (
          { __typename: 'Country' }
          & Pick<Types.TypeCountry, 'name' | 'code'>
        ), mainImage: Types.Maybe<(
          { __typename: 'Image' }
          & Pick<Types.TypeImage, 'name' | 'url'>
        )> }
      )> }
    )>>> }
  )> }
);

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