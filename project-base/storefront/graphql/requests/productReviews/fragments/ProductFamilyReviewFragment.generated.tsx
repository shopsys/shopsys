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

export type TypeProductFamilyReviewFragment = { __typename: 'ProductReview', productUuid: string | null, status: Types.TypeProductReviewStatusEnum, uuid: string, productName: string, reviewerName: string | null, rating: number, text: string | null, createdAt: string, isVerifiedPurchase: boolean, responseText: string | null, responseCreatedAt: string | null, images: Array<{ __typename: 'Image', name: string | null, url: string }> };

export const ProductFamilyReviewFragment = gql`
    fragment ProductFamilyReviewFragment on ProductReview {
  ...ProductReviewFragment
  productUuid
  status
}
    ${ProductReviewFragment}`;