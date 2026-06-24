// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
/** Input for requesting withdrawal from contract for an order */
export type TypeOrderWithdrawalRequestInput = {
  /** Email address for withdrawal confirmation */
  email: string;
  /** First name of the person requesting withdrawal */
  firstName: string;
  /** Last name of the person requesting withdrawal */
  lastName: string;
  /** Additional note or reason for withdrawal (optional) */
  note?: string | null | undefined;
  /** Order URL hash to identify the order */
  orderUrlHash: string;
  /** Telephone number data (optional) */
  telephone?: TypePhoneDataInput | null | undefined;
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

export type TypeOrderWithdrawalRequestMutationVariables = Exact<{
  input: Types.TypeOrderWithdrawalRequestInput;
}>;


export type TypeOrderWithdrawalRequestMutation = { OrderWithdrawalRequest: boolean };


export const OrderWithdrawalRequestMutationDocument = gql`
    mutation OrderWithdrawalRequestMutation($input: OrderWithdrawalRequestInput!) {
  OrderWithdrawalRequest(input: $input)
}
    `;

export function useOrderWithdrawalRequestMutation() {
  return Urql.useMutation<TypeOrderWithdrawalRequestMutation, TypeOrderWithdrawalRequestMutationVariables>(OrderWithdrawalRequestMutationDocument);
};