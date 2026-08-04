// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeProductReviewsSummaryFragment = { __typename: 'ProductReviewsSummary', averageRating: number | null, totalCount: number, ratingCounts: Array<{ __typename: 'ProductReviewRatingCount', rating: number, count: number }> };

export const ProductReviewsSummaryFragment = gql`
    fragment ProductReviewsSummaryFragment on ProductReviewsSummary {
  __typename
  averageRating
  totalCount
  ratingCounts {
    __typename
    rating
    count
  }
}
    `;