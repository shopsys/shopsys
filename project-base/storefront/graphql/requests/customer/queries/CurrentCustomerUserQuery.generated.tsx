// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { CurrentCustomerUserFragment } from '../fragments/CurrentCustomerUserFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeCurrentCustomerUserQueryVariables = Types.Exact<{ [key: string]: never; }>;


export type TypeCurrentCustomerUserQuery = (
  { __typename?: 'Query' }
  & { currentCustomerUser: Types.Maybe<(
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
  ) | (
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
  )> }
);


export const CurrentCustomerUserQueryDocument = gql`
    query CurrentCustomerUserQuery {
  currentCustomerUser {
    ...CurrentCustomerUserFragment
  }
}
    ${CurrentCustomerUserFragment}`;

export function useCurrentCustomerUserQuery(options?: Omit<Urql.UseQueryArgs<TypeCurrentCustomerUserQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeCurrentCustomerUserQuery, TypeCurrentCustomerUserQueryVariables>({ query: CurrentCustomerUserQueryDocument, ...options });
};