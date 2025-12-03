import * as Types from '../../../types';

import gql from 'graphql-tag';
import { OrderWithdrawalInstructionsFragment } from '../fragments/OrderWithdrawalInstructionsFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeOrderWithdrawalInstructionsQueryVariables = Types.Exact<{
  urlHash: Types.Scalars['String']['input'];
}>;


export type TypeOrderWithdrawalInstructionsQuery = { __typename?: 'Query', order: { __typename: 'Order', withdrawalInstructions: string } | null };


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
    

export const OrderWithdrawalInstructionsQueryDocument = gql`
    query OrderWithdrawalInstructionsQuery($urlHash: String!) {
  order(urlHash: $urlHash) {
    ...OrderWithdrawalInstructionsFragment
  }
}
    ${OrderWithdrawalInstructionsFragment}`;

export function useOrderWithdrawalInstructionsQuery(options: Omit<Urql.UseQueryArgs<TypeOrderWithdrawalInstructionsQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeOrderWithdrawalInstructionsQuery, TypeOrderWithdrawalInstructionsQueryVariables>({ query: OrderWithdrawalInstructionsQueryDocument, ...options });
};