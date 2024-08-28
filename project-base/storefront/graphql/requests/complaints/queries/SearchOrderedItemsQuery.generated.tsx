import * as Types from '../../../types';

import gql from 'graphql-tag';
import { OrderDetailItemFragment } from '../../orders/fragments/OrderDetailItemFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeSearchOrderedItemsQueryVariables = Types.Exact<{
  first: Types.InputMaybe<Types.Scalars['Int']['input']>;
  after: Types.InputMaybe<Types.Scalars['String']['input']>;
  searchInput: Types.TypeSearchInput;
  filter: Types.InputMaybe<Types.TypeOrderItemsFilterInput>;
}>;


export type TypeSearchOrderedItemsQuery = { __typename?: 'Query', orderItemsSearch: { __typename?: 'OrderItemConnection', totalCount: number, edges: Array<{ __typename?: 'OrderItemEdge', cursor: string, node: { __typename: 'OrderItem', uuid: string, name: string, vatRate: string, quantity: number, unit: string | null, type: Types.TypeOrderItemTypeEnum, unitPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, order: { __typename?: 'Order', uuid: string, number: string, creationDate: any } } | null } | null> | null } };


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
    

export const SearchOrderedItemsQueryDocument = gql`
    query SearchOrderedItemsQuery($first: Int, $after: String, $searchInput: SearchInput!, $filter: OrderItemsFilterInput) {
  orderItemsSearch(
    first: $first
    after: $after
    searchInput: $searchInput
    filter: $filter
  ) {
    totalCount
    edges {
      cursor
      node {
        ...OrderDetailItemFragment
      }
    }
  }
}
    ${OrderDetailItemFragment}`;

export function useSearchOrderedItemsQuery(options: Omit<Urql.UseQueryArgs<TypeSearchOrderedItemsQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeSearchOrderedItemsQuery, TypeSearchOrderedItemsQueryVariables>({ query: SearchOrderedItemsQueryDocument, ...options });
};