// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { CountryFragment } from '../../countries/fragments/CountryFragment.generated';
import { DeliveryAddressFragment } from './DeliveryAddressFragment.generated';
import { CustomerUserRoleGroupFragment } from './CustomerUserRoleGroupGragment.generated';
import { SalesRepresentativeFragment } from './SalesRepresentativeFragment.generated';
/** Available customer user roles */
export type TypeCustomerUserRoleEnum =
  | 'ROLE_API_ALL'
  | 'ROLE_API_CART_AND_ORDER_CREATION'
  | 'ROLE_API_COMPANY_COMPLAINTS_VIEW'
  | 'ROLE_API_COMPANY_ORDERS_VIEW'
  | 'ROLE_API_COMPLAINT_CREATION'
  | 'ROLE_API_CUSTOMER_SEES_PRICES'
  | 'ROLE_API_CUSTOMER_SELF_MANAGE'
  | 'ROLE_API_MANAGE_COMPANY_DATA'
  | 'ROLE_API_MANAGE_CUSTOMERS';

export type TypeBaseCustomerUserFragment_CompanyCustomerUser = { __typename: 'CompanyCustomerUser', companyName: string | null, companyNumber: string | null, companyTaxNumber: string | null, uuid: string, firstName: string | null, lastName: string | null, email: string, telephone: string | null, billingAddressUuid: string, street: string | null, city: string | null, postcode: string | null, newsletterSubscription: boolean, pricingGroup: string, hasPasswordSet: boolean, roles: Array<Types.TypeCustomerUserRoleEnum>, telephoneData: { prefix: string | null, countryCode: string | null, number: string } | null, country: { __typename: 'Country', name: string, code: string } | null, defaultDeliveryAddress: { __typename: 'DeliveryAddress', uuid: string, companyName: string | null, street: string | null, city: string | null, postcode: string | null, telephone: string | null, firstName: string | null, lastName: string | null, telephoneData: { prefix: string | null, countryCode: string | null, number: string } | null, country: { __typename: 'Country', name: string, code: string } | null } | null, deliveryAddresses: Array<{ __typename: 'DeliveryAddress', uuid: string, companyName: string | null, street: string | null, city: string | null, postcode: string | null, telephone: string | null, firstName: string | null, lastName: string | null, telephoneData: { prefix: string | null, countryCode: string | null, number: string } | null, country: { __typename: 'Country', name: string, code: string } | null }>, roleGroup: { __typename: 'CustomerUserRoleGroup', uuid: string, name: string }, salesRepresentative: { __typename: 'SalesRepresentative', email: string | null, firstName: string | null, lastName: string | null, telephone: string | null, uuid: string, image: { url: string, name: string | null } | null, telephoneData: { prefix: string | null, countryCode: string | null, number: string } | null } | null };

export type TypeBaseCustomerUserFragment_CurrentCompanyCustomerUser = { __typename: 'CurrentCompanyCustomerUser', uuid: string, firstName: string | null, lastName: string | null, email: string, telephone: string | null, billingAddressUuid: string, street: string | null, city: string | null, postcode: string | null, newsletterSubscription: boolean, pricingGroup: string, hasPasswordSet: boolean, roles: Array<Types.TypeCustomerUserRoleEnum>, telephoneData: { prefix: string | null, countryCode: string | null, number: string } | null, country: { __typename: 'Country', name: string, code: string } | null, defaultDeliveryAddress: { __typename: 'DeliveryAddress', uuid: string, companyName: string | null, street: string | null, city: string | null, postcode: string | null, telephone: string | null, firstName: string | null, lastName: string | null, telephoneData: { prefix: string | null, countryCode: string | null, number: string } | null, country: { __typename: 'Country', name: string, code: string } | null } | null, deliveryAddresses: Array<{ __typename: 'DeliveryAddress', uuid: string, companyName: string | null, street: string | null, city: string | null, postcode: string | null, telephone: string | null, firstName: string | null, lastName: string | null, telephoneData: { prefix: string | null, countryCode: string | null, number: string } | null, country: { __typename: 'Country', name: string, code: string } | null }>, roleGroup: { __typename: 'CustomerUserRoleGroup', uuid: string, name: string }, salesRepresentative: { __typename: 'SalesRepresentative', email: string | null, firstName: string | null, lastName: string | null, telephone: string | null, uuid: string, image: { url: string, name: string | null } | null, telephoneData: { prefix: string | null, countryCode: string | null, number: string } | null } | null };

export type TypeBaseCustomerUserFragment_CurrentRegularCustomerUser = { __typename: 'CurrentRegularCustomerUser', uuid: string, firstName: string | null, lastName: string | null, email: string, telephone: string | null, billingAddressUuid: string, street: string | null, city: string | null, postcode: string | null, newsletterSubscription: boolean, pricingGroup: string, hasPasswordSet: boolean, roles: Array<Types.TypeCustomerUserRoleEnum>, telephoneData: { prefix: string | null, countryCode: string | null, number: string } | null, country: { __typename: 'Country', name: string, code: string } | null, defaultDeliveryAddress: { __typename: 'DeliveryAddress', uuid: string, companyName: string | null, street: string | null, city: string | null, postcode: string | null, telephone: string | null, firstName: string | null, lastName: string | null, telephoneData: { prefix: string | null, countryCode: string | null, number: string } | null, country: { __typename: 'Country', name: string, code: string } | null } | null, deliveryAddresses: Array<{ __typename: 'DeliveryAddress', uuid: string, companyName: string | null, street: string | null, city: string | null, postcode: string | null, telephone: string | null, firstName: string | null, lastName: string | null, telephoneData: { prefix: string | null, countryCode: string | null, number: string } | null, country: { __typename: 'Country', name: string, code: string } | null }>, roleGroup: { __typename: 'CustomerUserRoleGroup', uuid: string, name: string }, salesRepresentative: { __typename: 'SalesRepresentative', email: string | null, firstName: string | null, lastName: string | null, telephone: string | null, uuid: string, image: { url: string, name: string | null } | null, telephoneData: { prefix: string | null, countryCode: string | null, number: string } | null } | null };

export type TypeBaseCustomerUserFragment_RegularCustomerUser = { __typename: 'RegularCustomerUser', uuid: string, firstName: string | null, lastName: string | null, email: string, telephone: string | null, billingAddressUuid: string, street: string | null, city: string | null, postcode: string | null, newsletterSubscription: boolean, pricingGroup: string, hasPasswordSet: boolean, roles: Array<Types.TypeCustomerUserRoleEnum>, telephoneData: { prefix: string | null, countryCode: string | null, number: string } | null, country: { __typename: 'Country', name: string, code: string } | null, defaultDeliveryAddress: { __typename: 'DeliveryAddress', uuid: string, companyName: string | null, street: string | null, city: string | null, postcode: string | null, telephone: string | null, firstName: string | null, lastName: string | null, telephoneData: { prefix: string | null, countryCode: string | null, number: string } | null, country: { __typename: 'Country', name: string, code: string } | null } | null, deliveryAddresses: Array<{ __typename: 'DeliveryAddress', uuid: string, companyName: string | null, street: string | null, city: string | null, postcode: string | null, telephone: string | null, firstName: string | null, lastName: string | null, telephoneData: { prefix: string | null, countryCode: string | null, number: string } | null, country: { __typename: 'Country', name: string, code: string } | null }>, roleGroup: { __typename: 'CustomerUserRoleGroup', uuid: string, name: string }, salesRepresentative: { __typename: 'SalesRepresentative', email: string | null, firstName: string | null, lastName: string | null, telephone: string | null, uuid: string, image: { url: string, name: string | null } | null, telephoneData: { prefix: string | null, countryCode: string | null, number: string } | null } | null };

export type TypeBaseCustomerUserFragment =
  | TypeBaseCustomerUserFragment_CompanyCustomerUser
  | TypeBaseCustomerUserFragment_CurrentCompanyCustomerUser
  | TypeBaseCustomerUserFragment_CurrentRegularCustomerUser
  | TypeBaseCustomerUserFragment_RegularCustomerUser
;

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