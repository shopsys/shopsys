import * as Types from '../../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeUpdatePaymentStatusMutationVariables = Types.Exact<{
  orderUuid: Types.Scalars['Uuid']['input'];
  orderPaymentStatusPageValidityHash?: Types.InputMaybe<Types.Scalars['String']['input']>;
}>;


export type TypeUpdatePaymentStatusMutation = { __typename?: 'Mutation', UpdatePaymentStatus: { __typename?: 'Order', isPaid: boolean, paymentTransactionsCount: number, payment: { __typename?: 'Payment', type: string } } };


      export interface PossibleTypesResultData {
        possibleTypes: {
          [key: string]: string[]
        }
      }
      const result: PossibleTypesResultData = {
  "possibleTypes": {
    "Advert": [
      "AdvertCode",
      "AdvertImage"
    ],
    "ArticleInterface": [
      "ArticleSite",
      "BlogArticle"
    ],
    "Breadcrumb": [
      "ArticleSite",
      "BlogArticle",
      "BlogCategory",
      "Brand",
      "Category",
      "Flag",
      "MainVariant",
      "RegularProduct",
      "Store",
      "Variant"
    ],
    "CustomerUser": [
      "CompanyCustomerUser",
      "RegularCustomerUser"
    ],
    "Hreflang": [
      "BlogArticle",
      "BlogCategory",
      "Brand",
      "Flag",
      "MainVariant",
      "RegularProduct",
      "SeoPage",
      "Variant"
    ],
    "NotBlogArticleInterface": [
      "ArticleLink",
      "ArticleSite"
    ],
    "ParameterFilterOptionInterface": [
      "ParameterCheckboxFilterOption",
      "ParameterColorFilterOption",
      "ParameterSliderFilterOption"
    ],
    "Product": [
      "MainVariant",
      "RegularProduct",
      "Variant"
    ],
    "ProductListable": [
      "Brand",
      "Category",
      "Flag"
    ],
    "Slug": [
      "ArticleSite",
      "BlogArticle",
      "BlogCategory",
      "Brand",
      "Category",
      "Flag",
      "MainVariant",
      "RegularProduct",
      "Store",
      "Variant"
    ]
  }
};
      export default result;
    

export const UpdatePaymentStatusMutationDocument = gql`
    mutation UpdatePaymentStatusMutation($orderUuid: Uuid!, $orderPaymentStatusPageValidityHash: String = null) {
  UpdatePaymentStatus(
    orderUuid: $orderUuid
    orderPaymentStatusPageValidityHash: $orderPaymentStatusPageValidityHash
  ) {
    isPaid
    paymentTransactionsCount
    payment {
      type
    }
  }
}
    `;

export function useUpdatePaymentStatusMutation() {
  return Urql.useMutation<TypeUpdatePaymentStatusMutation, TypeUpdatePaymentStatusMutationVariables>(UpdatePaymentStatusMutationDocument);
};