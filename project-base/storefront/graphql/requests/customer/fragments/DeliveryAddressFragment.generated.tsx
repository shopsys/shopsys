// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { CountryFragment } from '../../countries/fragments/CountryFragment.generated';
export type TypeDeliveryAddressFragment = { __typename: 'DeliveryAddress', uuid: string, companyName: string | null, street: string | null, city: string | null, postcode: string | null, telephone: string | null, firstName: string | null, lastName: string | null, telephoneData: { __typename?: 'PhoneData', prefix: string | null, countryCode: string | null, number: string } | null, country: { __typename: 'Country', name: string, code: string } | null };

export const DeliveryAddressFragment = gql`
    fragment DeliveryAddressFragment on DeliveryAddress {
  __typename
  uuid
  companyName
  street
  city
  postcode
  telephone
  telephoneData {
    prefix
    countryCode
    number
  }
  country {
    ...CountryFragment
  }
  firstName
  lastName
}
    ${CountryFragment}`;