import * as Types from '../../../types';

import gql from 'graphql-tag';
import { LastOrderFragment } from '../fragments/LastOrderFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type LastOrderQueryVariables = Types.Exact<{ [key: string]: never; }>;


export type LastOrderQuery = { __typename?: 'Query', lastOrder: { __typename: 'Order', pickupPlaceIdentifier: string | null, deliveryStreet: string | null, deliveryCity: string | null, deliveryPostcode: string | null, transport: { __typename: 'Transport', uuid: string, name: string, description: string | null, transportType: { __typename?: 'TransportType', code: string } }, payment: { __typename: 'Payment', uuid: string, name: string, description: string | null, instruction: string | null, type: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, goPayPaymentMethod: { __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string } | null }, deliveryCountry: { __typename: 'Country', name: string, code: string } | null } | null };


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
    

export const LastOrderQueryDocument = gql`
    query LastOrderQuery {
  lastOrder {
    ...LastOrderFragment
  }
}
    ${LastOrderFragment}`;

export function useLastOrderQuery(options?: Omit<Urql.UseQueryArgs<LastOrderQueryVariables>, 'query'>) {
  return Urql.useQuery<LastOrderQuery, LastOrderQueryVariables>({ query: LastOrderQueryDocument, ...options });
};