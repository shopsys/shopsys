// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { SeoAttributesFragment } from '../../seo/fragments/SeoAttributesFragment.generated';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
import { HreflangLinksFragment } from '../../hreflangLinks/fragments/HreflangLinksFragment.generated';
export type TypeSeoPageFragment = { __typename: 'SeoPage', ogTitle: string | null, ogDescription: string | null, seo: { __typename: 'SeoAttributes', title: string | null, metaDescription: string | null, h1: string | null, metaRobots: string | null, canonicalUrl: string | null }, ogImage: { __typename: 'Image', name: string | null, url: string } | null, hreflangLinks: Array<{ hreflang: string, href: string }> };

export const SeoPageFragment = gql`
    fragment SeoPageFragment on SeoPage {
  __typename
  seo {
    ...SeoAttributesFragment
  }
  ogTitle
  ogDescription
  ogImage {
    ...ImageFragment
  }
  hreflangLinks {
    ...HreflangLinksFragment
  }
}
    ${SeoAttributesFragment}
${ImageFragment}
${HreflangLinksFragment}`;