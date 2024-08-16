import * as Types from '../../../types';

import gql from 'graphql-tag';
import { CustomerUserRoleGroupFragment } from './CustomerUserRoleGroupFragment.generated';
export type TypeSimpleCustomerUserFragment_CompanyCustomerUser_ = { __typename: 'CompanyCustomerUser', uuid: string, firstName: string | null, lastName: string | null, email: string, telephone: string | null, roles: Array<string>, roleGroup: { __typename: 'CustomerUserRoleGroup', uuid: string, name: string } };

export type TypeSimpleCustomerUserFragment_RegularCustomerUser_ = { __typename: 'RegularCustomerUser', uuid: string, firstName: string | null, lastName: string | null, email: string, telephone: string | null, roles: Array<string>, roleGroup: { __typename: 'CustomerUserRoleGroup', uuid: string, name: string } };

export type TypeSimpleCustomerUserFragment = TypeSimpleCustomerUserFragment_CompanyCustomerUser_ | TypeSimpleCustomerUserFragment_RegularCustomerUser_;


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
    
export const SimpleCustomerUserFragment = gql`
    fragment SimpleCustomerUserFragment on CustomerUser {
  __typename
  uuid
  firstName
  lastName
  email
  telephone
  roles
  roleGroup {
    ...CustomerUserRoleGroupFragment
  }
}
    ${CustomerUserRoleGroupFragment}`;