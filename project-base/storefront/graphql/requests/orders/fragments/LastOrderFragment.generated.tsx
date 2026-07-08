// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { CountryFragment } from '../../countries/fragments/CountryFragment.generated';
export type TypeLastOrderFragment = (
  { __typename: 'Order' }
  & Pick<Types.TypeOrder, 'pickupPlaceIdentifier' | 'deliveryStreet' | 'deliveryCity' | 'deliveryPostcode'>
  & { deliveryCountry: Types.Maybe<(
    { __typename: 'Country' }
    & Pick<Types.TypeCountry, 'name' | 'code'>
  )>, items: Array<(
    { __typename?: 'OrderItem' }
    & Pick<Types.TypeOrderItem, 'type'>
    & { payment: Types.Maybe<(
      { __typename?: 'Payment' }
      & Pick<Types.TypePayment, 'uuid'>
    )>, transport: Types.Maybe<(
      { __typename?: 'Transport' }
      & Pick<Types.TypeTransport, 'uuid' | 'transportTypeCode'>
    )> }
  )> }
);

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