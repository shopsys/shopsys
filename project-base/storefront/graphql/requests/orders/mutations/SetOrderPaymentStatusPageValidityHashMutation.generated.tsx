import * as Types from '../../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeSetOrderPaymentStatusPageValidityHashMutationVariables = Types.Exact<{
  orderUuid: Types.Scalars['Uuid']['input'];
  orderPaymentStatusPageValidityHash: Types.Scalars['String']['input'];
}>;


export type TypeSetOrderPaymentStatusPageValidityHashMutation = { __typename?: 'Mutation', SetOrderPaymentStatusPageValidityHashMutation: string };


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
    "BaseCustomerUser": [
      "CompanyCustomerUser",
      "CurrentCompanyCustomerUser",
      "CurrentRegularCustomerUser",
      "RegularCustomerUser"
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
    "CurrentCustomerUser": [
      "CurrentCompanyCustomerUser",
      "CurrentRegularCustomerUser"
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
    

export const SetOrderPaymentStatusPageValidityHashMutationDocument = gql`
    mutation SetOrderPaymentStatusPageValidityHashMutation($orderUuid: Uuid!, $orderPaymentStatusPageValidityHash: String!) {
  SetOrderPaymentStatusPageValidityHashMutation(
    orderUuid: $orderUuid
    orderPaymentStatusPageValidityHash: $orderPaymentStatusPageValidityHash
  )
}
    `;

export function useSetOrderPaymentStatusPageValidityHashMutation() {
  return Urql.useMutation<TypeSetOrderPaymentStatusPageValidityHashMutation, TypeSetOrderPaymentStatusPageValidityHashMutationVariables>(SetOrderPaymentStatusPageValidityHashMutationDocument);
};