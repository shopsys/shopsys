// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeHreflangLinksFragment = (
  { __typename?: 'HreflangLink' }
  & Pick<Types.TypeHreflangLink, 'hreflang' | 'href'>
);

export const HreflangLinksFragment = gql`
    fragment HreflangLinksFragment on HreflangLink {
  hreflang
  href
}
    `;