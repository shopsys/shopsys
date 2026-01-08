// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeNewsletterSubscribeMutationVariables = Types.Exact<{
  email: Types.Scalars['String']['input'];
}>;


export type TypeNewsletterSubscribeMutation = { __typename?: 'Mutation', NewsletterSubscribe: boolean };


export const NewsletterSubscribeMutationDocument = gql`
    mutation NewsletterSubscribeMutation($email: String!) {
  NewsletterSubscribe(input: {email: $email})
}
    `;

export function useNewsletterSubscribeMutation() {
  return Urql.useMutation<TypeNewsletterSubscribeMutation, TypeNewsletterSubscribeMutationVariables>(NewsletterSubscribeMutationDocument);
};