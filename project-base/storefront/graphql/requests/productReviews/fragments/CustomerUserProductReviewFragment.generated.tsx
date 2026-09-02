// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
/** One of possible moderation statuses of a product review */
export type TypeProductReviewStatusEnum =
  /** The review is approved and publicly visible */
  | 'APPROVED'
  /** The review is waiting for moderation */
  | 'PENDING'
  /** The review was rejected */
  | 'REJECTED';

export type TypeCustomerUserProductReviewFragment = { __typename: 'ProductReview', uuid: string, reviewerName: string | null, rating: number, text: string | null, createdAt: string, isVerifiedPurchase: boolean, status: Types.TypeProductReviewStatusEnum, rejectionReason: string | null, responseText: string | null, responseCreatedAt: string | null, productUuid: string | null, productName: string, product:
    | { slug: string, isVisible: boolean, fullName: string, mainImage: { url: string } | null }
    | { slug: string, isVisible: boolean, fullName: string, mainImage: { url: string } | null }
    | { slug: string, isVisible: boolean, fullName: string, mainImage: { url: string } | null }
   | null };

export const CustomerUserProductReviewFragment = gql`
    fragment CustomerUserProductReviewFragment on ProductReview {
  __typename
  uuid
  reviewerName
  rating
  text
  createdAt
  isVerifiedPurchase
  status
  rejectionReason
  responseText
  responseCreatedAt
  productUuid
  productName
  product {
    slug
    isVisible
    fullName
    mainImage {
      url
    }
  }
}
    `;