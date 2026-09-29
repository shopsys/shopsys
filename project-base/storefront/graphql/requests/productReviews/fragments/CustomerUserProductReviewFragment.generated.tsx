// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ProductReviewFragment } from './ProductReviewFragment.generated';
/** One of possible moderation statuses of a product review */
export type TypeProductReviewStatusEnum =
  /** The review is approved and publicly visible */
  | 'APPROVED'
  /** The review is waiting for moderation */
  | 'PENDING'
  /** The review was rejected */
  | 'REJECTED';

export type TypeCustomerUserProductReviewFragment = { __typename: 'ProductReview', status: Types.TypeProductReviewStatusEnum, rejectionReason: string | null, rejectedImagesCount: number, productUuid: string | null, uuid: string, productName: string, reviewerName: string | null, rating: number, text: string | null, createdAt: string, isVerifiedPurchase: boolean, responseText: string | null, responseCreatedAt: string | null, product:
    | { slug: string, isVisible: boolean, fullName: string, mainImage: { url: string } | null }
    | { slug: string, isVisible: boolean, fullName: string, mainImage: { url: string } | null }
    | { slug: string, isVisible: boolean, fullName: string, mainImage: { url: string } | null }
   | null, images: Array<{ __typename: 'Image', name: string | null, url: string }> };

export const CustomerUserProductReviewFragment = gql`
    fragment CustomerUserProductReviewFragment on ProductReview {
  ...ProductReviewFragment
  status
  rejectionReason
  rejectedImagesCount
  productUuid
  product {
    slug
    isVisible
    fullName
    mainImage {
      url
    }
  }
}
    ${ProductReviewFragment}`;