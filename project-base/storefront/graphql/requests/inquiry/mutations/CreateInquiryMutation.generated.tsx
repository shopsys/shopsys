// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeCreateInquiryMutationVariables = Types.Exact<{
  input: Types.TypeCreateInquiryInput;
}>;


export type TypeCreateInquiryMutation = { __typename?: 'Mutation', CreateInquiry: boolean };


export const CreateInquiryMutationDocument = gql`
    mutation CreateInquiryMutation($input: CreateInquiryInput!) {
  CreateInquiry(input: $input)
}
    `;

export function useCreateInquiryMutation() {
  return Urql.useMutation<TypeCreateInquiryMutation, TypeCreateInquiryMutationVariables>(CreateInquiryMutationDocument);
};