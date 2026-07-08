// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeProductQuestionMutationVariables = Types.Exact<{
  input: Types.TypeProductQuestionInput;
}>;


export type TypeProductQuestionMutation = (
  { __typename?: 'Mutation' }
  & Pick<Types.TypeMutation, 'ProductQuestion'>
);


export const ProductQuestionMutationDocument = gql`
    mutation ProductQuestionMutation($input: ProductQuestionInput!) {
  ProductQuestion(input: $input)
}
    `;

export function useProductQuestionMutation() {
  return Urql.useMutation<TypeProductQuestionMutation, TypeProductQuestionMutationVariables>(ProductQuestionMutationDocument);
};