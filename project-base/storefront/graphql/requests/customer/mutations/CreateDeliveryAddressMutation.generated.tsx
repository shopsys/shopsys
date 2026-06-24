// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { DeliveryAddressFragment } from '../fragments/DeliveryAddressFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeDeliveryAddressInput = {
  /** Delivery address city name */
  city: string;
  /** Delivery address company name */
  companyName?: string | null | undefined;
  /** Delivery address country */
  country: string;
  /** Delivery address first name */
  firstName: string;
  /** Delivery address last name */
  lastName: string;
  /** Delivery address zip code */
  postcode: string;
  /** Delivery address street name */
  street: string;
  /** Delivery address telephone */
  telephone?: TypePhoneDataInput | null | undefined;
  /** UUID */
  uuid?: string | null | undefined;
};

/** Represents phone number input */
export type TypePhoneDataInput = {
  /** Phone prefix country code in ISO 3166-1 alpha-2 */
  countryCode: string;
  /** Phone number without prefix */
  number: string;
  /** Phone prefix (eg. +420) */
  prefix: string;
};

export type TypeCreateDeliveryAddressMutationVariables = Exact<{
  input: Types.TypeDeliveryAddressInput;
}>;


export type TypeCreateDeliveryAddressMutation = { CreateDeliveryAddress: Array<{ __typename: 'DeliveryAddress', uuid: string, companyName: string | null, street: string | null, city: string | null, postcode: string | null, telephone: string | null, firstName: string | null, lastName: string | null, telephoneData: { prefix: string | null, countryCode: string | null, number: string } | null, country: { __typename: 'Country', name: string, code: string } | null }> };


export const CreateDeliveryAddressMutationDocument = gql`
    mutation CreateDeliveryAddressMutation($input: DeliveryAddressInput!) {
  CreateDeliveryAddress(input: $input) {
    ...DeliveryAddressFragment
  }
}
    ${DeliveryAddressFragment}`;

export function useCreateDeliveryAddressMutation() {
  return Urql.useMutation<TypeCreateDeliveryAddressMutation, TypeCreateDeliveryAddressMutationVariables>(CreateDeliveryAddressMutationDocument);
};