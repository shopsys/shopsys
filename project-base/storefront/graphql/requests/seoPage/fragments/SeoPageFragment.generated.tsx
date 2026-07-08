// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
import { HreflangLinksFragment } from '../../hreflangLinks/fragments/HreflangLinksFragment.generated';
export type TypeSeoPageFragment = (
  { __typename: 'SeoPage' }
  & Pick<Types.TypeSeoPage, 'title' | 'metaDescription' | 'canonicalUrl' | 'ogTitle' | 'ogDescription'>
  & { ogImage: Types.Maybe<(
    { __typename: 'Image' }
    & Pick<Types.TypeImage, 'name' | 'url'>
  )>, hreflangLinks: Array<(
    { __typename?: 'HreflangLink' }
    & Pick<Types.TypeHreflangLink, 'hreflang' | 'href'>
  )> }
);

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