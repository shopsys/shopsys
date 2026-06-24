// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { CurrentCustomerUserFragment } from '../fragments/CurrentCustomerUserFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeChangePersonalDataInput = {
  /** Customer user first name */
  firstName: string;
  /** Customer user last name */
  lastName: string;
  /** Whether customer user should receive newsletters or not */
  newsletterSubscription: boolean;
  /** The customer's telephone */
  telephone: TypePhoneDataInput;
};

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

/** One of the possible methods of the customer user login */
export type TypeLoginTypeEnum =
  | 'admin'
  | 'facebook'
  | 'google'
  | 'seznam'
  | 'web';

/** Represents phone number input */
export type TypePhoneDataInput = {
  /** Phone prefix country code in ISO 3166-1 alpha-2 */
  countryCode: string;
  /** Phone number without prefix */
  number: string;
  /** Phone prefix (eg. +420) */
  prefix: string;
};

export type TypeChangePersonalDataMutationVariables = Exact<{
  input: Types.TypeChangePersonalDataInput;
}>;


export type TypeChangePersonalDataMutation = { ChangePersonalData:
    | { __typename: 'CurrentCompanyCustomerUser', companyName: string | null, companyNumber: string | null, companyTaxNumber: string | null, uuid: string, firstName: string | null, lastName: string | null, email: string, telephone: string | null, billingAddressUuid: string, street: string | null, city: string | null, postcode: string | null, newsletterSubscription: boolean, pricingGroup: string, hasPasswordSet: boolean, roles: Array<Types.TypeCustomerUserRoleEnum>, loginInfo: { __typename: 'LoginInfo', externalId: string | null, loginType: Types.TypeLoginTypeEnum }, telephoneData: { prefix: string | null, countryCode: string | null, number: string } | null, country: { __typename: 'Country', name: string, code: string } | null, defaultDeliveryAddress: { __typename: 'DeliveryAddress', uuid: string, companyName: string | null, street: string | null, city: string | null, postcode: string | null, telephone: string | null, firstName: string | null, lastName: string | null, telephoneData: { prefix: string | null, countryCode: string | null, number: string } | null, country: { __typename: 'Country', name: string, code: string } | null } | null, deliveryAddresses: Array<{ __typename: 'DeliveryAddress', uuid: string, companyName: string | null, street: string | null, city: string | null, postcode: string | null, telephone: string | null, firstName: string | null, lastName: string | null, telephoneData: { prefix: string | null, countryCode: string | null, number: string } | null, country: { __typename: 'Country', name: string, code: string } | null }>, roleGroup: { __typename: 'CustomerUserRoleGroup', uuid: string, name: string }, salesRepresentative: { __typename: 'SalesRepresentative', email: string | null, firstName: string | null, lastName: string | null, telephone: string | null, uuid: string, image: { url: string, name: string | null } | null, telephoneData: { prefix: string | null, countryCode: string | null, number: string } | null } | null }
    | { __typename: 'CurrentRegularCustomerUser', uuid: string, firstName: string | null, lastName: string | null, email: string, telephone: string | null, billingAddressUuid: string, street: string | null, city: string | null, postcode: string | null, newsletterSubscription: boolean, pricingGroup: string, hasPasswordSet: boolean, roles: Array<Types.TypeCustomerUserRoleEnum>, loginInfo: { __typename: 'LoginInfo', externalId: string | null, loginType: Types.TypeLoginTypeEnum }, telephoneData: { prefix: string | null, countryCode: string | null, number: string } | null, country: { __typename: 'Country', name: string, code: string } | null, defaultDeliveryAddress: { __typename: 'DeliveryAddress', uuid: string, companyName: string | null, street: string | null, city: string | null, postcode: string | null, telephone: string | null, firstName: string | null, lastName: string | null, telephoneData: { prefix: string | null, countryCode: string | null, number: string } | null, country: { __typename: 'Country', name: string, code: string } | null } | null, deliveryAddresses: Array<{ __typename: 'DeliveryAddress', uuid: string, companyName: string | null, street: string | null, city: string | null, postcode: string | null, telephone: string | null, firstName: string | null, lastName: string | null, telephoneData: { prefix: string | null, countryCode: string | null, number: string } | null, country: { __typename: 'Country', name: string, code: string } | null }>, roleGroup: { __typename: 'CustomerUserRoleGroup', uuid: string, name: string }, salesRepresentative: { __typename: 'SalesRepresentative', email: string | null, firstName: string | null, lastName: string | null, telephone: string | null, uuid: string, image: { url: string, name: string | null } | null, telephoneData: { prefix: string | null, countryCode: string | null, number: string } | null } | null }
   };


export const ChangePersonalDataMutationDocument = gql`
    mutation ChangePersonalDataMutation($input: ChangePersonalDataInput!) {
  ChangePersonalData(input: $input) {
    ...CurrentCustomerUserFragment
  }
}
    ${CurrentCustomerUserFragment}`;

export function useChangePersonalDataMutation() {
  return Urql.useMutation<TypeChangePersonalDataMutation, TypeChangePersonalDataMutationVariables>(ChangePersonalDataMutationDocument);
};