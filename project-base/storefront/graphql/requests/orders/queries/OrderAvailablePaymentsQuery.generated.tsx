import * as Types from '../../../types';

import gql from 'graphql-tag';
import { SimplePaymentFragment } from '../../payments/fragments/SimplePaymentFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type OrderAvailablePaymentsQueryVariables = Types.Exact<{
  orderUuid: Types.Scalars['Uuid']['input'];
}>;


export type OrderAvailablePaymentsQuery = { __typename?: 'Query', orderPayments: { __typename?: 'OrderPaymentsConfig', availablePayments: Array<{ __typename: 'Payment', uuid: string, name: string, description: string | null, instruction: string | null, type: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, goPayPaymentMethod: { __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string } | null }>, currentPayment: { __typename: 'Payment', uuid: string, name: string, description: string | null, instruction: string | null, type: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, goPayPaymentMethod: { __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string } | null } } };


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
    

export const OrderAvailablePaymentsQueryDocument = gql`
    query OrderAvailablePaymentsQuery($orderUuid: Uuid!) {
  orderPayments(orderUuid: $orderUuid) {
    availablePayments {
      ...SimplePaymentFragment
    }
    currentPayment {
      ...SimplePaymentFragment
    }
  }
}
    ${SimplePaymentFragment}`;

export function useOrderAvailablePaymentsQuery(options: Omit<Urql.UseQueryArgs<OrderAvailablePaymentsQueryVariables>, 'query'>) {
  return Urql.useQuery<OrderAvailablePaymentsQuery, OrderAvailablePaymentsQueryVariables>({ query: OrderAvailablePaymentsQueryDocument, ...options });
};