// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeCurrentCustomerUserReviewedProductUuidsQueryVariables = Exact<{
  first?: number | null | undefined;
}>;


export type TypeCurrentCustomerUserReviewedProductUuidsQuery = { currentCustomerUserProductReviews: { edges: Array<{ node: { uuid: string, productUuid: string | null } | null } | null> | null } };


export const CurrentCustomerUserReviewedProductUuidsQueryDocument = gql`
    query CurrentCustomerUserReviewedProductUuidsQuery($first: Int) {
  currentCustomerUserProductReviews(first: $first) {
    edges {
      node {
        uuid
        productUuid
      }
    }
  }
}
    `;

export function useCurrentCustomerUserReviewedProductUuidsQuery(options?: Omit<Urql.UseQueryArgs<TypeCurrentCustomerUserReviewedProductUuidsQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeCurrentCustomerUserReviewedProductUuidsQuery, TypeCurrentCustomerUserReviewedProductUuidsQueryVariables>({ query: CurrentCustomerUserReviewedProductUuidsQueryDocument, ...options });
};