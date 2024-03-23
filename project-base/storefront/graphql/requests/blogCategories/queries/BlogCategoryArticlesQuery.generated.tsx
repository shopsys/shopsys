import * as Types from '../../../types';

import gql from 'graphql-tag';
import { BlogArticleConnectionFragment } from '../../articlesInterface/blogArticles/fragments/BlogArticleConnectionFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type BlogCategoryArticlesVariables = Types.Exact<{
  uuid: Types.Scalars['Uuid']['input'];
  endCursor: Types.Scalars['String']['input'];
  pageSize: Types.InputMaybe<Types.Scalars['Int']['input']>;
}>;


export type BlogCategoryArticles = { __typename?: 'Query', blogCategory: { __typename?: 'BlogCategory', blogArticles: { __typename: 'BlogArticleConnection', totalCount: number, pageInfo: { __typename: 'PageInfo', hasNextPage: boolean, hasPreviousPage: boolean, endCursor: string | null }, edges: Array<{ __typename: 'BlogArticleEdge', node: { __typename: 'BlogArticle', uuid: string, name: string, link: string, publishDate: any, perex: string | null, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null, blogCategories: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, parent: { __typename?: 'BlogCategory', name: string } | null }> } | null } | null> | null } } | null };


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
    

export const BlogCategoryArticlesDocument = gql`
    query BlogCategoryArticles($uuid: Uuid!, $endCursor: String!, $pageSize: Int) {
  blogCategory(uuid: $uuid) {
    blogArticles(after: $endCursor, first: $pageSize) {
      ...BlogArticleConnectionFragment
    }
  }
}
    ${BlogArticleConnectionFragment}`;

export function useBlogCategoryArticles(options: Omit<Urql.UseQueryArgs<BlogCategoryArticlesVariables>, 'query'>) {
  return Urql.useQuery<BlogCategoryArticles, BlogCategoryArticlesVariables>({ query: BlogCategoryArticlesDocument, ...options });
};