import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ListedStoreFragment } from '../fragments/ListedStoreFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type StoreQueryVariables = Types.Exact<{
  uuid: Types.InputMaybe<Types.Scalars['Uuid']['input']>;
}>;


export type StoreQuery = { __typename?: 'Query', store: { __typename: 'Store', slug: string, name: string, description: string | null, locationLatitude: string | null, locationLongitude: string | null, street: string, postcode: string, city: string, identifier: string, openingHours: { __typename?: 'OpeningHours', isOpen: boolean, dayOfWeek: number, openingHoursOfDays: Array<{ __typename?: 'OpeningHoursOfDay', date: any, dayOfWeek: number, openingHoursRanges: Array<{ __typename?: 'OpeningHoursRange', openingTime: string, closingTime: string }> }> }, country: { __typename: 'Country', name: string, code: string } } | null };


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
    

export const StoreQueryDocument = gql`
    query StoreQuery($uuid: Uuid) {
  store(uuid: $uuid) {
    ...ListedStoreFragment
  }
}
    ${ListedStoreFragment}`;

export function useStoreQuery(options?: Omit<Urql.UseQueryArgs<StoreQueryVariables>, 'query'>) {
  return Urql.useQuery<StoreQuery, StoreQueryVariables>({ query: StoreQueryDocument, ...options });
};