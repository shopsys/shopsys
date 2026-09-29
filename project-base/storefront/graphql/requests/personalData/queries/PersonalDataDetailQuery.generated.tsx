// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { CountryFragment } from '../../countries/fragments/CountryFragment.generated';
import { DeliveryAddressFragment } from '../../customer/fragments/DeliveryAddressFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
/** One of possible types of the order item */
export type TypeOrderItemTypeEnum =
  | 'additionalService'
  | 'discount'
  | 'payment'
  | 'product'
  | 'productGift'
  | 'promotion'
  | 'rounding'
  | 'transport';

export type TypePersonalDataDetailQueryVariables = Exact<{
  hash: string;
}>;


export type TypePersonalDataDetailQuery = { accessPersonalData: { __typename: 'PersonalData', exportLink: string, orders: Array<{ __typename: 'Order', uuid: string, city: string, companyName: string | null, number: string, creationDate: string, firstName: string | null, lastName: string | null, telephone: string, companyNumber: string | null, companyTaxNumber: string | null, street: string, postcode: string, deliveryFirstName: string | null, deliveryLastName: string | null, deliveryCompanyName: string | null, deliveryTelephone: string | null, deliveryStreet: string | null, deliveryCity: string | null, deliveryPostcode: string | null, items: Array<{ __typename: 'OrderItem', uuid: string, type: Types.TypeOrderItemTypeEnum, name: string }>, country: { __typename: 'Country', name: string, code: string }, deliveryCountry: { __typename: 'Country', name: string, code: string } | null, productItems: Array<{ __typename: 'OrderItem', uuid: string, quantity: number }>, totalPrice: { priceWithVat: string } }>, customerUser:
      | { __typename: 'CompanyCustomerUser', companyName: string | null, companyNumber: string | null, companyTaxNumber: string | null, uuid: string, firstName: string | null, lastName: string | null, email: string, telephone: string | null, street: string | null, city: string | null, postcode: string | null, country: { __typename: 'Country', name: string, code: string } | null, deliveryAddresses: Array<{ __typename: 'DeliveryAddress', uuid: string, companyName: string | null, street: string | null, city: string | null, postcode: string | null, telephone: string | null, firstName: string | null, lastName: string | null, telephoneData: { prefix: string | null, countryCode: string | null, number: string } | null, country: { __typename: 'Country', name: string, code: string } | null }> }
      | { __typename: 'CurrentCompanyCustomerUser', uuid: string, firstName: string | null, lastName: string | null, email: string, telephone: string | null, street: string | null, city: string | null, postcode: string | null, country: { __typename: 'Country', name: string, code: string } | null, deliveryAddresses: Array<{ __typename: 'DeliveryAddress', uuid: string, companyName: string | null, street: string | null, city: string | null, postcode: string | null, telephone: string | null, firstName: string | null, lastName: string | null, telephoneData: { prefix: string | null, countryCode: string | null, number: string } | null, country: { __typename: 'Country', name: string, code: string } | null }> }
      | { __typename: 'CurrentRegularCustomerUser', uuid: string, firstName: string | null, lastName: string | null, email: string, telephone: string | null, street: string | null, city: string | null, postcode: string | null, country: { __typename: 'Country', name: string, code: string } | null, deliveryAddresses: Array<{ __typename: 'DeliveryAddress', uuid: string, companyName: string | null, street: string | null, city: string | null, postcode: string | null, telephone: string | null, firstName: string | null, lastName: string | null, telephoneData: { prefix: string | null, countryCode: string | null, number: string } | null, country: { __typename: 'Country', name: string, code: string } | null }> }
      | { __typename: 'RegularCustomerUser', uuid: string, firstName: string | null, lastName: string | null, email: string, telephone: string | null, street: string | null, city: string | null, postcode: string | null, country: { __typename: 'Country', name: string, code: string } | null, deliveryAddresses: Array<{ __typename: 'DeliveryAddress', uuid: string, companyName: string | null, street: string | null, city: string | null, postcode: string | null, telephone: string | null, firstName: string | null, lastName: string | null, telephoneData: { prefix: string | null, countryCode: string | null, number: string } | null, country: { __typename: 'Country', name: string, code: string } | null }> }
     | null, newsletterSubscriber: { __typename: 'NewsletterSubscriber', email: string, createdAt: string } | null, complaints: Array<{ __typename: 'Complaint', uuid: string, number: string, createdAt: string, status: string, deliveryFirstName: string, deliveryLastName: string, deliveryCompanyName: string | null, deliveryCity: string, deliveryPostcode: string, deliveryStreet: string, deliveryTelephone: string, deliveryCountry: { name: string }, items: Array<{ __typename: 'ComplaintItem', productName: string, quantity: number, description: string, orderItem: { uuid: string } | null }> }> } };


export const PersonalDataDetailQueryDocument = gql`
    query PersonalDataDetailQuery($hash: String!) {
  accessPersonalData(hash: $hash) {
    __typename
    orders {
      __typename
      uuid
      city
      companyName
      number
      creationDate
      items {
        __typename
        uuid
        type
        name
      }
      firstName
      lastName
      telephone
      companyNumber
      companyTaxNumber
      street
      city
      postcode
      country {
        ...CountryFragment
      }
      deliveryFirstName
      deliveryLastName
      deliveryCompanyName
      deliveryTelephone
      deliveryStreet
      deliveryCity
      deliveryPostcode
      deliveryCountry {
        ...CountryFragment
      }
      productItems {
        __typename
        uuid
        quantity
      }
      totalPrice {
        priceWithVat
      }
    }
    customerUser {
      __typename
      uuid
      firstName
      lastName
      email
      telephone
      street
      city
      postcode
      country {
        ...CountryFragment
      }
      deliveryAddresses {
        ...DeliveryAddressFragment
      }
      ... on CompanyCustomerUser {
        companyName
        companyNumber
        companyTaxNumber
      }
    }
    newsletterSubscriber {
      __typename
      email
      createdAt
    }
    exportLink
    complaints {
      __typename
      uuid
      number
      createdAt
      status
      deliveryFirstName
      deliveryLastName
      deliveryCompanyName
      deliveryCity
      deliveryPostcode
      deliveryStreet
      deliveryTelephone
      deliveryCountry {
        name
      }
      items {
        __typename
        productName
        quantity
        description
        orderItem {
          uuid
        }
      }
    }
  }
}
    ${CountryFragment}
${DeliveryAddressFragment}`;

export function usePersonalDataDetailQuery(options: Omit<Urql.UseQueryArgs<TypePersonalDataDetailQueryVariables>, 'query'>) {
  return Urql.useQuery<TypePersonalDataDetailQuery, TypePersonalDataDetailQueryVariables>({ query: PersonalDataDetailQueryDocument, ...options });
};