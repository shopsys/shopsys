// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { BaseCustomerUserFragment } from './BaseCustomerUserFragment.generated';
import { LoginInfoFragment } from './LoginInfoFragment.generated';
export type TypeCurrentCustomerUserFragment_CurrentCompanyCustomerUser_ = (
  { __typename: 'CurrentCompanyCustomerUser' }
  & Pick<Types.TypeCurrentCompanyCustomerUser, 'companyName' | 'companyNumber' | 'companyTaxNumber' | 'uuid' | 'firstName' | 'lastName' | 'email' | 'telephone' | 'billingAddressUuid' | 'street' | 'city' | 'postcode' | 'newsletterSubscription' | 'pricingGroup' | 'hasPasswordSet' | 'roles'>
  & { loginInfo: (
    { __typename: 'LoginInfo' }
    & Pick<Types.TypeLoginInfo, 'externalId' | 'loginType'>
  ), telephoneData: Types.Maybe<(
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

export type TypeCurrentCustomerUserFragment_CurrentRegularCustomerUser_ = (
  { __typename: 'CurrentRegularCustomerUser' }
  & Pick<Types.TypeCurrentRegularCustomerUser, 'uuid' | 'firstName' | 'lastName' | 'email' | 'telephone' | 'billingAddressUuid' | 'street' | 'city' | 'postcode' | 'newsletterSubscription' | 'pricingGroup' | 'hasPasswordSet' | 'roles'>
  & { loginInfo: (
    { __typename: 'LoginInfo' }
    & Pick<Types.TypeLoginInfo, 'externalId' | 'loginType'>
  ), telephoneData: Types.Maybe<(
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

export type TypeCurrentCustomerUserFragment = TypeCurrentCustomerUserFragment_CurrentCompanyCustomerUser_ | TypeCurrentCustomerUserFragment_CurrentRegularCustomerUser_;

export const CurrentCustomerUserFragment = gql`
    fragment CurrentCustomerUserFragment on CurrentCustomerUser {
  ...BaseCustomerUserFragment
  ... on CurrentCompanyCustomerUser {
    companyName
    companyNumber
    companyTaxNumber
  }
  loginInfo {
    ...LoginInfoFragment
  }
}
    ${BaseCustomerUserFragment}
${LoginInfoFragment}`;