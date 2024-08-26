import * as Types from '../../../types';

import gql from 'graphql-tag';
import { OrderDetailItemFragment } from '../../orders/fragments/OrderDetailItemFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeOrderedItemsQueryVariables = Types.Exact<{
  first: Types.InputMaybe<Types.Scalars['Int']['input']>;
  after: Types.InputMaybe<Types.Scalars['String']['input']>;
  filter: Types.InputMaybe<Types.TypeOrderItemsFilterInput>;
}>;


export type TypeOrderedItemsQuery = { __typename?: 'Query', orderItems: { __typename: 'OrderItemConnection', totalCount: number, edges: Array<{ __typename: 'OrderItemEdge', cursor: string, node: { __typename: 'OrderItem', uuid: string, name: string, vatRate: string, quantity: number, unit: string | null, type: Types.TypeOrderItemTypeEnum, unitPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, order: { __typename?: 'Order', number: string, creationDate: any }, product: { __typename?: 'MainVariant', slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null } | { __typename?: 'RegularProduct', slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null } | { __typename?: 'Variant', slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null } | null } | null } | null> | null } };


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
    

export const OrderedItemsQueryDocument = gql`
    query OrderedItemsQuery($first: Int, $after: String, $filter: OrderItemsFilterInput) {
  orderItems(first: $first, after: $after, filter: $filter) {
    __typename
    totalCount
    edges {
      __typename
      cursor
      node {
        ...OrderDetailItemFragment
      }
    }
  }
}
    ${OrderDetailItemFragment}`;

export function useOrderedItemsQuery(options?: Omit<Urql.UseQueryArgs<TypeOrderedItemsQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeOrderedItemsQuery, TypeOrderedItemsQueryVariables>({ query: OrderedItemsQueryDocument, ...options });
};