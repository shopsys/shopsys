import * as Types from '../../../types';

import gql from 'graphql-tag';
import { NotificationBarsFragment } from '../fragments/NotificationBarsFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type NotificationBarsVariables = Types.Exact<{ [key: string]: never; }>;


export type NotificationBars = { __typename?: 'Query', notificationBars: Array<{ __typename: 'NotificationBar', text: string, rgbColor: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null }> | null };


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
    

export const NotificationBarsDocument = gql`
    query NotificationBars @redisCache(ttl: 3600) {
  notificationBars {
    ...NotificationBarsFragment
  }
}
    ${NotificationBarsFragment}`;

export function useNotificationBars(options?: Omit<Urql.UseQueryArgs<NotificationBarsVariables>, 'query'>) {
  return Urql.useQuery<NotificationBars, NotificationBarsVariables>({ query: NotificationBarsDocument, ...options });
};