// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { TokenFragments } from '../../auth/fragments/TokensFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
/** Represents phone number input */
export type TypePhoneDataInput = {
  /** Phone prefix country code in ISO 3166-1 alpha-2 */
  countryCode: string;
  /** Phone number without prefix */
  number: string;
  /** Phone prefix (eg. +420) */
  prefix: string;
};

/** Represents the main input object to register customer user */
export type TypeRegistrationDataInput = {
  /** UUID */
  billingAddressUuid?: string | null | undefined;
  /** Uuid of the cart that should be merged to the cart of the newly registered user */
  cartUuid?: string | null | undefined;
  /** Billing address city name (will be on the tax invoice) */
  city: string;
  /** Determines whether the customer is a company or not. */
  companyCustomer?: boolean | null | undefined;
  /** The customer’s company name (required when companyCustomer is true) */
  companyName?: string | null | undefined;
  /** The customer’s company identification number (required when companyCustomer is true) */
  companyNumber?: string | null | undefined;
  /** The customer’s company tax number (required when companyCustomer is true) */
  companyTaxNumber?: string | null | undefined;
  /** Billing address country code in ISO 3166-1 alpha-2 (Country will be on the tax invoice) */
  country: string;
  /** The customer's email address */
  email: string;
  /** Customer user first name */
  firstName: string;
  /** Customer user last name */
  lastName: string;
  /** Whether customer user should receive newsletters or not */
  newsletterSubscription: boolean;
  /** Customer user password */
  password: string;
  /** Billing address zip code (will be on the tax invoice) */
  postcode: string;
  /** Uuids of product lists that should be merged to the product lists of the user after registration */
  productListsUuids: Array<string>;
  /** Billing address street name (will be on the tax invoice) */
  street: string;
  /** The customer's telephone */
  telephone: TypePhoneDataInput;
};

export type TypeRegistrationMutationVariables = Exact<{
  input: Types.TypeRegistrationDataInput;
}>;


export type TypeRegistrationMutation = { Register: { showCartMergeInfo: boolean, tokens: { accessToken: string, refreshToken: string } } };


export const RegistrationMutationDocument = gql`
    mutation RegistrationMutation($input: RegistrationDataInput!) {
  Register(input: $input) {
    tokens {
      ...TokenFragments
    }
    showCartMergeInfo
  }
}
    ${TokenFragments}`;

export function useRegistrationMutation() {
  return Urql.useMutation<TypeRegistrationMutation, TypeRegistrationMutationVariables>(RegistrationMutationDocument);
};