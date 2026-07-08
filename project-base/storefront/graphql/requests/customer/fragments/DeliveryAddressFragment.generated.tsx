// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { CountryFragment } from '../../countries/fragments/CountryFragment.generated';
export type TypeDeliveryAddressFragment = (
  { __typename: 'DeliveryAddress' }
  & Pick<Types.TypeDeliveryAddress, 'uuid' | 'companyName' | 'street' | 'city' | 'postcode' | 'telephone' | 'firstName' | 'lastName'>
  & { telephoneData: Types.Maybe<(
    { __typename?: 'PhoneData' }
    & Pick<Types.TypePhoneData, 'prefix' | 'countryCode' | 'number'>
  )>, country: Types.Maybe<(
    { __typename: 'Country' }
    & Pick<Types.TypeCountry, 'name' | 'code'>
  )> }
);

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