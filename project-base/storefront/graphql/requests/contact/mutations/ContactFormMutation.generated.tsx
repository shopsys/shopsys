// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeContactFormInput = {
  /** Email address of the sender */
  email: string;
  /** Message that will be sent to recipient */
  message: string;
  /** Name of the sender */
  name: string;
  /** Subject of the message */
  subject?: string | null | undefined;
};

export type TypeContactFormMutationVariables = Exact<{
  input: Types.TypeContactFormInput;
}>;


export type TypeContactFormMutation = { ContactForm: boolean };


export const ContactFormMutationDocument = gql`
    mutation ContactFormMutation($input: ContactFormInput!) {
  ContactForm(input: $input)
}
    `;

export function useContactFormMutation() {
  return Urql.useMutation<TypeContactFormMutation, TypeContactFormMutationVariables>(ContactFormMutationDocument);
};