// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ProductReviewsSummaryFragment } from './ProductReviewsSummaryFragment.generated';
import { PageInfoFragment } from '../../pageInfo/fragments/PageInfoFragment.generated';
import { ProductReviewFragment } from './ProductReviewFragment.generated';
/** One of possible ordering modes for product reviews */
export type TypeProductReviewOrderingModeEnum =
  /** Order by rating, highest first */
  | 'HIGHEST_RATING'
  /** Order by rating, lowest first */
  | 'LOWEST_RATING'
  /** Order by date of creation, newest first */
  | 'NEWEST';

export type TypeProductReviewConnectionFragment = { totalCount: number, orderingMode: Types.TypeProductReviewOrderingModeEnum, summary: { __typename: 'ProductReviewsSummary', averageRating: number | null, totalCount: number, ratingCounts: Array<{ __typename: 'ProductReviewRatingCount', rating: number, count: number }> }, pageInfo: { __typename: 'PageInfo', hasNextPage: boolean, hasPreviousPage: boolean, endCursor: string | null }, edges: Array<{ cursor: string, node: { __typename: 'ProductReview', uuid: string, productName: string, reviewerName: string | null, rating: number, text: string | null, createdAt: string, isVerifiedPurchase: boolean, responseText: string | null, responseCreatedAt: string | null, images: Array<{ __typename: 'Image', name: string | null, url: string }> } | null } | null> | null };

export const ProductReviewConnectionFragment = gql`
    fragment ProductReviewConnectionFragment on ProductReviewConnection {
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
    ${ProductReviewsSummaryFragment}
${PageInfoFragment}
${ProductReviewFragment}`;