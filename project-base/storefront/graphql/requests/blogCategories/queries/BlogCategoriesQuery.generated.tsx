import * as Types from '../../../types';

import gql from 'graphql-tag';
import { BlogCategoriesFragment } from '../fragments/BlogCategoriesFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type BlogCategoriesVariables = Types.Exact<{ [key: string]: never; }>;


export type BlogCategories = { __typename?: 'Query', blogCategories: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, parent: { __typename?: 'BlogCategory', name: string } | null }>, parent: { __typename?: 'BlogCategory', name: string } | null }>, parent: { __typename?: 'BlogCategory', name: string } | null }>, parent: { __typename?: 'BlogCategory', name: string } | null }>, parent: { __typename?: 'BlogCategory', name: string } | null }> };


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
    

export const BlogCategoriesDocument = gql`
    query BlogCategories {
  blogCategories {
    ...BlogCategoriesFragment
  }
}
    ${BlogCategoriesFragment}`;

export function useBlogCategories(options?: Omit<Urql.UseQueryArgs<BlogCategoriesVariables>, 'query'>) {
  return Urql.useQuery<BlogCategories, BlogCategoriesVariables>({ query: BlogCategoriesDocument, ...options });
};