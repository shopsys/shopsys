import * as Types from '../../../types';

import gql from 'graphql-tag';
import { TokenFragments } from '../../auth/fragments/TokensFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeRegistrationByOrderMutationVariables = Types.Exact<{
  input: Types.TypeRegistrationByOrderInput;
}>;


export type TypeRegistrationByOrderMutation = { __typename?: 'Mutation', RegisterByOrder: { __typename?: 'LoginResult', showCartMergeInfo: boolean, tokens: { __typename?: 'Token', accessToken: string, refreshToken: string } } };


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
    

export const RegistrationByOrderMutationDocument = gql`
    mutation RegistrationByOrderMutation($input: RegistrationByOrderInput!) {
  RegisterByOrder(input: $input) {
    tokens {
      ...TokenFragments
    }
    showCartMergeInfo
  }
}
    ${TokenFragments}`;

export function useRegistrationByOrderMutation() {
  return Urql.useMutation<TypeRegistrationByOrderMutation, TypeRegistrationByOrderMutationVariables>(RegistrationByOrderMutationDocument);
};