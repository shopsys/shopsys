// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { PageInfoFragment } from '../../pageInfo/fragments/PageInfoFragment.generated';
import { CustomerUserProductReviewFragment } from '../fragments/CustomerUserProductReviewFragment.generated';
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

export type TypeCurrentCustomerUserProductReviewsQueryVariables = Exact<{
  productUuid?: string | null | undefined;
  first?: number | null | undefined;
  after?: string | null | undefined;
}>;


export type TypeCurrentCustomerUserProductReviewsQuery = { currentCustomerUserProductReviews: { totalCount: number, pageInfo: { __typename: 'PageInfo', hasNextPage: boolean, hasPreviousPage: boolean, endCursor: string | null }, edges: Array<{ cursor: string, node: { __typename: 'ProductReview', uuid: string, reviewerName: string | null, rating: number, text: string | null, createdAt: string, isVerifiedPurchase: boolean, status: Types.TypeProductReviewStatusEnum, rejectionReason: string | null, responseText: string | null, responseCreatedAt: string | null, productUuid: string | null, productName: string, product:
          | { slug: string, isVisible: boolean, fullName: string, mainImage: { url: string } | null }
          | { slug: string, isVisible: boolean, fullName: string, mainImage: { url: string } | null }
          | { slug: string, isVisible: boolean, fullName: string, mainImage: { url: string } | null }
         | null } | null } | null> | null } };


export const CurrentCustomerUserProductReviewsQueryDocument = gql`
    query CurrentCustomerUserProductReviewsQuery($productUuid: Uuid, $first: Int, $after: String) {
  currentCustomerUserProductReviews(
    productUuid: $productUuid
    first: $first
    after: $after
  ) {
    totalCount
    pageInfo {
      ...PageInfoFragment
    }
    edges {
      cursor
      node {
        ...CustomerUserProductReviewFragment
      }
    }
  }
}
    ${PageInfoFragment}
${CustomerUserProductReviewFragment}`;

export function useCurrentCustomerUserProductReviewsQuery(options?: Omit<Urql.UseQueryArgs<TypeCurrentCustomerUserProductReviewsQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeCurrentCustomerUserProductReviewsQuery, TypeCurrentCustomerUserProductReviewsQueryVariables>({ query: CurrentCustomerUserProductReviewsQueryDocument, ...options });
};