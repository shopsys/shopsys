// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { SimpleTransportFragment } from '../../transports/fragments/SimpleTransportFragment.generated';
import { SimplePaymentFragment } from '../../payments/fragments/SimplePaymentFragment.generated';
import { CountryFragment } from '../../countries/fragments/CountryFragment.generated';
export type TypeLastOrderFragment = { __typename: 'Order', pickupPlaceIdentifier: string | null, deliveryStreet: string | null, deliveryCity: string | null, deliveryPostcode: string | null, transport: { __typename: 'Transport', uuid: string, name: string, description: string | null, transportTypeCode: Types.TypeTransportTypeEnum }, payment: { __typename: 'Payment', uuid: string, name: string, description: string | null, instructions: string | null, type: Types.TypePaymentTypeEnum, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, goPayPaymentMethod: { __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string } | null }, deliveryCountry: { __typename: 'Country', name: string, code: string } | null };

export const LastOrderFragment = gql`
    fragment LastOrderFragment on Order {
  __typename
  transport {
    ...SimpleTransportFragment
  }
  payment {
    ...SimplePaymentFragment
  }
  pickupPlaceIdentifier
  deliveryStreet
  deliveryCity
  deliveryPostcode
  deliveryCountry {
    ...CountryFragment
  }
}
    ${SimpleTransportFragment}
${SimplePaymentFragment}
${CountryFragment}`;