// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
import { HreflangLinksFragment } from '../../hreflangLinks/fragments/HreflangLinksFragment.generated';
export type TypeSeoPageFragment = { __typename: 'SeoPage', title: string | null, metaDescription: string | null, canonicalUrl: string | null, ogTitle: string | null, ogDescription: string | null, ogImage: { __typename: 'Image', name: string | null, url: string } | null, hreflangLinks: Array<{ __typename?: 'HreflangLink', hreflang: string, href: string }> };

export const SeoPageFragment = gql`
    fragment SeoPageFragment on SeoPage {
  __typename
  title
  metaDescription
  canonicalUrl
  ogTitle
  ogDescription
  ogImage {
    ...ImageFragment
  }
  hreflangLinks {
    ...HreflangLinksFragment
  }
}
    ${ImageFragment}
${HreflangLinksFragment}`;