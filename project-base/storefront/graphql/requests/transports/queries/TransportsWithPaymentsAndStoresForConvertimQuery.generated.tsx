import * as Types from '../../../types';

import gql from 'graphql-tag';
import { TransportsWithPaymentsAndStoresForConvertimFragment } from '../fragments/TransportsWithPaymentsAndStoresForConvertimFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeTransportsWithPaymentsAndStoresForConvertimQueryVariables = Types.Exact<{
  cartUuid?: Types.InputMaybe<Types.Scalars['Uuid']['input']>;
  displayInCartOnly?: Types.InputMaybe<Types.Scalars['Boolean']['input']>;
}>;


export type TypeTransportsWithPaymentsAndStoresForConvertimQuery = { __typename?: 'Query', transports: Array<{ __typename: 'Transport', uuid: string, name: string, description: string | null, instructions: string | null, daysUntilDelivery: number, transportTypeCode: Types.TypeTransportTypeEnum, isPersonalPickup: boolean, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, payments: Array<{ __typename: 'Payment', uuid: string, name: string, description: string | null, instructions: string | null, type: Types.TypePaymentTypeEnum, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, goPayPaymentMethod: { __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string } | null, vat: { __typename: 'Vat', percent: string } }>, stores: { __typename: 'StoreConnection', edges: Array<{ __typename: 'StoreEdge', node: { __typename: 'Store', slug: string, name: string, description: string | null, latitude: string | null, longitude: string | null, street: string, postcode: string, city: string, distance: number | null, email: string | null, phone: string | null, specialMessage: string | null, identifier: string, openingHours: { __typename?: 'OpeningHours', status: Types.TypeStoreOpeningStatusEnum, dayOfWeek: number, openingHoursOfDays: Array<{ __typename?: 'OpeningHoursOfDay', date: any, dayOfWeek: number, openingHoursRanges: Array<{ __typename?: 'OpeningHoursRange', openingTime: string, closingTime: string }> }> }, country: { __typename: 'Country', name: string, code: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null } | null } | null> | null } | null, vat: { __typename: 'Vat', percent: string } }> };


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
    

export const TransportsWithPaymentsAndStoresForConvertimQueryDocument = gql`
    query TransportsWithPaymentsAndStoresForConvertimQuery($cartUuid: Uuid, $displayInCartOnly: Boolean = true) {
  transports(cartUuid: $cartUuid, displayInCartOnly: $displayInCartOnly) {
    ...TransportsWithPaymentsAndStoresForConvertimFragment
  }
}
    ${TransportsWithPaymentsAndStoresForConvertimFragment}`;

export function useTransportsWithPaymentsAndStoresForConvertimQuery(options?: Omit<Urql.UseQueryArgs<TypeTransportsWithPaymentsAndStoresForConvertimQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeTransportsWithPaymentsAndStoresForConvertimQuery, TypeTransportsWithPaymentsAndStoresForConvertimQueryVariables>({ query: TransportsWithPaymentsAndStoresForConvertimQueryDocument, ...options });
};