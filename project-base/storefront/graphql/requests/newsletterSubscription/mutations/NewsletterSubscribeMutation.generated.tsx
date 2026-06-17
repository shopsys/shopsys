// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeNewsletterSubscribeMutationVariables = Exact<{
  email: string;
}>;


export type TypeNewsletterSubscribeMutation = { NewsletterSubscribe: boolean };


export const NewsletterSubscribeMutationDocument = gql`
    mutation NewsletterSubscribeMutation($email: String!) {
  NewsletterSubscribe(input: {email: $email})
}
    `;

export function useNewsletterSubscribeMutation() {
  return Urql.useMutation<TypeNewsletterSubscribeMutation, TypeNewsletterSubscribeMutationVariables>(NewsletterSubscribeMutationDocument);
};