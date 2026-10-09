// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeComplaintInput = {
  /** Bank account number for money return */
  bankAccountNumber?: string | null | undefined;
  /** Delivery address */
  deliveryAddress: TypeDeliveryAddressInput;
  /** The customer's email address */
  email: string;
  /** All items in the complaint */
  items: Array<TypeComplaintItemInput>;
  /** Order or document number (doesn't have to be from any existing order) */
  manualDocumentNumber?: string | null | undefined;
  /** UUID of the order */
  orderUuid?: string | null | undefined;
  /** Chosen resolution from complaintResolutionQuery */
  resolution: string;
};

export type TypeComplaintItemInput = {
  /** Description of the complaint item */
  description: string;
  /** Files attached to the complaint item */
  files?: Array<File> | null | undefined;
  /** Catalog number of the complaint item entered by customer (if the complaint is created without an order, otherwise, the catalog number is taken from the order item) */
  manualComplaintItemCatnum?: string | null | undefined;
  /** Name of the complaint item entered by customer (if the complaint is created without an order, otherwise, the name is taken from the order item) */
  manualComplaintItemName?: string | null | undefined;
  /** UUID of the order item */
  orderItemUuid?: string | null | undefined;
  /** Quantity of the complaint item */
  quantity: number;
};

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

export type TypeCreateComplaintVariables = Exact<{
  input: Types.TypeComplaintInput;
}>;


export type TypeCreateComplaint = { CreateComplaint: { uuid: string } };


export const CreateComplaintDocument = gql`
    mutation CreateComplaint($input: ComplaintInput!) {
  CreateComplaint(input: $input) {
    uuid
  }
}
    `;

export function useCreateComplaint() {
  return Urql.useMutation<TypeCreateComplaint, TypeCreateComplaintVariables>(CreateComplaintDocument);
};