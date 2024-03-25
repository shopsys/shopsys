import * as Types from '../../../types';

import gql from 'graphql-tag';
import { BlogCategoryDetailFragment } from '../fragments/BlogCategoryDetailFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type BlogCategoryQueryVariables = Types.Exact<{
  urlSlug: Types.InputMaybe<Types.Scalars['String']['input']>;
}>;


export type BlogCategoryQuery = { __typename?: 'Query', blogCategory: { __typename: 'BlogCategory', uuid: string, name: string, seoTitle: string | null, seoMetaDescription: string | null, articlesTotalCount: number, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, hreflangLinks: Array<{ __typename?: 'HreflangLink', hreflang: string, href: string }>, blogCategoriesTree: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, children: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, parent: { __typename?: 'BlogCategory', name: string } | null }>, parent: { __typename?: 'BlogCategory', name: string } | null }>, parent: { __typename?: 'BlogCategory', name: string } | null }>, parent: { __typename?: 'BlogCategory', name: string } | null }>, parent: { __typename?: 'BlogCategory', name: string } | null }> } | null };


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
    

export const BlogCategoryQueryDocument = gql`
    query BlogCategoryQuery($urlSlug: String) @friendlyUrl {
  blogCategory(urlSlug: $urlSlug) {
    ...BlogCategoryDetailFragment
  }
}
    ${BlogCategoryDetailFragment}`;

export function useBlogCategoryQuery(options?: Omit<Urql.UseQueryArgs<BlogCategoryQueryVariables>, 'query'>) {
  return Urql.useQuery<BlogCategoryQuery, BlogCategoryQueryVariables>({ query: BlogCategoryQueryDocument, ...options });
};