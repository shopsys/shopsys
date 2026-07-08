// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeSeoSettingFragment = (
  { __typename: 'SeoSetting' }
  & Pick<Types.TypeSeoSetting, 'title' | 'titleAddOn' | 'metaDescription'>
);

export const SeoSettingFragment = gql`
    fragment SeoSettingFragment on SeoSetting {
  __typename
  title
  titleAddOn
  metaDescription
}
    `;