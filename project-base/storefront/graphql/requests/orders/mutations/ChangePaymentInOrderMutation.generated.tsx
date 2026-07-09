// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ChangePaymentInOrderFragment } from '../fragments/ChangePaymentInOrderFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeChangePaymentInOrderInput = {
  /** Order urlHash as a proof of access to the order for anonymous customers */
  orderUrlHash?: string | null | undefined;
  /** Order identifier */
  orderUuid: string;
  /** Selected bank swift code of goPay payment bank transfer */
  paymentGoPayBankSwift?: string | null | undefined;
  /** UUID of a payment that should be assigned to the order. */
  paymentUuid: string;
};

export type TypeChangePaymentInOrderMutationVariables = Exact<{
  input: Types.TypeChangePaymentInOrderInput;
}>;


export type TypeChangePaymentInOrderMutation = { ChangePaymentInOrder: { __typename: 'Order', urlHash: string, number: string, paymentTransactionsCount: number } };


export const ChangePaymentInOrderMutationDocument = gql`
    mutation ChangePaymentInOrderMutation($input: ChangePaymentInOrderInput!) {
  ChangePaymentInOrder(input: $input) {
    ...ChangePaymentInOrderFragment
  }
}
    ${ChangePaymentInOrderFragment}`;

export function useChangePaymentInOrderMutation() {
  return Urql.useMutation<TypeChangePaymentInOrderMutation, TypeChangePaymentInOrderMutationVariables>(ChangePaymentInOrderMutationDocument);
};