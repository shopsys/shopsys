import * as Types from '../../../types';

import gql from 'graphql-tag';
import { SimpleCustomerUserFragment } from '../fragments/SimpleCustomerUserFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeEditCustomerUserPersonalDataMutationVariables = Types.Exact<{
  input: Types.TypeEditCustomerUserPersonalDataInput;
}>;


export type TypeEditCustomerUserPersonalDataMutation = { __typename?: 'Mutation', EditCustomerUserPersonalData: { __typename: 'CompanyCustomerUser', uuid: string, firstName: string | null, lastName: string | null, email: string, telephone: string | null, roles: Array<string>, roleGroup: { __typename: 'CustomerUserRoleGroup', uuid: string, name: string } } | { __typename: 'RegularCustomerUser', uuid: string, firstName: string | null, lastName: string | null, email: string, telephone: string | null, roles: Array<string>, roleGroup: { __typename: 'CustomerUserRoleGroup', uuid: string, name: string } } };


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
    

export const EditCustomerUserPersonalDataMutationDocument = gql`
    mutation EditCustomerUserPersonalDataMutation($input: EditCustomerUserPersonalDataInput!) {
  EditCustomerUserPersonalData(input: $input) {
    ...SimpleCustomerUserFragment
  }
}
    ${SimpleCustomerUserFragment}`;

export function useEditCustomerUserPersonalDataMutation() {
  return Urql.useMutation<TypeEditCustomerUserPersonalDataMutation, TypeEditCustomerUserPersonalDataMutationVariables>(EditCustomerUserPersonalDataMutationDocument);
};