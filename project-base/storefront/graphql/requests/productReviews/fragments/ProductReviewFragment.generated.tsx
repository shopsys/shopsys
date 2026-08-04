// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeProductReviewFragment = { __typename: 'ProductReview', uuid: string, productName: string, reviewerName: string | null, rating: number, text: string | null, createdAt: string, isVerifiedPurchase: boolean, responseText: string | null, responseCreatedAt: string | null };

export const ProductReviewFragment = gql`
    fragment ProductReviewFragment on ProductReview {
  __typename
  uuid
  productName
  reviewerName
  rating
  text
  createdAt
  isVerifiedPurchase
  responseText
  responseCreatedAt
}
    `;