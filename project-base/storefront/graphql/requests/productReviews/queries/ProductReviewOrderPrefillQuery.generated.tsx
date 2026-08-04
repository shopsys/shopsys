// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeProductReviewOrderPrefillQueryVariables = Exact<{
  urlHash?: string | null | undefined;
}>;


export type TypeProductReviewOrderPrefillQuery = { order: { __typename: 'Order', uuid: string, firstName: string | null, lastName: string | null, email: string } | null };


export const ProductReviewOrderPrefillQueryDocument = gql`
    query ProductReviewOrderPrefillQuery($urlHash: String) {
  order(urlHash: $urlHash) {
    __typename
    uuid
    firstName
    lastName
    email
  }
}
    `;

export function useProductReviewOrderPrefillQuery(options?: Omit<Urql.UseQueryArgs<TypeProductReviewOrderPrefillQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeProductReviewOrderPrefillQuery, TypeProductReviewOrderPrefillQueryVariables>({ query: ProductReviewOrderPrefillQueryDocument, ...options });
};