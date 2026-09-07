// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeSeoSettingFragment = { __typename: 'SeoSetting', title: string | null, titleAddOn: string | null, metaDescription: string | null, organization: { name: string | null, companyTaxNumber: string | null, companyNumber: string | null, description: string | null, street: string | null, city: string | null, postcode: string | null, country: string | null, logo: string | null, socialNetworkUrls: Array<string> } };

export const SeoSettingFragment = gql`
    fragment SeoSettingFragment on SeoSetting {
  __typename
  organization {
    name
    companyTaxNumber
    companyNumber
    description
    street
    city
    postcode
    country
    logo
    socialNetworkUrls
  }
  title
  titleAddOn
  metaDescription
}
    `;