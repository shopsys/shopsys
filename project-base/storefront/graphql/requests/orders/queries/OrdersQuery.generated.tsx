import * as Types from '../../../types';

import gql from 'graphql-tag';
import { OrderListFragment } from '../fragments/OrderListFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeOrdersQueryVariables = Types.Exact<{
  after: Types.InputMaybe<Types.Scalars['String']['input']>;
  first: Types.InputMaybe<Types.Scalars['Int']['input']>;
}>;


export type TypeOrdersQuery = { __typename?: 'Query', orders: { __typename: 'OrderConnection', totalCount: number, pageInfo: { __typename: 'PageInfo', hasNextPage: boolean, hasPreviousPage: boolean, endCursor: string | null }, edges: Array<{ __typename: 'OrderEdge', cursor: string, node: { __typename: 'Order', uuid: string, number: string, creationDate: any, isPaid: boolean, status: string, note: string | null, productItems: Array<{ __typename: 'OrderItem', quantity: number }>, transport: { __typename: 'Transport', name: string, mainImage: { __typename: 'Image', url: string, name: string | null } | null }, payment: { __typename: 'Payment', name: string, type: string, mainImage: { __typename?: 'Image', url: string } | null }, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } } | null } | null> | null } | null };


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
    

export const OrdersQueryDocument = gql`
    query OrdersQuery($after: String, $first: Int) {
  orders(after: $after, first: $first) {
    ...OrderListFragment
  }
}
    ${OrderListFragment}`;

export function useOrdersQuery(options?: Omit<Urql.UseQueryArgs<TypeOrdersQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeOrdersQuery, TypeOrdersQueryVariables>({ query: OrdersQueryDocument, ...options });
};