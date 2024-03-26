import * as Types from '../../../types';

import gql from 'graphql-tag';
import { TokenFragments } from '../../auth/fragments/TokensFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type RecoverPasswordMutationVariables = Types.Exact<{
  email: Types.Scalars['String']['input'];
  hash: Types.Scalars['String']['input'];
  newPassword: Types.Scalars['Password']['input'];
}>;


export type RecoverPasswordMutation = { __typename?: 'Mutation', RecoverPassword: { __typename?: 'LoginResult', tokens: { __typename?: 'Token', accessToken: string, refreshToken: string } } };


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
    "CartInterface": [
      "Cart"
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
    "PriceInterface": [
      "Price",
      "ProductPrice"
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
    

export const RecoverPasswordMutationDocument = gql`
    mutation RecoverPasswordMutation($email: String!, $hash: String!, $newPassword: Password!) {
  RecoverPassword(input: {email: $email, hash: $hash, newPassword: $newPassword}) {
    tokens {
      ...TokenFragments
    }
  }
}
    ${TokenFragments}`;

export function useRecoverPasswordMutation() {
  return Urql.useMutation<RecoverPasswordMutation, RecoverPasswordMutationVariables>(RecoverPasswordMutationDocument);
};