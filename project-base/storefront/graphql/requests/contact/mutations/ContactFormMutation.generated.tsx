// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeContactFormMutationVariables = Types.Exact<{
  input: Types.TypeContactFormInput;
}>;


export type TypeContactFormMutation = (
  { __typename?: 'Mutation' }
  & Pick<Types.TypeMutation, 'ContactForm'>
);


export const ContactFormMutationDocument = gql`
    mutation ContactFormMutation($input: ContactFormInput!) {
  ContactForm(input: $input)
}
    `;

export function useContactFormMutation() {
  return Urql.useMutation<TypeContactFormMutation, TypeContactFormMutationVariables>(ContactFormMutationDocument);
};