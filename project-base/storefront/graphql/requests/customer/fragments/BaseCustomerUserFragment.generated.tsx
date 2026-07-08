// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { CountryFragment } from '../../countries/fragments/CountryFragment.generated';
import { DeliveryAddressFragment } from './DeliveryAddressFragment.generated';
import { CustomerUserRoleGroupFragment } from './CustomerUserRoleGroupGragment.generated';
import { SalesRepresentativeFragment } from './SalesRepresentativeFragment.generated';
export type TypeBaseCustomerUserFragment_CompanyCustomerUser_ = (
  { __typename: 'CompanyCustomerUser' }
  & Pick<Types.TypeCompanyCustomerUser, 'companyName' | 'companyNumber' | 'companyTaxNumber' | 'uuid' | 'firstName' | 'lastName' | 'email' | 'telephone' | 'billingAddressUuid' | 'street' | 'city' | 'postcode' | 'newsletterSubscription' | 'pricingGroup' | 'hasPasswordSet' | 'roles'>
  & { telephoneData: Types.Maybe<(
    { __typename?: 'PhoneData' }
    & Pick<Types.TypePhoneData, 'prefix' | 'countryCode' | 'number'>
  )>, country: Types.Maybe<(
    { __typename: 'Country' }
    & Pick<Types.TypeCountry, 'name' | 'code'>
  )>, defaultDeliveryAddress: Types.Maybe<(
    { __typename: 'DeliveryAddress' }
    & Pick<Types.TypeDeliveryAddress, 'uuid' | 'companyName' | 'street' | 'city' | 'postcode' | 'telephone' | 'firstName' | 'lastName'>
    & { telephoneData: Types.Maybe<(
      { __typename?: 'PhoneData' }
      & Pick<Types.TypePhoneData, 'prefix' | 'countryCode' | 'number'>
    )>, country: Types.Maybe<(
      { __typename: 'Country' }
      & Pick<Types.TypeCountry, 'name' | 'code'>
    )> }
  )>, deliveryAddresses: Array<(
    { __typename: 'DeliveryAddress' }
    & Pick<Types.TypeDeliveryAddress, 'uuid' | 'companyName' | 'street' | 'city' | 'postcode' | 'telephone' | 'firstName' | 'lastName'>
    & { telephoneData: Types.Maybe<(
      { __typename?: 'PhoneData' }
      & Pick<Types.TypePhoneData, 'prefix' | 'countryCode' | 'number'>
    )>, country: Types.Maybe<(
      { __typename: 'Country' }
      & Pick<Types.TypeCountry, 'name' | 'code'>
    )> }
  )>, roleGroup: (
    { __typename: 'CustomerUserRoleGroup' }
    & Pick<Types.TypeCustomerUserRoleGroup, 'uuid' | 'name'>
  ), salesRepresentative: Types.Maybe<(
    { __typename: 'SalesRepresentative' }
    & Pick<Types.TypeSalesRepresentative, 'email' | 'firstName' | 'lastName' | 'telephone' | 'uuid'>
    & { image: Types.Maybe<(
      { __typename?: 'Image' }
      & Pick<Types.TypeImage, 'url' | 'name'>
    )>, telephoneData: Types.Maybe<(
      { __typename?: 'PhoneData' }
      & Pick<Types.TypePhoneData, 'prefix' | 'countryCode' | 'number'>
    )> }
  )> }
);

export type TypeBaseCustomerUserFragment_CurrentCompanyCustomerUser_ = (
  { __typename: 'CurrentCompanyCustomerUser' }
  & Pick<Types.TypeCurrentCompanyCustomerUser, 'uuid' | 'firstName' | 'lastName' | 'email' | 'telephone' | 'billingAddressUuid' | 'street' | 'city' | 'postcode' | 'newsletterSubscription' | 'pricingGroup' | 'hasPasswordSet' | 'roles'>
  & { telephoneData: Types.Maybe<(
    { __typename?: 'PhoneData' }
    & Pick<Types.TypePhoneData, 'prefix' | 'countryCode' | 'number'>
  )>, country: Types.Maybe<(
    { __typename: 'Country' }
    & Pick<Types.TypeCountry, 'name' | 'code'>
  )>, defaultDeliveryAddress: Types.Maybe<(
    { __typename: 'DeliveryAddress' }
    & Pick<Types.TypeDeliveryAddress, 'uuid' | 'companyName' | 'street' | 'city' | 'postcode' | 'telephone' | 'firstName' | 'lastName'>
    & { telephoneData: Types.Maybe<(
      { __typename?: 'PhoneData' }
      & Pick<Types.TypePhoneData, 'prefix' | 'countryCode' | 'number'>
    )>, country: Types.Maybe<(
      { __typename: 'Country' }
      & Pick<Types.TypeCountry, 'name' | 'code'>
    )> }
  )>, deliveryAddresses: Array<(
    { __typename: 'DeliveryAddress' }
    & Pick<Types.TypeDeliveryAddress, 'uuid' | 'companyName' | 'street' | 'city' | 'postcode' | 'telephone' | 'firstName' | 'lastName'>
    & { telephoneData: Types.Maybe<(
      { __typename?: 'PhoneData' }
      & Pick<Types.TypePhoneData, 'prefix' | 'countryCode' | 'number'>
    )>, country: Types.Maybe<(
      { __typename: 'Country' }
      & Pick<Types.TypeCountry, 'name' | 'code'>
    )> }
  )>, roleGroup: (
    { __typename: 'CustomerUserRoleGroup' }
    & Pick<Types.TypeCustomerUserRoleGroup, 'uuid' | 'name'>
  ), salesRepresentative: Types.Maybe<(
    { __typename: 'SalesRepresentative' }
    & Pick<Types.TypeSalesRepresentative, 'email' | 'firstName' | 'lastName' | 'telephone' | 'uuid'>
    & { image: Types.Maybe<(
      { __typename?: 'Image' }
      & Pick<Types.TypeImage, 'url' | 'name'>
    )>, telephoneData: Types.Maybe<(
      { __typename?: 'PhoneData' }
      & Pick<Types.TypePhoneData, 'prefix' | 'countryCode' | 'number'>
    )> }
  )> }
);

export type TypeBaseCustomerUserFragment_CurrentRegularCustomerUser_ = (
  { __typename: 'CurrentRegularCustomerUser' }
  & Pick<Types.TypeCurrentRegularCustomerUser, 'uuid' | 'firstName' | 'lastName' | 'email' | 'telephone' | 'billingAddressUuid' | 'street' | 'city' | 'postcode' | 'newsletterSubscription' | 'pricingGroup' | 'hasPasswordSet' | 'roles'>
  & { telephoneData: Types.Maybe<(
    { __typename?: 'PhoneData' }
    & Pick<Types.TypePhoneData, 'prefix' | 'countryCode' | 'number'>
  )>, country: Types.Maybe<(
    { __typename: 'Country' }
    & Pick<Types.TypeCountry, 'name' | 'code'>
  )>, defaultDeliveryAddress: Types.Maybe<(
    { __typename: 'DeliveryAddress' }
    & Pick<Types.TypeDeliveryAddress, 'uuid' | 'companyName' | 'street' | 'city' | 'postcode' | 'telephone' | 'firstName' | 'lastName'>
    & { telephoneData: Types.Maybe<(
      { __typename?: 'PhoneData' }
      & Pick<Types.TypePhoneData, 'prefix' | 'countryCode' | 'number'>
    )>, country: Types.Maybe<(
      { __typename: 'Country' }
      & Pick<Types.TypeCountry, 'name' | 'code'>
    )> }
  )>, deliveryAddresses: Array<(
    { __typename: 'DeliveryAddress' }
    & Pick<Types.TypeDeliveryAddress, 'uuid' | 'companyName' | 'street' | 'city' | 'postcode' | 'telephone' | 'firstName' | 'lastName'>
    & { telephoneData: Types.Maybe<(
      { __typename?: 'PhoneData' }
      & Pick<Types.TypePhoneData, 'prefix' | 'countryCode' | 'number'>
    )>, country: Types.Maybe<(
      { __typename: 'Country' }
      & Pick<Types.TypeCountry, 'name' | 'code'>
    )> }
  )>, roleGroup: (
    { __typename: 'CustomerUserRoleGroup' }
    & Pick<Types.TypeCustomerUserRoleGroup, 'uuid' | 'name'>
  ), salesRepresentative: Types.Maybe<(
    { __typename: 'SalesRepresentative' }
    & Pick<Types.TypeSalesRepresentative, 'email' | 'firstName' | 'lastName' | 'telephone' | 'uuid'>
    & { image: Types.Maybe<(
      { __typename?: 'Image' }
      & Pick<Types.TypeImage, 'url' | 'name'>
    )>, telephoneData: Types.Maybe<(
      { __typename?: 'PhoneData' }
      & Pick<Types.TypePhoneData, 'prefix' | 'countryCode' | 'number'>
    )> }
  )> }
);

export type TypeBaseCustomerUserFragment_RegularCustomerUser_ = (
  { __typename: 'RegularCustomerUser' }
  & Pick<Types.TypeRegularCustomerUser, 'uuid' | 'firstName' | 'lastName' | 'email' | 'telephone' | 'billingAddressUuid' | 'street' | 'city' | 'postcode' | 'newsletterSubscription' | 'pricingGroup' | 'hasPasswordSet' | 'roles'>
  & { telephoneData: Types.Maybe<(
    { __typename?: 'PhoneData' }
    & Pick<Types.TypePhoneData, 'prefix' | 'countryCode' | 'number'>
  )>, country: Types.Maybe<(
    { __typename: 'Country' }
    & Pick<Types.TypeCountry, 'name' | 'code'>
  )>, defaultDeliveryAddress: Types.Maybe<(
    { __typename: 'DeliveryAddress' }
    & Pick<Types.TypeDeliveryAddress, 'uuid' | 'companyName' | 'street' | 'city' | 'postcode' | 'telephone' | 'firstName' | 'lastName'>
    & { telephoneData: Types.Maybe<(
      { __typename?: 'PhoneData' }
      & Pick<Types.TypePhoneData, 'prefix' | 'countryCode' | 'number'>
    )>, country: Types.Maybe<(
      { __typename: 'Country' }
      & Pick<Types.TypeCountry, 'name' | 'code'>
    )> }
  )>, deliveryAddresses: Array<(
    { __typename: 'DeliveryAddress' }
    & Pick<Types.TypeDeliveryAddress, 'uuid' | 'companyName' | 'street' | 'city' | 'postcode' | 'telephone' | 'firstName' | 'lastName'>
    & { telephoneData: Types.Maybe<(
      { __typename?: 'PhoneData' }
      & Pick<Types.TypePhoneData, 'prefix' | 'countryCode' | 'number'>
    )>, country: Types.Maybe<(
      { __typename: 'Country' }
      & Pick<Types.TypeCountry, 'name' | 'code'>
    )> }
  )>, roleGroup: (
    { __typename: 'CustomerUserRoleGroup' }
    & Pick<Types.TypeCustomerUserRoleGroup, 'uuid' | 'name'>
  ), salesRepresentative: Types.Maybe<(
    { __typename: 'SalesRepresentative' }
    & Pick<Types.TypeSalesRepresentative, 'email' | 'firstName' | 'lastName' | 'telephone' | 'uuid'>
    & { image: Types.Maybe<(
      { __typename?: 'Image' }
      & Pick<Types.TypeImage, 'url' | 'name'>
    )>, telephoneData: Types.Maybe<(
      { __typename?: 'PhoneData' }
      & Pick<Types.TypePhoneData, 'prefix' | 'countryCode' | 'number'>
    )> }
  )> }
);

export type TypeBaseCustomerUserFragment = TypeBaseCustomerUserFragment_CompanyCustomerUser_ | TypeBaseCustomerUserFragment_CurrentCompanyCustomerUser_ | TypeBaseCustomerUserFragment_CurrentRegularCustomerUser_ | TypeBaseCustomerUserFragment_RegularCustomerUser_;

export const BaseCustomerUserFragment = gql`
    fragment BaseCustomerUserFragment on BaseCustomerUser {
  __typename
  uuid
  firstName
  lastName
  email
  telephone
  telephoneData {
    prefix
    countryCode
    number
  }
  billingAddressUuid
  street
  city
  postcode
  country {
    ...CountryFragment
  }
  newsletterSubscription
  defaultDeliveryAddress {
    ...DeliveryAddressFragment
  }
  deliveryAddresses {
    ...DeliveryAddressFragment
  }
  ... on CompanyCustomerUser {
    companyName
    companyNumber
    companyTaxNumber
  }
  pricingGroup
  hasPasswordSet
  roles
  roleGroup {
    ...CustomerUserRoleGroupFragment
  }
  salesRepresentative {
    ...SalesRepresentativeFragment
  }
}
    ${CountryFragment}
${DeliveryAddressFragment}
${CustomerUserRoleGroupFragment}
${SalesRepresentativeFragment}`;