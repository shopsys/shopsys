// @ts-nocheck
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