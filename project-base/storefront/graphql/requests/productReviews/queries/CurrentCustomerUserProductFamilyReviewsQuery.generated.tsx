// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ProductFamilyReviewFragment } from '../fragments/ProductFamilyReviewFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
/** One of possible moderation statuses of a product review */
export type TypeProductReviewStatusEnum =
  /** The review is approved and publicly visible */
  | 'APPROVED'
  /** The review is waiting for moderation */
  | 'PENDING'
  /** The review was rejected */
  | 'REJECTED';

export type TypeCurrentCustomerUserProductFamilyReviewsQueryVariables = Exact<{
  productUuid: string;
  first?: number | null | undefined;
}>;


export type TypeCurrentCustomerUserProductFamilyReviewsQuery = { currentCustomerUserProductReviews: { edges: Array<{ node: { __typename: 'ProductReview', productUuid: string | null, status: Types.TypeProductReviewStatusEnum, uuid: string, productName: string, reviewerName: string | null, rating: number, text: string | null, createdAt: string, isVerifiedPurchase: boolean, responseText: string | null, responseCreatedAt: string | null, images: Array<{ __typename: 'Image', name: string | null, url: string }> } | null } | null> | null } };


export const CurrentCustomerUserProductFamilyReviewsQueryDocument = gql`
    query CurrentCustomerUserProductFamilyReviewsQuery($productUuid: Uuid!, $first: Int) {
  currentCustomerUserProductReviews(productUuid: $productUuid, first: $first) {
    edges {
      node {
        ...ProductFamilyReviewFragment
      }
    }
  }
}
    ${ProductFamilyReviewFragment}`;

export function useCurrentCustomerUserProductFamilyReviewsQuery(options: Omit<Urql.UseQueryArgs<TypeCurrentCustomerUserProductFamilyReviewsQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeCurrentCustomerUserProductFamilyReviewsQuery, TypeCurrentCustomerUserProductFamilyReviewsQueryVariables>({ query: CurrentCustomerUserProductFamilyReviewsQueryDocument, ...options });
};