import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ComplaintDetailFragment } from '../fragments/ComplaintDetailFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeComplaintsQueryVariables = Types.Exact<{
  first: Types.InputMaybe<Types.Scalars['Int']['input']>;
  after: Types.InputMaybe<Types.Scalars['String']['input']>;
  searchInput: Types.InputMaybe<Types.TypeSearchInput>;
}>;


export type TypeComplaintsQuery = { __typename?: 'Query', complaints: { __typename?: 'ComplaintConnection', totalCount: number, edges: Array<{ __typename?: 'ComplaintEdge', cursor: string, node: { __typename?: 'Complaint', uuid: string, number: string, createdAt: any, deliveryFirstName: string, deliveryLastName: string, deliveryCompanyName: string | null, deliveryTelephone: string, deliveryStreet: string, deliveryCity: string, deliveryPostcode: string, status: string, deliveryCountry: { __typename: 'Country', name: string, code: string }, items: Array<{ __typename?: 'ComplaintItem', quantity: number, description: string, orderItem: { __typename: 'OrderItem', uuid: string, name: string, vatRate: string, quantity: number, unit: string | null, type: Types.TypeOrderItemTypeEnum, unitPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, order: { __typename?: 'Order', uuid: string, number: string, creationDate: any }, product: { __typename?: 'MainVariant', slug: string, isVisible: boolean, mainImage: { __typename: 'Image', name: string | null, url: string } | null } | { __typename?: 'RegularProduct', slug: string, isVisible: boolean, mainImage: { __typename: 'Image', name: string | null, url: string } | null } | { __typename?: 'Variant', slug: string, isVisible: boolean, mainImage: { __typename: 'Image', name: string | null, url: string } | null } | null } | null, files: Array<{ __typename: 'File', anchorText: string, url: string }> | null }>, order: { __typename: 'Order', uuid: string, number: string, creationDate: any, status: string, firstName: string | null, lastName: string | null, email: string, telephone: string, companyName: string | null, companyNumber: string | null, companyTaxNumber: string | null, street: string, city: string, postcode: string, isDeliveryAddressDifferentFromBilling: boolean, deliveryFirstName: string | null, deliveryLastName: string | null, deliveryCompanyName: string | null, deliveryTelephone: string | null, deliveryStreet: string | null, deliveryCity: string | null, deliveryPostcode: string | null, note: string | null, urlHash: string, promoCode: string | null, trackingNumber: string | null, trackingUrl: string | null, paymentTransactionsCount: number, isPaid: boolean, items: Array<{ __typename: 'OrderItem', uuid: string, name: string, vatRate: string, quantity: number, unit: string | null, type: Types.TypeOrderItemTypeEnum, unitPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, order: { __typename?: 'Order', uuid: string, number: string, creationDate: any }, product: { __typename?: 'MainVariant', slug: string, isVisible: boolean, mainImage: { __typename: 'Image', name: string | null, url: string } | null } | { __typename?: 'RegularProduct', slug: string, isVisible: boolean, mainImage: { __typename: 'Image', name: string | null, url: string } | null } | { __typename?: 'Variant', slug: string, isVisible: boolean, mainImage: { __typename: 'Image', name: string | null, url: string } | null } | null }>, transport: { __typename: 'Transport', name: string, isPersonalPickup: boolean, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename?: 'Image', url: string } | null, transportType: { __typename?: 'TransportType', code: string } }, payment: { __typename: 'Payment', name: string, type: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename?: 'Image', url: string } | null }, country: { __typename: 'Country', name: string }, deliveryCountry: { __typename: 'Country', name: string } | null, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } } } | null } | null> | null } };


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
    

export const ComplaintsQueryDocument = gql`
    query ComplaintsQuery($first: Int, $after: String, $searchInput: SearchInput) {
  complaints(first: $first, after: $after, searchInput: $searchInput) {
    totalCount
    edges {
      cursor
      node {
        ...ComplaintDetailFragment
      }
    }
  }
}
    ${ComplaintDetailFragment}`;

export function useComplaintsQuery(options?: Omit<Urql.UseQueryArgs<TypeComplaintsQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeComplaintsQuery, TypeComplaintsQueryVariables>({ query: ComplaintsQueryDocument, ...options });
};