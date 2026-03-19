// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { CountryFragment } from '../../countries/fragments/CountryFragment.generated';
export type TypeLastOrderFragment = { __typename: 'Order', pickupPlaceIdentifier: string | null, deliveryStreet: string | null, deliveryCity: string | null, deliveryPostcode: string | null, deliveryCountry: { __typename: 'Country', name: string, code: string } | null, items: Array<{ __typename?: 'OrderItem', type: Types.TypeOrderItemTypeEnum, payment: { __typename?: 'Payment', uuid: string } | null, transport: { __typename?: 'Transport', uuid: string, transportTypeCode: Types.TypeTransportTypeEnum } | null }> };

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