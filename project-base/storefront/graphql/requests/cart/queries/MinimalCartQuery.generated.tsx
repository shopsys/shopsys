import * as Types from '../../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type MinimalCartQueryVariables = Types.Exact<{
  cartUuid: Types.InputMaybe<Types.Scalars['Uuid']['input']>;
}>;


export type MinimalCartQuery = { __typename?: 'Query', cart: { __typename?: 'Cart', items: Array<{ __typename?: 'CartItem', uuid: string }>, transport: { __typename?: 'Transport', uuid: string } | null, payment: { __typename?: 'Payment', uuid: string } | null } | null };


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
    

export const MinimalCartQueryDocument = gql`
    query MinimalCartQuery($cartUuid: Uuid) {
  cart(cartInput: {cartUuid: $cartUuid}) {
    items {
      uuid
    }
    transport {
      uuid
    }
    payment {
      uuid
    }
  }
}
    `;

export function useMinimalCartQuery(options?: Omit<Urql.UseQueryArgs<MinimalCartQueryVariables>, 'query'>) {
  return Urql.useQuery<MinimalCartQuery, MinimalCartQueryVariables>({ query: MinimalCartQueryDocument, ...options });
};