// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeHreflangLinksFragment = { hreflang: string, href: string };

export const HreflangLinksFragment = gql`
    fragment HreflangLinksFragment on HreflangLink {
  hreflang
  href
}
    `;