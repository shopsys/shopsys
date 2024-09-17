import * as Types from '../../../types';

import gql from 'graphql-tag';
import { SimpleCustomerUserFragment } from '../fragments/SimpleCustomerUserFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeCurrentCustomerUsersQueryVariables = Types.Exact<{ [key: string]: never; }>;


export type TypeCurrentCustomerUsersQuery = { __typename?: 'Query', customerUsers: Array<{ __typename: 'CompanyCustomerUser', uuid: string, firstName: string | null, lastName: string | null, email: string, telephone: string | null, roles: Array<string>, roleGroup: { __typename: 'CustomerUserRoleGroup', uuid: string, name: string } } | { __typename: 'RegularCustomerUser', uuid: string, firstName: string | null, lastName: string | null, email: string, telephone: string | null, roles: Array<string>, roleGroup: { __typename: 'CustomerUserRoleGroup', uuid: string, name: string } }> };


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
    

export const CurrentCustomerUsersQueryDocument = gql`
    query CurrentCustomerUsersQuery {
  customerUsers {
    ...SimpleCustomerUserFragment
  }
}
    ${SimpleCustomerUserFragment}`;

export function useCurrentCustomerUsersQuery(options?: Omit<Urql.UseQueryArgs<TypeCurrentCustomerUsersQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeCurrentCustomerUsersQuery, TypeCurrentCustomerUsersQueryVariables>({ query: CurrentCustomerUsersQueryDocument, ...options });
};