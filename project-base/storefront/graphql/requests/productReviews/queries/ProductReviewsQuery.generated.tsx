// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ProductReviewsSummaryFragment } from '../fragments/ProductReviewsSummaryFragment.generated';
import { PageInfoFragment } from '../../pageInfo/fragments/PageInfoFragment.generated';
import { ProductReviewFragment } from '../fragments/ProductReviewFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
/** One of possible ordering modes for product reviews */
export type TypeProductReviewOrderingModeEnum =
  /** Order by rating, highest first */
  | 'HIGHEST_RATING'
  /** Order by rating, lowest first */
  | 'LOWEST_RATING'
  /** Order by date of creation, newest first */
  | 'NEWEST';

export type TypeProductReviewsQueryVariables = Exact<{
  productUuid: string;
  first?: number | null | undefined;
  after?: string | null | undefined;
  orderingMode?: Types.TypeProductReviewOrderingModeEnum | null | undefined;
}>;


export type TypeProductReviewsQuery = { productReviews: { totalCount: number, orderingMode: Types.TypeProductReviewOrderingModeEnum, summary: { __typename: 'ProductReviewsSummary', averageRating: number | null, totalCount: number, ratingCounts: Array<{ __typename: 'ProductReviewRatingCount', rating: number, count: number }> }, pageInfo: { __typename: 'PageInfo', hasNextPage: boolean, hasPreviousPage: boolean, endCursor: string | null }, edges: Array<{ cursor: string, node: { __typename: 'ProductReview', uuid: string, productName: string, reviewerName: string | null, rating: number, text: string | null, createdAt: string, isVerifiedPurchase: boolean, responseText: string | null, responseCreatedAt: string | null } | null } | null> | null } };


export const ProductReviewsQueryDocument = gql`
    query ProductReviewsQuery($productUuid: Uuid!, $first: Int, $after: String, $orderingMode: ProductReviewOrderingModeEnum) {
  productReviews(
    productUuid: $productUuid
    first: $first
    after: $after
    orderingMode: $orderingMode
  ) {
    totalCount
    orderingMode
    summary {
      ...ProductReviewsSummaryFragment
    }
    pageInfo {
      ...PageInfoFragment
    }
    edges {
      cursor
      node {
        ...ProductReviewFragment
      }
    }
  }
}
    ${ProductReviewsSummaryFragment}
${PageInfoFragment}
${ProductReviewFragment}`;

export function useProductReviewsQuery(options: Omit<Urql.UseQueryArgs<TypeProductReviewsQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeProductReviewsQuery, TypeProductReviewsQueryVariables>({ query: ProductReviewsQueryDocument, ...options });
};