// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeHreflangLinksFragment = { __typename?: 'HreflangLink', hreflang: string, href: string };

export const HreflangLinksFragment = gql`
    fragment HreflangLinksFragment on HreflangLink {
  hreflang
  href
}
    `;