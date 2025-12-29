// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ChangePaymentInOrderFragment } from '../fragments/ChangePaymentInOrderFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeChangePaymentInOrderMutationVariables = Types.Exact<{
  input: Types.TypeChangePaymentInOrderInput;
}>;


export type TypeChangePaymentInOrderMutation = { __typename?: 'Mutation', ChangePaymentInOrder: { __typename: 'Order', urlHash: string, number: string, payment: { __typename: 'Payment', uuid: string, name: string, description: string | null, instructions: string | null, type: Types.TypePaymentTypeEnum, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, goPayPaymentMethod: { __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string } | null } } };


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