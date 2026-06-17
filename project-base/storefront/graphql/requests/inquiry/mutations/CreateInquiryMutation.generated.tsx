// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeCreateInquiryInput = {
  /** The customer’s company name */
  companyName?: string | null | undefined;
  /** The customer’s company identification number */
  companyNumber?: string | null | undefined;
  /** The customer’s company tax number */
  companyTaxNumber?: string | null | undefined;
  /** The customer's email address */
  email: string;
  /** Customer user first name */
  firstName: string;
  /** Customer user last name */
  lastName: string;
  /** Customer's question or note to the inquiry product */
  note?: string | null | undefined;
  /** Product UUID */
  productUuid: string;
  /** The customer's telephone */
  telephone: TypePhoneDataInput;
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

export type TypeCreateInquiryMutationVariables = Exact<{
  input: Types.TypeCreateInquiryInput;
}>;


export type TypeCreateInquiryMutation = { CreateInquiry: boolean };


export const CreateInquiryMutationDocument = gql`
    mutation CreateInquiryMutation($input: CreateInquiryInput!) {
  CreateInquiry(input: $input)
}
    `;

export function useCreateInquiryMutation() {
  return Urql.useMutation<TypeCreateInquiryMutation, TypeCreateInquiryMutationVariables>(CreateInquiryMutationDocument);
};