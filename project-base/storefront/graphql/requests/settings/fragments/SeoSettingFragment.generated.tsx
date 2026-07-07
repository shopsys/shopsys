// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeSeoSettingFragment = { __typename: 'SeoSetting', title: string | null, titleAddOn: string | null, metaDescription: string | null };

export const SeoSettingFragment = gql`
    fragment SeoSettingFragment on SeoSetting {
  __typename
  title
  titleAddOn
  metaDescription
}
    `;