// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeProductQuestionInput = {
  /** Name of the customer asking the question */
  customerName: string;
  /** The customer's email address */
  email: string;
  /** UUID of the product the question is about */
  productUuid: string;
  /** The customer's question about the product */
  question: string;
};

export type TypeProductQuestionMutationVariables = Exact<{
  input: Types.TypeProductQuestionInput;
}>;


export type TypeProductQuestionMutation = { ProductQuestion: boolean };


export const ProductQuestionMutationDocument = gql`
    mutation ProductQuestionMutation($input: ProductQuestionInput!) {
  ProductQuestion(input: $input)
}
    `;

export function useProductQuestionMutation() {
  return Urql.useMutation<TypeProductQuestionMutation, TypeProductQuestionMutationVariables>(ProductQuestionMutationDocument);
};