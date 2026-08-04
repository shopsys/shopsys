// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { CustomerUserProductReviewFragment } from '../fragments/CustomerUserProductReviewFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
/** Represents the input for creating a product review */
export type TypeProductReviewInput = {
  /** Email of the reviewer, required for a customer that is not logged in (the account email is used otherwise) */
  email?: string | null | undefined;
  /** First name of the reviewer */
  firstName?: string | null | undefined;
  /** The review will be published without the reviewer name */
  isAnonymous: boolean;
  /** Last name of the reviewer */
  lastName?: string | null | undefined;
  /** URL hash of the order proving the purchase of a customer that is not logged in, the review is created unverified without it */
  orderUrlHash?: string | null | undefined;
  /** UUID of the reviewed product; a concrete variant has to be chosen for products with variants */
  productUuid: string;
  /** Star rating from 1 to 5 */
  rating: number;
  /** Text of the review */
  text?: string | null | undefined;
};

/** One of possible moderation statuses of a product review */
export type TypeProductReviewStatusEnum =
  /** The review is approved and publicly visible */
  | 'APPROVED'
  /** The review is waiting for moderation */
  | 'PENDING'
  /** The review was rejected */
  | 'REJECTED';

export type TypeCreateProductReviewMutationVariables = Exact<{
  input: Types.TypeProductReviewInput;
}>;


export type TypeCreateProductReviewMutation = { CreateProductReview: { __typename: 'ProductReview', uuid: string, reviewerName: string | null, rating: number, text: string | null, createdAt: string, isVerifiedPurchase: boolean, status: Types.TypeProductReviewStatusEnum, rejectionReason: string | null, responseText: string | null, responseCreatedAt: string | null, productUuid: string | null, productName: string, product:
      | { slug: string, isVisible: boolean, fullName: string, mainImage: { url: string } | null }
      | { slug: string, isVisible: boolean, fullName: string, mainImage: { url: string } | null }
      | { slug: string, isVisible: boolean, fullName: string, mainImage: { url: string } | null }
     | null } };


export const CreateProductReviewMutationDocument = gql`
    mutation CreateProductReviewMutation($input: ProductReviewInput!) {
  CreateProductReview(input: $input) {
    ...CustomerUserProductReviewFragment
  }
}
    ${CustomerUserProductReviewFragment}`;

export function useCreateProductReviewMutation() {
  return Urql.useMutation<TypeCreateProductReviewMutation, TypeCreateProductReviewMutationVariables>(CreateProductReviewMutationDocument);
};