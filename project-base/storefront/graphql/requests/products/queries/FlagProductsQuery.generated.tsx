import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ListedProductConnectionFragment } from '../fragments/ListedProductConnectionFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeFlagProductsQueryVariables = Types.Exact<{
  endCursor: Types.Scalars['String']['input'];
  orderingMode?: Types.InputMaybe<Types.TypeProductOrderingModeEnum>;
  filter?: Types.InputMaybe<Types.TypeProductFilter>;
  urlSlug?: Types.InputMaybe<Types.Scalars['String']['input']>;
  pageSize?: Types.InputMaybe<Types.Scalars['Int']['input']>;
}>;


export type TypeFlagProductsQuery = { __typename?: 'Query', products: { __typename: 'ProductConnection', pageInfo: { __typename?: 'PageInfo', hasNextPage: boolean }, edges: Array<{ __typename: 'ProductEdge', node: { __typename: 'MainVariant', variantsCount: number, id: number, uuid: string, slug: string, fullName: string, name: string, isSellingDenied: boolean, availableStoresCount: number | null, catalogNumber: string, isMainVariant: boolean, isInquiryType: boolean, unit: { __typename?: 'Unit', name: string }, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: any | null, percentageDiscount: number | null, basicPrice: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, isSellingDenied: boolean, availableStoresCount: number | null, catalogNumber: string, isMainVariant: boolean, isInquiryType: boolean, unit: { __typename?: 'Unit', name: string }, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: any | null, percentageDiscount: number | null, basicPrice: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, isSellingDenied: boolean, availableStoresCount: number | null, catalogNumber: string, isMainVariant: boolean, isInquiryType: boolean, mainVariant: { __typename?: 'MainVariant', slug: string } | null, unit: { __typename?: 'Unit', name: string }, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: any | null, percentageDiscount: number | null, basicPrice: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> } | null } | null> | null } };


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
    "BaseCustomerUser": [
      "CompanyCustomerUser",
      "CurrentCompanyCustomerUser",
      "CurrentRegularCustomerUser",
      "RegularCustomerUser"
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
    "CurrentCustomerUser": [
      "CurrentCompanyCustomerUser",
      "CurrentRegularCustomerUser"
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
    

export const FlagProductsQueryDocument = gql`
    query FlagProductsQuery($endCursor: String!, $orderingMode: ProductOrderingModeEnum, $filter: ProductFilter, $urlSlug: String, $pageSize: Int) {
  products(
    flagSlug: $urlSlug
    after: $endCursor
    orderingMode: $orderingMode
    filter: $filter
    first: $pageSize
  ) {
    ...ListedProductConnectionFragment
  }
}
    ${ListedProductConnectionFragment}`;

export function useFlagProductsQuery(options: Omit<Urql.UseQueryArgs<TypeFlagProductsQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeFlagProductsQuery, TypeFlagProductsQueryVariables>({ query: FlagProductsQueryDocument, ...options });
};