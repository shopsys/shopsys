import * as Types from '../../../types';

import gql from 'graphql-tag';
import { CountryFragment } from '../../countries/fragments/CountryFragment.generated';
import { OpeningHoursFragment } from './OpeningHoursFragment.generated';
import { BreadcrumbFragment } from '../../breadcrumbs/fragments/BreadcrumbFragment.generated';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
export type TypeStoreDetailFragment = { __typename: 'Store', uuid: string, slug: string, description: string | null, street: string, city: string, postcode: string, contactInfo: string | null, specialMessage: string | null, latitude: string | null, longitude: string | null, storeName: string, country: { __typename: 'Country', name: string, code: string }, openingHours: { __typename?: 'OpeningHours', isOpen: boolean, dayOfWeek: number, openingHoursOfDays: Array<{ __typename?: 'OpeningHoursOfDay', date: any, dayOfWeek: number, openingHoursRanges: Array<{ __typename?: 'OpeningHoursRange', openingTime: string, closingTime: string }> }> }, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, storeImages: Array<{ __typename: 'Image', name: string | null, url: string }> };


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
    
export const StoreDetailFragment = gql`
    fragment StoreDetailFragment on Store {
  __typename
  uuid
  slug
  storeName: name
  description
  street
  city
  postcode
  country {
    ...CountryFragment
  }
  openingHours {
    ...OpeningHoursFragment
  }
  contactInfo
  specialMessage
  latitude
  longitude
  breadcrumb {
    ...BreadcrumbFragment
  }
  storeImages: images {
    ...ImageFragment
  }
}
    ${CountryFragment}
${OpeningHoursFragment}
${BreadcrumbFragment}
${ImageFragment}`;