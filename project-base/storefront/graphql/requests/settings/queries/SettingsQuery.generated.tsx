import * as Types from '../../../types';

import gql from 'graphql-tag';
import { PricingSettingFragment } from '../fragments/PricingSettingFragment.generated';
import { SeoSettingFragment } from '../fragments/SeoSettingFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeSettingsQueryVariables = Types.Exact<{ [key: string]: never; }>;


export type TypeSettingsQuery = { __typename?: 'Query', settings: { __typename?: 'Settings', contactFormMainText: string, maxAllowedPaymentTransactions: number, displayTimezone: string, heurekaEnabled: boolean, privacyPolicyArticleUrl: string | null, termsAndConditionsArticleUrl: string | null, userConsentPolicyArticleUrl: string | null, socialNetworkLoginConfig: Array<Types.TypeLoginTypeEnum>, pricing: { __typename: 'PricingSetting', defaultCurrencyCode: string, minimumFractionDigits: number }, seo: { __typename: 'SeoSetting', title: string, titleAddOn: string, metaDescription: string }, mainBlogCategoryData: { __typename?: 'MainBlogCategoryData', mainBlogCategoryUrl: string | null, mainBlogCategoryMainImage: { __typename?: 'Image', url: string } | null } } | null };


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
    

export const SettingsQueryDocument = gql`
    query SettingsQuery @redisCache(ttl: 3600) {
  settings {
    pricing {
      ...PricingSettingFragment
    }
    seo {
      ...SeoSettingFragment
    }
    contactFormMainText
    maxAllowedPaymentTransactions
    displayTimezone
    heurekaEnabled
    mainBlogCategoryData {
      mainBlogCategoryUrl
      mainBlogCategoryMainImage {
        url
      }
    }
    privacyPolicyArticleUrl
    termsAndConditionsArticleUrl
    userConsentPolicyArticleUrl
    socialNetworkLoginConfig
  }
}
    ${PricingSettingFragment}
${SeoSettingFragment}`;

export function useSettingsQuery(options?: Omit<Urql.UseQueryArgs<TypeSettingsQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeSettingsQuery, TypeSettingsQueryVariables>({ query: SettingsQueryDocument, ...options });
};